<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void
    {
        Schema::table('bukti', function (Blueprint $table) {
            $table->string('path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bukti', function (Blueprint $table) {
            $table->string('path')->nullable(false)->change();
        });
    }
};

