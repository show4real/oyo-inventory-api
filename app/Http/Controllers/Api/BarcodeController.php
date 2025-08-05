<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Barcode;
use App\Stock;
use App\PurchaseOrder;
use Illuminate\Support\Str;

class BarcodeController extends Controller
{

    public function fetchUniquePurchaseOrderBarcodes()
    {
        $barcodes = PurchaseOrder::whereHas('stock')
            ->whereNotNull('barcode')
            ->distinct()
            ->pluck('barcode');

        return response()->json($barcodes);
    }

    
    public function storeBarcodesFromPurchaseOrders()
    {
        $barcodes = PurchaseOrder::whereHas('stock')
            ->whereNotNull('barcode')
            ->distinct()
            ->select('barcode','organization_id')
            ->get();

        

        foreach ($barcodes as $code) {
            if (!Barcode::where('name', $code->barcode)->exists()) {
                Barcode::create(['name' => $code->barcode, 'organization_id' => $code->organization_id]);
            }
        }

        return response()->json(['message' => 'Barcodes stored successfully.']);
    }

    
    public function generateBarcodes(Request $request)
    {
        $count = $request->input('count', 1);
        $organization_id = auth()->user()->organization_id;
        $barcodes = [];

        for ($i = 0; $i < $count; $i++) {
            do {
                $timestampPart = substr((string) now()->timestamp, -8);
                $randomPart = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $newBarcode = $timestampPart . $randomPart;
            } while (Barcode::where('name', $newBarcode)->exists() || in_array($newBarcode, $barcodes));

            Barcode::create([
                'name' => $newBarcode,
                'organization_id' => $organization_id,
            ]);

            $barcodes[] = $newBarcode;
        }

        return response()->json(['generated_barcodes' => $barcodes]);
    }


    
    public function getBarcodesWithUsage(Request $request)
    {
        $organization_id = auth()->user()->organization_id;
        $perPage = $request->input('rows', 10);

        // Get paginated barcodes
        $barcodes = Barcode::where('organization_id', $organization_id)
            ->withCount('orderItems')
            ->paginate($request->rows, ['*'], 'page', $request->page);

            return response()->json(compact('barcodes'));
    }

}