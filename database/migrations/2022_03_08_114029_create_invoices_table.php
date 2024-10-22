<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('purchase_order_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('currency')->nullable();
            $table->unsignedBigInteger('cashier_id')->nullable();
            $table->integer('client_id')->nullable();
            $table->string('issued_date')->nullable();
            $table->string('due_date')->nullable();
            $table->integer('amount')->nullable();
            $table->integer('amount_paid')->nullable();
            $table->integer('balance')->nullable();
            $table->integer('payment_status')->nullable();
            $table->string('description')->nullable();
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
        Schema::dropIfExists('invoices');
    }
}
