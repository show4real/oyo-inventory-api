<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\BarcodeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/ 
// Route::post('/register', [RegisterController::class, 'register']);
// Route::prefix('item')->group(function(){
//   Route::post('/{id}', [RegisterController::class, 'store']);
// });
Route::post('/register', 'Api\RegisterController@register');
Route::post('/login', 'Api\LoginController@login');
Route::get('/recoverpassword/{recovery_code}', 'Api\ForgotPasswordController@recover');
Route::post('update/client/payments', 'Api\ClientController@updateClientOnPayment');


Route::post('/test', 'Api\IpAddressController@index');

Route::post('/changepassword', 'Api\ForgotPasswordController@changepassword');
Route::post('/sendrecovery', 'Api\ForgotPasswordController@sendrecovery');
 Route::post('all-products', 'Api\ProductController@getAllProducts');


Route::group(['middleware'=>['jwt.auth','CheckAdmin','CheckSubscription']],
function(){

    Route::post('/games', 'Bookings\GameController@store');
    Route::put('/games/{game}', 'Bookings\GameController@update');
    Route::post('/delete-games/{game}', 'Bookings\GameController@destroy');
    Route::post('/deletebooking/{id}', 'Bookings\BookingController@destroy');




    Route::post('bookings/users', 'Bookings\UserController@index');
    Route::get('bookings/user/{user}', 'Bookings\UserController@show');
    Route::post('bookings/adduser', 'Bookings\UserController@save');
    Route::post('bookings/deleteuser/{user}', 'Bookings\UserController@delete');
    Route::post('bookings/searchusers', 'Bookings\UserController@search');
    Route::post('bookings/updateuser/{user}', 'Bookings\UserController@update');



    Route::post('dashboards', 'Api\DashboardController@index');
    Route::post('addcompany', 'Api\CompanySettingsController@save');
    Route::get('company', 'Api\CompanySettingsController@index');

    Route::post('users', 'Api\UserController@index');
    Route::get('user/{user}', 'Api\UserController@show');
    Route::post('adduser', 'Api\UserController@save');
    Route::post('deleteuser/{user}', 'Api\UserController@delete');
    Route::post('searchusers', 'Api\UserController@search');
    Route::post('updateuser/{user}', 'Api\UserController@update');


    Route::post('categories', 'Api\CategoryController@index');
    Route::post('addcategories', 'Api\CategoryController@save');
    Route::post('add/category', 'Api\CategoryController@addCategory');
    Route::post('updatecategory/{category}', 'Api\CategoryController@update');
    Route::get('categories/{category}', 'Api\CategoryController@show');
    Route::delete('categories/{category}', 'Api\CategoryController@delete');

    Route::post('addbranches', 'Api\BranchController@save');
    Route::post('updatebranch/{branch}', 'Api\BranchController@update');
    Route::get('branches/{branch}', 'Api\BranchController@show');
    Route::delete('branches/{branch}', 'Api\BranchController@delete');

  
    Route::post('addbrands', 'Api\BrandController@save');
    Route::post('updatebrand/{brand}', 'Api\BrandController@update');
    Route::get('brands/{brand}', 'Api\BrandController@show');
    Route::delete('brands/{brand}', 'Api\BrandController@delete');

    Route::prefix('barcodes')->group(function () {

        Route::post('/store', [BarcodeController::class, 'storeBarcodesFromPurchaseOrders']);
        Route::post('/generate', [BarcodeController::class, 'generateBarcodes']);
        Route::post('/xxx', [BarcodeController::class, 'getBarcodesWithUsage']);
    });
    
    Route::post('suppliers', 'Api\SupplierController@index');
    Route::post('allsuppliers', 'Api\SupplierController@all');
    Route::post('addsuppliers', 'Api\SupplierController@save');
    Route::post('updatesupplier/{supplier}', 'Api\SupplierController@update');
    Route::get('supplier/{supplier}', 'Api\SupplierController@show');
    Route::post('deletesupplier/{supplier}', 'Api\SupplierController@delete');

    Route::post('products', 'Api\ProductController@index');
    Route::post('product', 'Api\ProductController@save');
    Route::put('product/{product}', 'Api\ProductController@update');
    Route::get('product/{product}', 'Api\ProductController@show');
    Route::post('deleteproduct/{product}', 'Api\ProductController@delete');

    Route::post('product/image/{image}', 'Api\ProductImageController@store');


    Route::post('product/{product_id}/purchase_orders', 'Api\PurchaseOrderController@index');
    Route::post('purchase_orders', 'Api\PurchaseOrderController@purchaseOrders');
    
   
    Route::post('edit/stock/{stockId}', 'Api\NewStockController@update');
    Route::post('delete/stock/{user}', 'Api\NewStockController@delete');

  
    

   
    Route::post('sales', 'Api\PurchaseOrderController@getSalesOrder');
    Route::post('stocks2', 'Api\StockController@stocks2');
    

    Route::post('purchase_order', 'Api\PurchaseOrderController@stocks');
    Route::post('purchase_order/editprice', 'Api\PurchaseOrderController@editPrice');
    Route::post('purchase_order/add-more', 'Api\PurchaseOrderController@addMoreOrder');
    Route::post('purchase_order/add-barcode', 'Api\PurchaseOrderController@addBarcode');
    Route::post('purchase_order/add-more2', 'Api\PurchaseOrderController@addMoreOrder2');

    

    


    Route::post('sale_order/{order}', 'Api\PurchaseOrderController@saleOrder');
    Route::post('sale_order', 'Api\PurchaseOrderController@multSaleOrder');



    Route::post('pos_order', 'Api\PosController@multPosOrder');
    Route::post('edit/pos_order', 'Api\PosController@editMultPosOrder');

    
    
    Route::post('pos_sales', 'Api\PosController@getPosSales');
    Route::post('pos_transactions', 'Api\PosController@getPosTransactions');

    Route::post('transaction_details', 'Api\PosController@getTransactionDetails');

    Route::post('purchase_order', 'Api\PurchaseOrderController@save');
    Route::post('purchase_order/{order}', 'Api\PurchaseOrderController@show');
    Route::post('updatepurchase_order/{order}', 'Api\PurchaseOrderController@update');
    Route::delete('purchase_order/{order}', 'Api\PurchaseOrderController@delete');

    Route::post('invoices', 'Api\InvoiceController@index');
    Route::get('invoice/{invoice}', 'Api\InvoiceController@show');
    Route::get('last_invoice', 'Api\InvoiceController@lastInvoice');
    Route::post('addinvoice', 'Api\InvoiceController@save');
    Route::post('deleteinvoice/{invoice}', 'Api\InvoiceController@delete');
    Route::post('updateinvoice/{invoice}', 'Api\InvoiceController@update');

    Route::post('addpayment', 'Api\PaymentController@save');
    Route::post('updatepayment', 'Api\PaymentController@update');

    Route::post('clients', 'Api\ClientController@index');
     Route::post('all/clients', 'Api\ClientController@allClients');
    Route::get('client/{client}', 'Api\ClientController@show');
    Route::post('addclient', 'Api\ClientController@save');
    Route::post('deleteclient/{client}', 'Api\ClientController@delete');
    Route::post('updateclient/{client}', 'Api\ClientController@update');
    Route::post('cashiers', 'Api\ClientController@cashiers');

    Route::post('clients/payments', 'Api\ClientController@clientPayments');

    Route::post('creditors', 'Api\CreditorController@index');
    Route::get('creditor/{id}', 'Api\CreditorController@show');

    Route::post('add/creditor/payment', 'Api\CreditorPaymentController@save');
    Route::post('update/creditor/payment', 'Api\CreditorPaymentController@update');

    Route::post('add/expense', 'Api\ExpenseController@save');
    Route::post('expenses', 'Api\ExpenseController@index');
    Route::post('update/expense', 'Api\ExpenseController@update');

    }
  );

  Route::group(['middleware'=>['jwt.auth','CheckSubscription','CheckInventoryManager']],
    function(){

      Route::post('branches', 'Api\BranchController@index');
      Route::post('allbranches', 'Api\BranchController@allbranches');

      Route::post('pos/products', 'Api\PosController@products');

      Route::post('stocks', 'Api\StockController@stocks');
      Route::post('create/stocks', 'Api\NewStockController@save');
      Route::post('remove/expiry/stock', 'Api\NewStockController@removeExpiryStock');
      Route::post('restore/expiry/stock', 'Api\NewStockController@restoreExpiryStock');
      Route::post('deleted/expiry/stocks', 'Api\NewStockController@deletedExpiryStocks');
      Route::post('create/multstocks', 'Api\MultStockController@save');
      Route::post('stock/editprice', 'Api\NewStockController@editPriceAddMoreQty');
      Route::post('stock/{stock}', 'Api\StockController@show');
      Route::post('returnstock', 'Api\StockController@returnStock');
      Route::post('movestock', 'Api\NewStockController@moveStock');

      Route::post('products', 'Api\ProductController@index');
      Route::post('product', 'Api\ProductController@save');
      Route::put('product/{product}', 'Api\ProductController@update');
      Route::get('product/{product}', 'Api\ProductController@show');

      Route::post('confirm_order/{order}', 'Api\PurchaseOrderController@confirmOrder');
      Route::post('return_order/{order}', 'Api\PurchaseOrderController@returnOrder');
      Route::post('move_order/{order}', 'Api\PurchaseOrderController@moveOrder');
      Route::post('add-barcode', 'Api\PurchaseOrderController@addBarcode');
      
      
    }
  );


  Route::group(['middleware'=>['jwt.auth','CheckSubscription']],
  function(){
   
    Route::post('invoices2', 'Api\InvoiceController@index2');
    Route::get('invoice2/{invoice}', 'Api\InvoiceController@show2');
    Route::get('last_invoice', 'Api\InvoiceController@lastInvoice');
    Route::post('addinvoice', 'Api\InvoiceController@save');
    Route::post('updateinvoice/{invoice}', 'Api\InvoiceController@update');

    Route::post('addpayment', 'Api\PaymentController@save');
    Route::post('updatepayment', 'Api\PaymentController@update');

    Route::post('branch_stocks', 'Api\StockController@branchStocks');
    

    Route::post('clients', 'Api\ClientController@index');
    Route::get('client/{client}', 'Api\ClientController@show');
    Route::post('addclient', 'Api\ClientController@save');
    Route::post('deleteclient/{client}', 'Api\ClientController@delete');
    Route::post('updateclient/{client}', 'Api\ClientController@update');

    Route::post('cashiers', 'Api\ClientController@cashiers');

    //Route::post('branch_stocks', 'Api\StockController@branchStocks');
    Route::post('pos_order', 'Api\PosController@multPosOrder');
    Route::post('pos_sales2', 'Api\PosController@getPosSales2');
    Route::post('transaction_details2', 'Api\PosController@getTransactionDetails2');
    Route::get('company', 'Api\CompanySettingsController@index');


    
  });

  Route::group(['middleware'=>['jwt.auth']],
  function(){

    Route::post('subscription', 'Api\SubscriptionController@index');
    Route::post('dashboards', 'Api\DashboardController@index');
    Route::post('allgames', 'Bookings\GameController@index');
    Route::post('bookings', 'Bookings\BookingController@store');
    Route::post('all-bookings', 'Bookings\BookingController@index');

    Route::get('bookings/{id}', 'Bookings\BookingController@getBooking');
    Route::post('bookings/dashboards', 'Bookings\BookingController@dashboard');

    
  });




