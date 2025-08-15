<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Subscription;

class SubscriptionController extends Controller
{
    public function index(Request $request){

        $subscription = Subscription::where('organization_id', auth()->user()->organization_id)->first();

        return response()->json(compact('subscription'));
    }
}
