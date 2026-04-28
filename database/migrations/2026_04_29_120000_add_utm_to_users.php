<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('utm_source', 128)->nullable()->after('chance_modifier');
            $table->string('utm_medium', 128)->nullable()->after('utm_source');
            $table->string('utm_campaign', 128)->nullable()->after('utm_medium');
            $table->string('utm_content', 128)->nullable()->after('utm_campaign');
            $table->string('utm_term', 128)->nullable()->after('utm_content');
            $table->string('referrer', 512)->nullable()->after('utm_term');
            $table->string('registration_ip', 45)->nullable()->after('referrer');

            $table->index('utm_source');
            $table->index('utm_campaign');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['utm_source']);
            $table->dropIndex(['utm_campaign']);
            $table->dropColumn([
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_content',
                'utm_term',
                'referrer',
                'registration_ip',
            ]);
        });
    }
};
