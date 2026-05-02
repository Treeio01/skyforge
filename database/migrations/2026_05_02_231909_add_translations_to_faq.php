<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faq_items', function (Blueprint $table) {
            $table->string('question_en', 500)->nullable()->after('question');
            $table->text('answer_en')->nullable()->after('answer');
        });

        Schema::table('faq_categories', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('faq_items', function (Blueprint $table) {
            $table->dropColumn(['question_en', 'answer_en']);
        });

        Schema::table('faq_categories', function (Blueprint $table) {
            $table->dropColumn('name_en');
        });
    }
};
