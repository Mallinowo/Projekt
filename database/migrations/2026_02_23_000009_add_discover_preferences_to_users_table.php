<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('preferred_age_min')->default(18)->after('age');
            $table->unsignedTinyInteger('preferred_age_max')->default(99)->after('preferred_age_min');
            $table->unsignedSmallInteger('preferred_max_distance_km')->default(120)->after('city');
            $table->json('preferred_subcultures')->nullable()->after('preferred_max_distance_km');
            $table->decimal('city_lat', 10, 7)->nullable()->after('preferred_subcultures');
            $table->decimal('city_lng', 10, 7)->nullable()->after('city_lat');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_age_min',
                'preferred_age_max',
                'preferred_max_distance_km',
                'preferred_subcultures',
                'city_lat',
                'city_lng',
            ]);
        });
    }
};
