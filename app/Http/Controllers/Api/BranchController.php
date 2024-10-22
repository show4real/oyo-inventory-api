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
        $branches = Branch::withCount('stocks')
            ->with('stocks')
            ->search($request->search)
            ->paginate($request->rows, ['*'], 'page', $request->page);
        return response()->json(compact('branches'));
    }

    public function show(Branch $branch){
        $branch = Branch::where('id', $branch->id)->first();
        return response()->json(compact('branch'));
    }

    public function save(Request $request){
       
        foreach($request->name as $values) {

            $branches[] = Branch::updateOrCreate(
                ['name' => $values, 
                ],

                ['name'=>$values,
                'branch_id'=>"BRCH-TRK-" . strtoupper(Str::random(10))
                ]
            );
        }

        return response()->json(compact('branches'));
    }

    public function update(Request $request, Branch $branch){

        $validator = Validator::make($request->all(), [
            'name' => 'unique:branches,name,'. $branch->id
        ]);

        if($validator->fails()){
          return response()->json($validator->messages(), 422);
        }
        $branch->name = $request->name;
        $branch->save();
        return response()->json(compact('branch'));
    }

    public function delete(Branch $branch){
        $branch->delete();
        return response()->json(true);
    }
}
