<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['match_id', 'id'], 'messages_match_id_id_idx');
            $table->index(['match_id', 'read_at'], 'messages_match_id_read_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_match_id_id_idx');
            $table->dropIndex('messages_match_id_read_at_idx');
        });
    }
};
