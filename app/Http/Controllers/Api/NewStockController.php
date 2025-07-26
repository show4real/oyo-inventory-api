<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Str;
use App\PurchaseOrder;
use App\Product;
use App\Stock;

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
            ]
        );

        return response()->json(['purchase_order' => $purchaseOrder], 200);
    }

    public function delete($id, Request $request)
    {
        
        $stock = Stock::where('id', $id)
            ->where(function ($query) {
                $query->whereNull('quantity_sold')->orWhere('quantity_sold', 0);
            })
            ->firstOrFail();

        
        $purchaseOrder = PurchaseOrder::find($stock->purchase_order_id);

        
        $stock->delete();
        
        if ($purchaseOrder) {
            $purchaseOrder->delete();
        }

        return response()->json(true);
    }

}
