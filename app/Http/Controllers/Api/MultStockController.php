<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Str;
use App\PurchaseOrder;
use App\Product;
use App\Stock;
use App\StockMovement;
use App\Branch;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class MultStockController extends Controller
{
    public function save(Request $request)
    {
        // Validate the request
        $request->validate([
            'invoice_no' => 'nullable|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'stock_items' => 'required|array|min:1',
            'stock_items.*.product_id' => 'required|exists:products,id',
            'stock_items.*.unit_price' => 'required|numeric|min:0.01',
            'stock_items.*.unit_selling_price' => 'required|numeric|min:0.01',
            'stock_items.*.stock_quantity' => 'required|integer|min:1',
            'stock_items.*.barcode' => 'nullable|string|max:255',
            //'stock_items.*.expiry_date' => 'nullable|date|after:today',
            //'stock_items.*.supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $createdPurchaseOrders = [];
        $createdStocks = [];
        
        // Use database transaction to ensure data consistency
        DB::beginTransaction();
        
        try {
            foreach ($request->stock_items as $stockItem) {
                // Get product with supplier information
                $product = Product::select('id', 'supplier_id')->findOrFail($stockItem['product_id']);

                // Create purchase order for each stock item
                $purchaseOrder = PurchaseOrder::create([
                    'product_id'         => $product->id,
                    'unit_price'         => $stockItem['unit_price'],
                    'unit_selling_price' => $stockItem['unit_selling_price'],
                    'barcode'            => $stockItem['barcode'] ?? null,
                    'supplier_id'        => $stockItem['supplier_id'] ?? $product->supplier_id,
                    'stock_quantity'     => $stockItem['stock_quantity'],
                    'quantity_moved'     => $stockItem['stock_quantity'],
                    'tracking_id'        => 'TRK-' . strtoupper(Str::random(8)),
                    'invoice_no'         => $request->invoice_no,
                    'organization_id'    => auth()->user()->organization_id,
                    'received_at'        => now(),
                    'confirmed_at'       => now(),
                ]);

                // Create or update stock for each item
                $stock = Stock::updateOrCreate(
                    [
                        'purchase_order_id' => $purchaseOrder->id,
                        'branch_id'         => $request->branch_id,
                    ],
                    [
                        'stock_quantity'   => $purchaseOrder->quantity_moved,
                        'product_id'       => $purchaseOrder->product_id,
                        'supplier_id'      => $purchaseOrder->supplier_id,
                        'organization_id'  => auth()->user()->organization_id,
                        'expiry_date'      => $stockItem['expiry_date'] ?? null,
                        'barcode'          => $stockItem['barcode'] ?? null,
                        'unit_price'       => $stockItem['unit_price'],
                        'unit_selling_price' => $stockItem['unit_selling_price'],
                        'invoice_no' => $request->invoice_no,
                    ]
                );

                $createdPurchaseOrders[] = $purchaseOrder;
                $createdStocks[] = $stock;
            }

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Stock items created successfully',
                'invoice_no' => $request->invoice_no,
                'total_items' => count($createdPurchaseOrders),
                'purchase_orders' => $createdPurchaseOrders,
                'stocks' => $createdStocks,
            ], 201);

        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollback();
            
            return response()->json([
                'error' => 'Failed to create stock items',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
