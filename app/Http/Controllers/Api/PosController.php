<?php

namespace App\Http\Controllers\Api;
use App\PurchaseOrder;
use App\Pos;
use App\Product;
use App\Branch;
use Valiadtor;
use Str;
use App\Stock;
use App\StockMovement;
use App\User;
use App\StockSerialNo;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CompanySettings;
use App\Invoice;
use App\Payment;
use DB;
use App\SavedTransaction;
use App\SavedTransactionItem;

class PosController extends Controller
{

    protected $pos;
    
    public function __construct(Pos $pos)
    {
        $this->pos = $pos;
    }

    public function multPosOrder(Request $request)
    {
        $organization_id = auth()->user()->organization_id;
        $user_id = auth()->user()->id;

        if (!$request->cart_items) {
            return response()->json(['error' => 'No cart items provided'], 400);
        }

        // Build an idempotency key so a double-clicked button or an automatic
        // client retry does not create the same checkout twice. Prefer a key
        // supplied by the client; otherwise fall back to a fingerprint of the
        // payload (only deduped within a short window, see below).
        $effectiveKey = $this->buildIdempotencyKey($request, $organization_id, $user_id);
        $lockName = 'pos_checkout_' . md5($effectiveKey);

        // Serialize concurrent identical requests at the database level. This is
        // reliable regardless of the configured cache driver.
        DB::selectOne('SELECT GET_LOCK(?, ?) AS acquired', [$lockName, 10]);

        try {
            // If this checkout was already processed, return the original
            // transaction instead of creating a duplicate.
            $existing = $this->findDuplicateInvoice($effectiveKey, $organization_id, $request);
            if ($existing) {
                return $this->posTransactionResponse($existing, $organization_id);
            }

            return DB::transaction(function () use ($request, $organization_id, $user_id, $effectiveKey) {
                $companySettings = CompanySettings::where('organization_id', $organization_id)->first();
                $currency = $companySettings->currency;
                $sell_by_serial_no = $companySettings->sell_by_serial_no;

                $payment_mode = $request->payment_mode;
                $transact_id = "TRANSAC-" . strtoupper(Str::random(15));

                $sale_orders = [];
                $pos_order = [];
                $total_purchase = 0;
                $sold_at = now();

                $v = $request->cart_items;

                foreach ($v as $index => $cart_items) {
                    $sale_orders = Stock::firstOrNew(['id' => $v[$index]['id']]);
                    $sale_orders->quantity_sold += $v[$index]['quantity'];
                    $sale_orders->save();

                    $total_purchase += $v[$index]['quantity'] * $v[$index]['order']['unit_selling_price'];

                    $pos_order = Pos::create([
                        'purchase_order_id' => $v[$index]['purchase_order_id'],
                        'transaction_id' => $transact_id,
                        'qty_sold' => $v[$index]['quantity'],
                        'unit_selling_price' => $v[$index]['order']['unit_selling_price'],
                        'supplier_id' => $v[$index]['supplier_id'],
                        'serials' => $sell_by_serial_no == 1 ? $v[$index]['new_serials'] : null,
                        'stock_id' => $v[$index]['id'],
                        'product_id' => $v[$index]['product_id'],
                        'cashier_id' => $user_id,
                        'payment_mode' => $payment_mode,
                        'channel' => 'pos_order',
                        'organization_id' => $organization_id,
                    ]);
                }

                // Add delivery fee if provided
                $delivery_fee = $request->delivery_fee ?? 0;
                $discount_percent = $request->discount_percent ?? 0;
                $discount = $request->discount ?? 0;
                $total_with_delivery = ($total_purchase - $discount) + $delivery_fee;

                // Save Invoice
                $now = Carbon::now();
                $invoice = new Invoice();
                $invoice->invoice_no = $request->invoice_no;
                $invoice->transaction_id = $transact_id;
                $invoice->idempotency_key = $effectiveKey;
                $invoice->cashier_id = $user_id;
                $invoice->organization_id = $organization_id;
                $invoice->description = "Sales from POS Menu";
                $invoice->payment_type = "POS";
                $invoice->client_id = $request->client_id;
                $invoice->currency = $currency;
                $invoice->issued_date = $now;
                $invoice->due_date = $request->due_date;
                $invoice->amount = $total_with_delivery;
                $invoice->amount_paid = $request->amount_paid;
                $invoice->balance = $total_with_delivery - $request->amount_paid;
                $invoice->payment_mode = $payment_mode;
                $invoice->delivery_fee = $delivery_fee;
                $invoice->discount_percent = $discount_percent;
                $invoice->discount = $discount;
                $invoice->save();

                $payment = new Payment();
                $payment->amount_paid = $request->amount_paid;
                $payment->amount = $total_with_delivery;
                $payment->balance = $total_with_delivery - $request->amount_paid;
                $payment->invoice_id = $invoice->id;
                $payment->client_id = $request->client_id;
                $payment->organization_id = $organization_id;
                $payment->save();

                $update_pos = Pos::where('organization_id', $organization_id)
                    ->where('transaction_id', $transact_id)
                    ->update(['invoice_id' => $invoice->id]);

                $invoice = Invoice::where('organization_id', $organization_id)
                    ->where('id', $invoice->id)
                    ->with('payments')
                    ->with('client')
                    ->first();

                $pos_items = Pos::where('organization_id', $organization_id)
                    ->where('invoice_id', $invoice->id)
                    ->with('stock')
                    ->with('order')
                    ->get();

                $invoices = Invoice::where('organization_id', $organization_id)
                    ->where('client_id', $invoice->client_id)
                    ->get();

                $balance = $total_with_delivery - $request->amount_paid;
                $total_balance = $invoices->sum('client_balance');
                $prev_balance = $total_balance - $balance;

                return response()->json(compact(
                    'pos_order',
                    'sold_at',
                    'payment_mode',
                    'invoice',
                    'pos_items',
                    'total_balance',
                    'balance',
                    'prev_balance'
                ));
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Transaction failed: ' . $e->getMessage()], 500);
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockName]);
        }
    }

