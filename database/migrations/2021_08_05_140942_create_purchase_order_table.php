<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('product_attributes')->nullable();
            $table->string('product_attributes_keys')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            //$table->unsignedBigInteger('branch_id')->nullable();
            $table->bigInteger('stock_quantity')->nullable();
            $table->bigInteger('quantity_moved')->nullable();
            $table->bigInteger('quantity_returned')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('status')->nullable();
            $table->string('barcode')->nullable();
            $table->string('currency')->nullable();
            $table->bigInteger('unit_price')->nullable();
            $table->bigInteger('unit_selling_price')->nullable();
            $table->bigInteger('amount')->nullable();
            $table->unsignedBigInteger('initiator_id')->nullable();
            $table->unsignedBigInteger('verifier_id')->nullable();
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('billing_id')->nullable();
            $table->string('tracking_id')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('received_at')->nullable();   
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_order');
    }
}
