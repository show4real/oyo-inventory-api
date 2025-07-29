
<?php

// 1. Migration for stock_movements table
// Create migration: php artisan make:migration create_stock_movements_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMovementsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('from_stock_id');
            $table->unsignedBigInteger('to_stock_id');
            $table->unsignedBigInteger('from_branch_id');
            $table->unsignedBigInteger('to_branch_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('organization_id');
            $table->integer('quantity');
            $table->unsignedBigInteger('moved_by');
            $table->string('reason')->default('Branch Transfer');
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_movements');
    }
}