    /**
     * Build a stable key identifying this checkout attempt. A client may send
     * an `idempotency_key` (recommended: a UUID generated once per checkout and
     * resent on retry). When absent we derive a fingerprint from the payload so
     * accidental rapid re-submits are still caught within a short time window.
     */
    private function buildIdempotencyKey(Request $request, $organization_id, $user_id)
    {
        if ($request->idempotency_key) {
            return 'pos:key:' . $organization_id . ':' . $request->idempotency_key;
        }

        $items = collect($request->cart_items)->map(function ($item) {
            return [
                'id' => $item['id'] ?? null,
                'qty' => $item['quantity'] ?? null,
                'price' => $item['order']['unit_selling_price'] ?? null,
            ];
        })->sortBy('id')->values()->all();

        $fingerprint = md5(json_encode([
            'org' => $organization_id,
            'user' => $user_id,
            'client' => $request->client_id,
            'payment_mode' => $request->payment_mode,
            'amount_paid' => $request->amount_paid,
            'delivery_fee' => $request->delivery_fee,
            'discount' => $request->discount,
            'items' => $items,
        ]));

        return 'pos:auto:' . $organization_id . ':' . $fingerprint;
    }

    /**
     * Look for an already-processed checkout with this idempotency key. A
     * client-supplied key matches at any time (a retry must never duplicate);
     * an auto-derived fingerprint only matches within a short window so genuine
     * repeat sales of the same cart are still allowed.
     */
    private function findDuplicateInvoice($effectiveKey, $organization_id, Request $request)
    {
        $query = Invoice::where('organization_id', $organization_id)
            ->where('idempotency_key', $effectiveKey);

        if (!$request->idempotency_key) {
            $query->where('created_at', '>=', Carbon::now()->subSeconds(20));
        }

        return $query->latest()->first();
    }

    /**
     * Build the standard checkout response payload for a given invoice. Used
     * both for a freshly created transaction and when returning the original
     * one for a duplicate (idempotent) request.
     */
    private function posTransactionResponse(Invoice $invoice, $organization_id)
    {
        $invoice = Invoice::where('organization_id', $organization_id)
            ->where('id', $invoice->id)
            ->with('payments')
            ->with('client')
            ->first();

        $pos_items = Pos::where('organization_id', $organization_id)
            ->where('invoice_id', $invoice->id)
            ->with('stock')
            ->with('order')
            ->get();

        $invoices = Invoice::where('organization_id', $organization_id)
            ->where('client_id', $invoice->client_id)
            ->get();

        $balance = $invoice->amount - $invoice->amount_paid;
        $total_balance = $invoices->sum('client_balance');
        $prev_balance = $total_balance - $balance;
        $payment_mode = $invoice->payment_mode;
        $pos_order = $pos_items;
        $sold_at = $invoice->issued_date ?? $invoice->created_at;

        return response()->json(compact(
            'pos_order',
            'sold_at',
            'payment_mode',
            'invoice',
            'pos_items',
            'total_balance',
            'balance',
            'prev_balance'
        ));
    }

