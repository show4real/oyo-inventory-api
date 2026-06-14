<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Str;
use App\Branch;
use App\Stock;


class BranchController extends Controller
{
    public function index(Request $request){
        // stocks_count = total units actually available in the branch, not the
        // number of stock rows. Counting rows overstated the figure: rows that
        // are fully sold or fully moved out to another branch stay in the table
        // (stock_quantity hits 0 but the row is never deleted), so a row count
        // never dropped after a transfer. We sum the real available quantity
        // (stock_quantity - sold - returned - saved) instead, reusing the same
        // expression the Stock model uses for in-stock filtering.
        $availableQty = Stock::availableQtyExpression();

        $branches = Branch::where('organization_id', auth()->user()->organization_id)
            ->select('branches.*')
            ->selectSub(function ($query) use ($availableQty) {
                $query->from('stocks')
                    ->whereColumn('stocks.branch_id', 'branches.id')
                    ->whereNull('stocks.deleted_at')
                    ->selectRaw('COALESCE(SUM(' . $availableQty . '), 0)');
            }, 'stocks_count')
            ->search($request->search)
            ->paginate($request->rows, ['*'], 'page', $request->page);
        return response()->json(compact('branches'));
    }

    public function allbranches(Request $request){
        $branches = Branch::where('organization_id', auth()->user()->organization_id)->select('id','name')->get();
        return response()->json(compact('branches'));
    }

    public function show(Branch $branch){
        $branch = Branch::where('id', $branch->id)->first();
        return response()->json(compact('branch'));
    }

    public function save(Request $request)
    {
        // Simple validation - just unique name
        $request->validate([
            'name' => 'required|unique:branches,name,NULL,id,organization_id,' . auth()->user()->organization_id,
        ], [
            'name.required' => 'Branch name is required',
            'name.unique' => 'A branch with this name already exists'
        ]);

        $branch = new Branch();
        $branch->organization_id = auth()->user()->organization_id;
        $branch->name = $request->name;
        $branch->sell = $request->sell;
        $branch->branch_id = "BRCH-TRK-" . strtoupper(Str::random(10));
        $branch->save();

        return response()->json(compact('branch'));
    }

    public function update(Request $request, Branch $branch){

        $validator = Validator::make($request->all(), [
            'name' => 'unique:branches,name,'. $branch->id
        ]);

        if($validator->fails()){
          return response()->json($validator->messages(), 422);
        }
        $branch->name = $request->name;
        $branch->sell = $request->sell;
        $branch->save();
        return response()->json(compact('branch'));
    }

    public function delete(Branch $branch){
        $branch->delete();
        return response()->json(true);
    }
}
