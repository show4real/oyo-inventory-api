<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantitySavedToStocksTable extends Migration
{
    public function up()
    {
        Schema::table('stocks', function (Blueprint $table) {
            if (! Schema::hasColumn('stocks', 'quantity_saved')) {
                $table->integer('quantity_saved')->default(0)->after('quantity_sold');
            }
        });
    }

    public function down()
    {
        Schema::table('stocks', function (Blueprint $table) {
            if (Schema::hasColumn('stocks', 'quantity_saved')) {
                $table->dropColumn('quantity_saved');
            }
        });
    }
}