    public function editMultPosOrder(Request $request)
    {
        $organization_id = auth()->user()->organization_id;
        $user_id = auth()->user()->id;

        if (!$request->cart_items) {
            return response()->json(['error' => 'No cart items provided'], 400);
        }

        // Identify this edit attempt so a double-submit does not re-run the
        // delete-and-recreate twice. The key is scoped to the invoice being
        // edited.
        $effectiveKey = $this->buildIdempotencyKey($request, $organization_id, $user_id)
            . ':inv:' . $request->invoice_id;

        // Serialize concurrent edits of the same invoice to avoid the
        // delete/recreate race corrupting stock counts.
        $lockName = 'pos_edit_' . md5($organization_id . ':' . $request->invoice_id);
        DB::selectOne('SELECT GET_LOCK(?, ?) AS acquired', [$lockName, 10]);

        try {
            // If this exact edit was already applied to this invoice, return the
            // current state instead of processing it again.
            $existing = Invoice::where('organization_id', $organization_id)
                ->find($request->invoice_id);
            if ($existing && $existing->idempotency_key === $effectiveKey) {
                return $this->posTransactionResponse($existing, $organization_id);
            }

            return DB::transaction(function () use ($request, $organization_id, $user_id, $effectiveKey) {
                $invoice = Invoice::where('organization_id', $organization_id)
                    ->with(['payments', 'client'])
                    ->findOrFail($request->invoice_id);

                // Delete existing POS records and revert stock
                $posRecords = Pos::where('invoice_id', $invoice->id)->get();
                if ($posRecords->isNotEmpty()) {
                    foreach ($posRecords as $pos) {
                        $pos->stock()->decrement('quantity_sold', $pos->qty_sold);
                        $pos->delete();
                    }
                }

                $sale_orders = [];
                $pos_order = [];
                $payment_mode = $request->payment_mode;
                $transact_id = "TRANSAC-" . strtoupper(Str::random(15));
                $total_purchase = 0;

                foreach ($request->cart_items as $cart_item) {
                    $stock = Stock::firstOrNew(['id' => $cart_item['id']]);
                    $stock->quantity_sold += $cart_item['quantity'];
                    $stock->save();

                    $total_purchase += $cart_item['quantity'] * $cart_item['order']['unit_selling_price'];

                    $pos_order = Pos::create([
                        'purchase_order_id' => $cart_item['purchase_order_id'],
                        'transaction_id' => $transact_id,
                        'qty_sold' => $cart_item['quantity'],
                        'unit_selling_price' => $cart_item['order']['unit_selling_price'],
                        'supplier_id' => $cart_item['supplier_id'],
                        'stock_id' => $cart_item['id'],
                        'product_id' => $cart_item['product_id'],
                        'edited_by' => $user_id,
                        'cashier_id' => $user_id,
                        'payment_mode' => $payment_mode,
                        'channel' => 'pos_order',
                        'organization_id' => $organization_id,
                        'created_at' => $invoice->created_at
                    ]);
                }

                // Add delivery fee if provided
                $delivery_fee = $request->delivery_fee ?? 0;
                $total_with_delivery = ($total_purchase - $request->discount) + $delivery_fee;

                // Update invoice
                $invoice->update([
                    'transaction_id' => $transact_id,
                    'idempotency_key' => $effectiveKey,
                    'edited_by' => $user_id,
                    'description' => "Sales from POS Menu",
                    'payment_type' => "POS",
                    'client_id' => $request->client_id,
                    'issued_date' => now(),
                    'amount' => $total_with_delivery,
                    'amount_paid' => $request->amount_paid,
                    'balance' => $total_with_delivery - $request->amount_paid,
                    'payment_mode' => $payment_mode,
                    'delivery_fee' => $delivery_fee,
                    'discount' => $request->discount ?? 0,
                    'discount_percent' => $request->discount_percent ?? 0,
                    'organization_id' => $organization_id,
                ]);

                // Update payment
                $payment = Payment::where('organization_id', $organization_id)
                    ->where('invoice_id', $request->invoice_id)
                    ->first();

                $payment->update([
                    'amount_paid' => $request->amount_paid,
                    'amount' => $total_with_delivery,
                    'balance' => $total_with_delivery - $request->amount_paid,
                    'client_id' => $request->client_id,
                    'organization_id' => $organization_id,
                ]);

                // Update POS records with invoice ID
                Pos::where('transaction_id', $transact_id)
                    ->update(['invoice_id' => $invoice->id]);

                // Load updated POS items
                $pos_items = Pos::where('organization_id', $organization_id)
                    ->where('invoice_id', $invoice->id)
                    ->with(['stock', 'order'])
                    ->get();

                $invoices = Invoice::where('organization_id', $organization_id)
                    ->where('client_id', $invoice->client_id)
                    ->get();

                $total_balance = $invoices->sum('client_balance');
                $balance = $total_with_delivery - $request->amount_paid;
                $prev_balance = $total_balance - $balance;
                $sold_at = now();

                return response()->json(compact(
                    'pos_order',
                    'sold_at',
                    'payment_mode',
                    'invoice',
                    'pos_items',
                    'total_balance',
                    'prev_balance',
                    'balance'
                ));
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Edit transaction failed: ' . $e->getMessage()], 500);
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockName]);
        }
    }



