<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Str;
use App\PurchaseOrder;
use App\Product;
use App\Stock;
use App\StockMovement;
use App\Branch;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NewStockController extends Controller
{
    

    public function save(Request $request)
    {
        $product = Product::select('id', 'supplier_id')->findOrFail($request->product_id);


        $purchaseOrder = PurchaseOrder::create([
            'product_id'         => $product->id,
            'unit_price'         => $request->unit_price,
            'unit_selling_price' => $request->unit_selling_price,
            'barcode'            => $request->barcode,
            'supplier_id'        => $request->supplier ?? $product->supplier_id,
            'stock_quantity'     => $request->stock_quantity,
            'quantity_moved'     => $request->stock_quantity,
            'tracking_id'        => 'TRK-' . strtoupper(Str::random(5)),
            'organization_id'    => auth()->user()->organization_id,
            'received_at'        => now(),
            'confirmed_at'       => now(),
        ]);

        Stock::updateOrCreate(
            [
                'purchase_order_id' => $purchaseOrder->id,
                'branch_id'         => $request->branch_id,
            ],
            [
                'stock_quantity'   => $purchaseOrder->quantity_moved,
                'product_id'       => $purchaseOrder->product_id,
                'supplier_id'      => $purchaseOrder->supplier_id,
                'organization_id'  => auth()->user()->organization_id,
                'expiry_date' => $request->expiry_date
            ]
        );

        return response()->json(['purchase_order' => $purchaseOrder], 200);
    }

    public function update(Request $request, $stockId)
    {
        
        $stock = Stock::where('id', $stockId)
            ->where('organization_id', auth()->user()->organization_id)
            ->firstOrFail();

        
        $purchaseOrder = PurchaseOrder::where('id', $stock->purchase_order_id)
            ->where('organization_id', auth()->user()->organization_id)
            ->firstOrFail();


        $product = Product::select('id', 'supplier_id')->findOrFail($request->product_id);

    
        $purchaseOrder->update([
            'product_id'         => $product->id,
            'unit_price'         => $request->unit_price,
            'unit_selling_price' => $request->unit_selling_price,
            'barcode'            => $request->barcode,
            'supplier_id'        => $request->supplier ?? $product->supplier_id,
            'stock_quantity'     => $request->stock_quantity,
            'quantity_moved'     => $request->stock_quantity,
            'received_at'        => now(),
            'confirmed_at'       => now(),
        ]);

        // Update stock
        $stock->update([
            'stock_quantity'   => $purchaseOrder->quantity_moved,
            'product_id'       => $purchaseOrder->product_id,
            'supplier_id'      => $purchaseOrder->supplier_id,
            'branch_id'        => $request->branch_id,
            'expiry_date'      => $request->expiry_date
        ]);

        return response()->json([
            'message'         => 'Stock and Purchase Order updated successfully.',
            'purchase_order'  => $purchaseOrder,
            'stock'           => $stock,
        ], 200);
    }


    public function delete($id, Request $request)
    {
        $stock = Stock::where('id', $id)
            ->where(function ($query) {
                $query->whereNull('quantity_sold')->orWhere('quantity_sold', 0);
            })
            ->firstOrFail();

        $purchaseOrder = PurchaseOrder::find($stock->purchase_order_id);

        // Delete the stock
        $stock->delete();

        // Check if other stocks still reference this purchase order
        if ($purchaseOrder) {
            $otherStocksCount = Stock::where('purchase_order_id', $purchaseOrder->id)->count();

            if ($otherStocksCount === 0) {
                $purchaseOrder->delete();
            }
        }

        return response()->json(true);
    }


    public function moveStock(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'stock_id' => 'required|exists:stocks,id',
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'quantity' => 'required|integer|min:1',
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:purchase_order,id', // or whatever your orders table is called
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Start database transaction
        DB::beginTransaction();

        try {
            $stockId = $request->stock_id;
            $fromBranchId = $request->from_branch_id;
            $toBranchId = $request->to_branch_id;
            $quantity = $request->quantity;
            $productId = $request->product_id;
            $orderId = $request->order_id;

            // 1. Get the original stock record with relationships
            $originalStock = Stock::where('id', $stockId)
                ->where('branch_id', $fromBranchId)
                ->first();

            if (!$originalStock) {
                throw new \Exception('Stock not found or does not belong to the specified branch');
            }

            // 2. Calculate available stock (stock_quantity - quantity_sold)
            $availableStock = $originalStock->stock_quantity - $originalStock->quantity_sold;


            // 4. Check if stock already exists for the same product/order in destination branch
            $existingDestinationStock = Stock::where('branch_id', $toBranchId)
                ->where('product_id', $productId)
                ->where('purchase_order_id', $orderId)
                ->first();

            if ($existingDestinationStock) {
                // If stock exists, add to existing stock
                $existingDestinationStock->update([
                    'stock_quantity' => $existingDestinationStock->stock_quantity + $quantity
                ]);
                $newStock = $existingDestinationStock;
            } else {
                // 5. Create new stock entry for destination branch
                $newStock = Stock::create([
                    'branch_id' => $toBranchId,
                    'product_id' => $productId,
                    'purchase_order_id' => $orderId,
                    'stock_quantity' => $quantity,
                    'quantity_sold' => 0,
                    'organization_id' => auth()->user()->organization_id
                ]);
            }

        
            $originalStock->update([
                'stock_quantity' => $originalStock->stock_quantity - $quantity
            ]);


            StockMovement::create([
                'from_stock_id' => $stockId,
                'to_stock_id' => $newStock->id,
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'moved_by' => Auth::id(),
                'reason' => 'Branch Transfer',
                'organization_id' => auth()->user()->organization_id
            ]);

            DB::commit();


            return response()->json([
                'success' => true,
                'message' => 'Stock moved successfully',
            ]);

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Failed to move stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function editPriceAddMoreQty(Request $request)
    {
    
        $request->validate([
            'id' => 'required|integer|exists:stocks,id',
            'unit_selling_price' => 'required|numeric|min:0',
            'quantity_operation' => 'required',
            'expiry_date' => 'string'
        ]);

        
        $stock = Stock::findOrFail($request->id);
    
        $purchase_order = PurchaseOrder::findOrFail($stock->purchase_order_id);

        $quantity = $request->stock_quantity;
        $quantity_operation = $request->quantity_operation;

        
        $purchase_order->unit_selling_price = $request->unit_selling_price;
        $stock->expiry_date = $request->expiry_date;
        
        if($quantity_operation == "add"){
            $purchase_order->stock_quantity += $quantity;
            $stock->stock_quantity += $quantity;
        } else {
            $purchase_order->stock_quantity -= $quantity;
            $stock->stock_quantity -= $quantity;
        }



        

        $purchase_order->save();
        $stock->save();

        
        return response()->json([
            'purchase_order' => $purchase_order,
            'stock' => $stock
        ], 200);
    }



}
