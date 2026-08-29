<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->index(['exam_id', 'user_id', 'status']);
            $table->dropUnique('attempts_exam_id_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropIndex(['exam_id', 'user_id', 'status']);
            $table->unique(['exam_id', 'user_id']);
        });
    }
};
