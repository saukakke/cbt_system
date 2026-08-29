<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->boolean('allow_retake')->default(false)->after('is_published');
            $table->unsignedSmallInteger('attempt_limit')->nullable()->after('allow_retake');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['allow_retake', 'attempt_limit']);
        });
    }
};
