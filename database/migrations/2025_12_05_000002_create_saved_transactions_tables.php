<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavedTransactionsTables extends Migration
{
    public function up()
    {
        Schema::create('saved_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transaction_id')->index()->unique();
            $table->unsignedBigInteger('organization_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('saved_transaction_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('saved_transaction_id')->index();
            $table->unsignedBigInteger('stock_id')->nullable()->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->integer('quantity')->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('saved_transaction_id')->references('id')->on('saved_transactions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('saved_transaction_items');
        Schema::dropIfExists('saved_transactions');
    }
}