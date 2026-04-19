<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 32)->unique();
            $table->string('name', 100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Заменим строковую category на foreign key
        Schema::table('faq_items', function (Blueprint $table) {
            $table->foreignId('faq_category_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('faq_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('faq_category_id');
        });

        Schema::dropIfExists('faq_categories');
    }
};
