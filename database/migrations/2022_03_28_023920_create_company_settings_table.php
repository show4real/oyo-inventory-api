<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('logo_url')->nullable();
            $table->string('invoice_header')->nullable();
            $table->string('website')->nullable();
            $table->string('currency')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_one')->nullable();
            $table->string('phone_two')->nullable();
            $table->string('address')->nullable();
            $table->string('invoice_footer_one')->nullable();
            $table->string('invoice_footer_two')->nullable();
            $table->integer('cashier_daily_filter')->nullable();
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
        Schema::dropIfExists('company_settings');
    }
}
