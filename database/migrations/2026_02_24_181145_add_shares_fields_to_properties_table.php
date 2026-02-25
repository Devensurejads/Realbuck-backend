<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {

            $table->integer('number_of_shares')
                  ->default(0)
                  ->after('PropertyPrice');

            $table->decimal('minimum_investment_amount', 12, 2)
                  ->default(0)
                  ->after('number_of_shares');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'number_of_shares',
                'minimum_investment_amount'
            ]);
        });
    }
};

