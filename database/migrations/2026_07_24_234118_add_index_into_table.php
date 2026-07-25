<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("houses", function (Blueprint $t) {
            $t->index("no_rumah");
        });

        Schema::table("residents", function (Blueprint $t) {
            $t->index("name");
            $t->index("phone_number");
            $t->index("resident_status");
        });

        Schema::table("monthly_payments", function (Blueprint $t) {
            $t->index("type_payment");
            $t->index("month");
            $t->index("year");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
