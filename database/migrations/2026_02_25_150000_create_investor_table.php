<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('investor', function (Blueprint $table) {
            $table->increments('id');
            $table->string('full_name', 150);
            $table->string('email', 150);
            $table->string('password', 255);
            $table->string('country_code', 20)->nullable();
            $table->string('phone_number', 100)->nullable();
            $table->text('property_type')->nullable();
            $table->text('location')->nullable();
            $table->string('investment_amount', 150)->nullable();
            $table->string('referred_by', 150)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('investor');
    }
};