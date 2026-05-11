<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `messages` MODIFY `body` TEXT NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE `messages` SET `body` = '' WHERE `body` IS NULL");
        DB::statement('ALTER TABLE `messages` MODIFY `body` TEXT NOT NULL');
    }
};