    public function products(Request $request){
        $products = Product::where('organization_id', auth()->user()->organization_id)->select('id','name')->get();
        return response()->json(compact('products'));
    }

   public function getPosSales(Request $request) 
    {
        $users = User::where('organization_id', auth()->user()->organization_id)
            ->select('id', 'firstname', 'lastname')
            ->get();
        
        $pos_sales = $this->pos
            ->where('organization_id', auth()->user()->organization_id)
            ->search($request->search)
            ->order($request->order)
            ->employee($request->user)
            ->product($request->product)
            ->startdate($request->fromdate)
            ->enddate($request->todate)
            ->with('stock')
            ->with('order')
            ->latest()
            ->paginate($request->rows, ['*'], 'page', $request->page);
        
        $sales = $this->pos
            ->where('organization_id', auth()->user()->organization_id)
            ->search($request->search)
            ->order($request->order)
            ->employee($request->user)
            ->product($request->product)
            ->startdate($request->fromdate)
            ->enddate($request->todate)
            ->get();
        
        $total_sales = 0;
        $total_sold = 0;
        $total_instock = 0;
        $total = 0;
        
        foreach($sales as $sale) {
            $result = $sale['selling_price'] * $sale['qty_sold'];
            $total_sales += $result;
            $total_sold += $sale['qty_sold'];
        }
        
        // Calculate totals when product is specified
        if ($request->product) {
            // Get stock information for the specific product
            $stock_query = $this->pos
                ->where('organization_id', auth()->user()->organization_id)
                ->product($request->product);
            
            // Apply date filters if specified
            if ($request->fromdate) {
                $stock_query->startdate($request->fromdate);
            }
            if ($request->todate) {
                $stock_query->enddate($request->todate);
            }
            
            $stock_data = $stock_query->with('stock')->get();
            
            // Calculate total instock from related stock records
            foreach($stock_data as $item) {
                if ($item->stock) {
                    $total_instock += $item->stock->stock_quantity ?? 0;
                }
            }
            
            // Calculate combined total (instock + sold)
            $total = $total_instock + $total_sold;
        }
        
        return response()->json(compact(
            'pos_sales', 
            'users', 
            'total_sales', 
            'total', 
            'total_sold', 
            'total_instock'
        ));
    }

