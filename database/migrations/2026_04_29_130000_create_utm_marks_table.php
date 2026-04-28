<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utm_marks', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('name', 128)->nullable();
            $table->string('utm_source', 128)->nullable();
            $table->string('utm_medium', 128)->nullable();
            $table->string('utm_campaign', 128)->nullable();
            $table->string('utm_content', 128)->nullable();
            $table->string('utm_term', 128)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_admin_created')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('utm_source');
            $table->index('utm_campaign');
            $table->index(['is_active', 'is_admin_created']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('utm_mark_id')
                ->nullable()
                ->after('chance_modifier')
                ->constrained('utm_marks')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['utm_mark_id']);
            $table->dropColumn('utm_mark_id');
        });

        Schema::dropIfExists('utm_marks');
    }
};
