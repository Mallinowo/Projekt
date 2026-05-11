<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female', 'nonbinary', 'other'])
                ->default('other')
                ->after('city');
            $table->enum('orientation', ['hetero', 'homo', 'bi', 'other'])
                ->default('bi')
                ->after('gender');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gender', 'orientation']);
        });
    }
};
