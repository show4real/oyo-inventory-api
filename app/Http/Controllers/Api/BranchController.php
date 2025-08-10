<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Str;
use App\Branch;


class BranchController extends Controller
{
    public function index(Request $request){
        $branches = Branch::where('organization_id', auth()->user()->organization_id)->withCount('stocks')
            //->with('stocks')
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
