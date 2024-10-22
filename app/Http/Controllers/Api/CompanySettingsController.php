<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CompanySettings;
use Str;
use Storage;

class CompanySettingsController extends Controller
{
    public function index(Request $request){
        $company = CompanySettings::first();
        return response()->json(compact('company'));

    }

  

    public function save(Request $request){
        $company= CompanySettings::first();
        $folder = "/logo/";
        $data = $request->data;
       
        if($data){
          
            $img=preg_replace('#^data:image/[^;]+;base64,#', '', $data);
            $type=explode(';',$data)[0];
            $type=explode('/',$type)[1];
            $name=Str::random(28);
            $path = $folder . $name.'.'.$type;
            $url=url('/');
            Storage::disk('public')->put($path,base64_decode($img));
            $company->logo_url = $url."/storage".$path;
        }
       
        $company->currency = $request->currency;
        $company->country = $request->country;
        $company->city = $request->city;
        $company->website = $request->website;
        $company->invoice_header = $request->invoice_header;
        $company->address = $request->address;
        $company->name = $request->name;
        $company->email = $request->email;
        $company->phone_one = $request->phone_one;
        $company->phone_two = $request->phone_two;
        $company->invoice_footer_one = $request->invoice_footer_one;
        $company->invoice_footer_two = $request->invoice_footer_two;
        $company->cashier_daily_filter = $request->cashier_daily_filter;
        $company->sell_by_serial_no = $request->sell_by_serial_no;
        $company->save();
        return response()->json(compact('company'));

        // $company = CompanySettings::updateOrCreate(
        //     ['id', 1],
               
        //     [   'logo_url'=>$url."/storage".$path,
        //         'currency'=>$request->currency,
        //         'country' => $request->country,
        //        'city' => $request->city,
        //        'address' => $request->address,
        //        'name' => $request->name,
        //        'email' => $request->email,
        //        'phone_one' => $request->phone_one,
        //        'phone_two' => $request->phone_two,
        //        'invoice_footer_one' => $request->invoice_footer_one,
        //        'invoice_footer_two' => $request->invoice_footer_two,
        //     ]
        // );
        
    }
    
}