    public function getPosTransactions(Request $request)
    {
        
        $users= User::where('organization_id', auth()->user()->organization_id)->select('id','firstname','lastname')->get();
         $latestIds = DB::table(DB::raw('(
            SELECT MAX(id) as max_id 
            FROM pos 
            WHERE organization_id = ?
            GROUP BY transaction_id
        ) as subquery'))
            ->setBindings([auth()->user()->organization_id])
            ->pluck('max_id');

   

        // Then get the actual records with those IDs
        $pos_sales = $this->pos
            ->where('organization_id', auth()->user()->organization_id)
            ->whereIn('id', $latestIds)
            ->search($request->search)
            ->order($request->order)
            ->employee($request->user)
            ->branch($request->branch)
            ->startdate($request->fromdate)
            ->enddate($request->todate)
            ->with('stock')
            ->with('invoice')
            ->latest()
            ->paginate($request->rows, ['*'], 'page', $request->page);
        
        $invoice=Invoice::where('organization_id', auth()->user()->organization_id)
        ->search($request->search)
        // ->order($request->order)
        ->cashier($request->user)
        //->filter1($request->fromdate)
        //->filter2($request->todate)
        ->get();

        $total_delivery_fee = $invoice->sum('delivery_fee');
        $total_discount = $invoice->sum('discount');

        $total_amount = $invoice->sum('amount');

        $total_amount_paid = $invoice->sum('amount_paid');

        // Derive gross sales from the SAME invoices so every figure reconciles.
        // Checkout sets amount = sales - discount + delivery_fee, therefore
        // sales = amount + discount - delivery_fee. Previously total_sales was
        // summed from Pos line items over a different filter set (branch, line
        // level), so it never lined up with the invoice-based amounts/balance.
        $total_sales = $total_amount + $total_discount - $total_delivery_fee;

        // Net outstanding balance, discount/delivery-aware. `amount` already
        // accounts for discount and delivery, so balance = amount - amount_paid.
        // This matches the per-invoice balance used by /api/clients/balance.
        $total_balance = $total_amount - $total_amount_paid;

        $branches = Branch::where('organization_id', auth()->user()->organization_id)
        ->where('sell', 1)
        ->select('id','name')->get();
        
        
        return response()->json(compact('pos_sales',
        'users','total_sales','total_amount','total_discount','total_amount_paid','total_delivery_fee','total_balance','branches'));
    }

    public function getPosSales2(Request $request)
    {
        $user=auth()->user()->id;
        $users= User::where('organization_id', auth()->user()->organization_id)->select('id','firstname','lastname')->get();
       

        $pos_sales=$this->pos
        ->where('organization_id', auth()->user()->organization_id)
        ->where('cashier_id', $user)
        ->search($request->search)
        ->order($request->order)
        ->employee($request->user)
        ->startdate($request->fromdate)
        ->enddate($request->todate)
        ->with('stock')->with('order')
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);
        
        
        return response()->json(compact('pos_sales','users'));
    }

    public function getTransactionDetails(Request $request){
        $transaction_detail=$this->pos
        ->where('organization_id', auth()->user()->organization_id)
        ->where('transaction_id', $request->transaction_id)
        ->with('stock')->with('order')->get();
        $invoice = Invoice::where('transaction_id', $request->transaction_id)->first();

        $clientInvoices = Invoice::where('client_id', $invoice->client_id)
                ->get();

        $total_balance = $clientInvoices->sum('client_balance');

        $balance = $invoice->amount - $invoice->amount_paid;

        $prev_balance = $total_balance - $balance;

        return response()->json(compact('transaction_detail','invoice','total_balance','balance','prev_balance'));
    }

    public function getTransactionDetails2(Request $request){
        $transaction_detail=$this->pos
        ->where('organization_id', auth()->user()->organization_id)
        ->where('cashier_id', auth()->user()->id)
        ->where('transaction_id', $request->transaction_id)
        ->with('stock')->with('order')->get();
        return response()->json(compact('transaction_detail'));
    }

