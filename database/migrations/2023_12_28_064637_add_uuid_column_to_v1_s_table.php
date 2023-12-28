<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('v1_s', function (Blueprint $table) {
            $table->string('uuid')->comment('this is uuid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v1_s', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