    public function saveSaleForLater(Request $request)
    {
        $organization_id = auth()->user()->organization_id;
        $user_id = auth()->user()->id;

        if (! $request->cart_items || ! is_array($request->cart_items) || count($request->cart_items) === 0) {
            return response()->json(['error' => 'No cart items provided'], 400);
        }

        try {
            return DB::transaction(function () use ($request, $organization_id, $user_id) {
                $transactionId = time();
                $totalAmount = 0;

                $saved = SavedTransaction::create([
                    'transaction_id' => $transactionId,
                    'organization_id' => $organization_id,
                    'user_id' => $user_id,
                    'notes' => $request->notes ?? null,
                    'total_amount' => 0,
                ]);

                foreach ($request->cart_items as $item) {
                    $stockId = $item['stock_id'] ?? $item['id'] ?? null;
                    $productId = $item['product_id'] ?? null;
                    $quantity = intval($item['quantity'] ?? 0);
                    $unitPrice = floatval($item['unit_price'] ?? ($item['order']['unit_selling_price'] ?? 0));
                    $totalPrice = $quantity * $unitPrice;

                    if ($stockId) {
                        $stock = Stock::firstOrNew(['id' => $stockId]);
                        $stock->quantity_saved = (int) ($stock->quantity_saved ?? 0) + $quantity;
                        if (isset($stock->organization_id) && ! $stock->organization_id) {
                            $stock->organization_id = $organization_id;
                        }
                        $stock->save();
                    }

                    SavedTransactionItem::create([
                        'saved_transaction_id' => $saved->id,
                        'stock_id' => $stockId,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ]);

                    $totalAmount += $totalPrice;
                }

                $saved->total_amount = $totalAmount;
                $saved->save();

                return response()->json([
                    'status' => true,
                    'saved_transaction_id' => $transactionId,
                    'saved_id' => $saved->id,
                    'total_amount' => $totalAmount,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Save failed: '.$e->getMessage()], 500);
        }
    }

    public function fetchSavedTransaction(Request $request)
    {
        $organization_id = auth()->user()->organization_id;

        if (! $request->transaction_id && ! $request->saved_id) {
            return response()->json(['error' => 'transaction_id or saved_id required'], 400);
        }

        $query = SavedTransaction::with(['items.stock.order'])->where('organization_id', $organization_id);

        if ($request->transaction_id) {
            $query->where('transaction_id', $request->transaction_id);
        } else {
            $query->where('id', $request->saved_id);
        }

        $transaction = $query->first();

        if (! $transaction) {
            return response()->json(['error' => 'transaction not found'], 404);
        }

        return response()->json(compact('transaction'));
    }

   
    public function fetchAllSavedTransaction(Request $request)
    {
        $organization_id = auth()->user()->organization_id;
        $rows = (int) ($request->rows ?? 25);

        $transactions = SavedTransaction::with(['items.stock.order', 'user'])
            ->where('organization_id', $organization_id)
            ->search($request->search)
            ->latest()
            ->paginate($rows, ['*'], 'page', $request->page);

        return response()->json(compact('transactions'));
    }

    public function deleteSavedTransaction($id)
    {
        $organization_id = auth()->user()->organization_id;

        try {
            return DB::transaction(function () use ($id, $organization_id) {
                $saved = SavedTransaction::where('transaction_id', $id)
                    ->where('organization_id', $organization_id)
                    ->with('items')
                    ->first();

                if (! $saved) {
                    return response()->json(['error' => 'Saved transaction not found'], 404);
                }

                foreach ($saved->items as $item) {
                    
                    if ($item->stock_id) {
                        $stock = Stock::find($item->stock_id);
                        if ($stock) {
                            $current = (int) ($stock->quantity_saved ?? 0);
                            $deduct = (int) $item->quantity;
                            $new = max(0, $current - $deduct);

                            $stock->quantity_saved = $new;
                            $stock->save();
                        }
                    }
                    $item->delete();
                }

                $saved->delete();

                return response()->json(['status' => true]);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Product-level inventory summary.
     *
     * Returns a paginated list of products with their aggregated inventory
     * position: how much is in stock, how much has been sold/returned/saved,
     * the cost and retail valuation of what remains, and how that stock is
     * spread across branches. Supports search, category/brand filters and a
     * stock-status filter (in_stock / out_of_stock / low_stock).
     *
     * Pass `product_id` to get the full per-product breakdown instead
     * (delegates to productInventoryDetail).
     */
    public function productInventorySummary(Request $request)
    {
        $organization_id = auth()->user()->organization_id;

        if ($request->product_id) {
            return $this->productInventoryDetail($request);
        }

        $lowStockThreshold = (int) ($request->low_stock_threshold ?? 5);

        // Pull every product for the org (with optional search/category/brand
        // filters), then attach an aggregated stock summary to each one.
        $productsQuery = Product::where('organization_id', $organization_id)
            ->search($request->search)
            ->category($request->category)
            ->brand($request->brand)
            ->sort($request->order);

        // Stock-status filter (available / unavailable / low). Applied at the
        // query level so pagination counts reflect the filtered set rather than
        // dropping rows after the page has been sliced.
        $this->applyStockStatusFilter(
            $productsQuery,
            $request->stock_status,
            $organization_id,
            $lowStockThreshold
        );

        $products = $productsQuery
            ->paginate($request->rows ?? 25, ['*'], 'page', $request->page);

        // available qty SQL expression, qualified to the stocks table because
        // the valuation join (purchase_order) shares those column names.
        $available = $this->qualifiedAvailableQtyExpression();

        $products->getCollection()->transform(function ($product) use ($organization_id, $available, $lowStockThreshold) {
            $summary = $this->buildProductStockSummary($product->id, $organization_id, $available);
            $summary['low_stock'] = $summary['available_quantity'] > 0
                && $summary['available_quantity'] <= $lowStockThreshold;

            $product->inventory = $summary;
            return $product;
        });

        // Org-wide totals across ALL products (not just the current page) so the
        // dashboard cards stay correct regardless of pagination.
        $totals = $this->buildInventoryTotals($organization_id, $available);

        return response()->json(compact('products', 'totals', 'lowStockThreshold'));
    }

    /**
     * Deep-dive inventory detail for a single product, including:
     *  - overall stock position (in stock, sold, returned, saved, valuation)
     *  - stock count broken down per branch
     *  - the individual stock (batch) rows with their purchase order pricing
     *  - stock movement history (branch transfers)
     *  - recent sales history from the POS
     *  - purchase / restock history
     */
    public function productInventoryDetail(Request $request)
    {
        $organization_id = auth()->user()->organization_id;
        $product_id = $request->product_id;

        $product = Product::where('organization_id', $organization_id)
            ->find($product_id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $available = $this->qualifiedAvailableQtyExpression();

        // Overall position for this product.
        $summary = $this->buildProductStockSummary($product_id, $organization_id, $available);

        // Stock count per branch.
        $byBranch = Stock::where('stocks.organization_id', $organization_id)
            ->where('stocks.product_id', $product_id)
            ->leftJoin('branches', 'branches.id', '=', 'stocks.branch_id')
            ->groupBy('stocks.branch_id', 'branches.name')
            ->select(
                'stocks.branch_id',
                DB::raw('COALESCE(branches.name, "Unassigned") as branch_name'),
                DB::raw('COALESCE(SUM(stocks.stock_quantity),0) as total_quantity'),
                DB::raw('COALESCE(SUM(stocks.quantity_sold),0) as quantity_sold'),
                DB::raw('COALESCE(SUM(stocks.quantity_returned),0) as quantity_returned'),
                DB::raw('COALESCE(SUM(stocks.quantity_saved),0) as quantity_saved'),
                DB::raw('SUM(' . $available . ') as available_quantity')
            )
            ->get();

        // Individual stock batches (with purchase order pricing & branch).
        $batches = Stock::where('organization_id', $organization_id)
            ->where('product_id', $product_id)
            ->with('order', 'branch')
            ->latest()
            ->get();

        // Stock movement (transfer) history.
        $movements = StockMovement::where('organization_id', $organization_id)
            ->where('product_id', $product_id)
            ->with(['fromBranch:id,name', 'toBranch:id,name', 'user:id,firstname,lastname'])
            ->latest()
            ->limit((int) ($request->movement_limit ?? 50))
            ->get();

        // Recent sales history from the POS.
        $sales = $this->pos
            ->where('organization_id', $organization_id)
            ->where('product_id', $product_id)
            ->with('order')
            ->latest()
            ->limit((int) ($request->sales_limit ?? 50))
            ->get();

        // Purchase / restock history.
        $purchases = PurchaseOrder::where('organization_id', $organization_id)
            ->where('product_id', $product_id)
            ->with('supplier')
            ->latest()
            ->limit((int) ($request->purchase_limit ?? 50))
            ->get();

        return response()->json(compact(
            'product',
            'summary',
            'byBranch',
            'batches',
            'movements',
            'sales',
            'purchases'
        ));
    }

    /**
     * Aggregate the stock position for one product into a flat summary array.
     * Quantities come straight from the stock table; valuation joins the
     * purchase order to value remaining stock at both cost and retail.
     */
    private function buildProductStockSummary($product_id, $organization_id, $available)
    {
        $row = Stock::where('stocks.organization_id', $organization_id)
            ->where('stocks.product_id', $product_id)
            ->leftJoin('purchase_order', 'purchase_order.id', '=', 'stocks.purchase_order_id')
            ->select(
                DB::raw('COALESCE(SUM(stocks.stock_quantity),0) as total_quantity'),
                DB::raw('COALESCE(SUM(stocks.quantity_sold),0) as quantity_sold'),
                DB::raw('COALESCE(SUM(stocks.quantity_returned),0) as quantity_returned'),
                DB::raw('COALESCE(SUM(stocks.quantity_saved),0) as quantity_saved'),
                DB::raw('SUM(' . $available . ') as available_quantity'),
                DB::raw('COUNT(DISTINCT stocks.id) as batch_count'),
                DB::raw('COUNT(DISTINCT stocks.branch_id) as branch_count'),
                DB::raw('COALESCE(SUM((' . $available . ') * COALESCE(purchase_order.unit_price,0)),0) as stock_value_cost'),
                DB::raw('COALESCE(SUM((' . $available . ') * COALESCE(purchase_order.unit_selling_price,0)),0) as stock_value_retail')
            )
            ->first();

        return [
            'total_quantity'     => (int) $row->total_quantity,
            'quantity_sold'      => (int) $row->quantity_sold,
            'quantity_returned'  => (int) $row->quantity_returned,
            'quantity_saved'     => (int) $row->quantity_saved,
            'available_quantity' => (int) $row->available_quantity,
            'batch_count'        => (int) $row->batch_count,
            'branch_count'       => (int) $row->branch_count,
            'stock_value_cost'   => (float) $row->stock_value_cost,
            'stock_value_retail' => (float) $row->stock_value_retail,
        ];
    }

    /**
     * Organization-wide inventory totals across every product, for summary
     * cards that should not be affected by pagination.
     */
    private function buildInventoryTotals($organization_id, $available)
    {
        $row = Stock::where('stocks.organization_id', $organization_id)
            ->leftJoin('purchase_order', 'purchase_order.id', '=', 'stocks.purchase_order_id')
            ->select(
                DB::raw('COUNT(DISTINCT stocks.product_id) as products_count'),
                DB::raw('COALESCE(SUM(stocks.quantity_sold),0) as total_sold'),
                DB::raw('SUM(' . $available . ') as total_available'),
                DB::raw('COALESCE(SUM((' . $available . ') * COALESCE(purchase_order.unit_price,0)),0) as total_value_cost'),
                DB::raw('COALESCE(SUM((' . $available . ') * COALESCE(purchase_order.unit_selling_price,0)),0) as total_value_retail')
            )
            ->first();

        return [
            'products_count'     => (int) $row->products_count,
            'total_sold'         => (int) $row->total_sold,
            'total_available'    => (int) $row->total_available,
            'total_value_cost'   => (float) $row->total_value_cost,
            'total_value_retail' => (float) $row->total_value_retail,
        ];
    }

    /**
     * The available-quantity SQL expression, qualified to the `stocks` table.
     * Mirrors Stock::availableQtyExpression() but prefixes every column so it
     * is unambiguous when the query joins `purchase_order`, which carries
     * identically named columns (stock_quantity, quantity_sold, ...).
     */
    private function qualifiedAvailableQtyExpression()
    {
        return '(COALESCE(stocks.stock_quantity,0) - COALESCE(stocks.quantity_sold,0) - COALESCE(stocks.quantity_returned,0) - COALESCE(stocks.quantity_saved,0))';
    }

    /**
     * Constrain a Product query by the product's aggregated available stock.
     * A product's available quantity is summed (in SQL) across all of its
     * non-deleted stock batches, so the filter matches the `available_quantity`
     * shown in each row's `inventory` summary.
     *
     *   available   => sum > 0
     *   unavailable => sum <= 0   (aliases: out_of_stock, out-of-stock)
     *   low         => 0 < sum <= low_stock_threshold
     *
     * Anything else (including null/empty) leaves the query untouched.
     */
    private function applyStockStatusFilter($query, $status, $organization_id, $lowStockThreshold)
    {
        if ($status === null || $status === '') {
            return $query;
        }

        $status = strtolower(trim((string) $status));

        // Correlated subquery: this product's total available quantity. Honors
        // the stocks soft-delete column so trashed batches are not counted.
        $availSub = '(SELECT COALESCE(SUM('
            . 'COALESCE(s.stock_quantity,0) - COALESCE(s.quantity_sold,0)'
            . ' - COALESCE(s.quantity_returned,0) - COALESCE(s.quantity_saved,0)'
            . '),0) FROM stocks s WHERE s.product_id = products.id'
            . ' AND s.organization_id = ? AND s.deleted_at IS NULL)';

        if (in_array($status, ['available', 'in_stock', 'in-stock', '1'], true)) {
            return $query->whereRaw($availSub . ' > 0', [$organization_id]);
        }

        if (in_array($status, ['unavailable', 'out_of_stock', 'out-of-stock', '0'], true)) {
            return $query->whereRaw($availSub . ' <= 0', [$organization_id]);
        }

        if (in_array($status, ['low', 'low_stock', 'low-stock'], true)) {
            return $query->whereRaw(
                $availSub . ' > 0 AND ' . $availSub . ' <= ?',
                [$organization_id, $organization_id, $lowStockThreshold]
            );
        }

        return $query;
    }
}
