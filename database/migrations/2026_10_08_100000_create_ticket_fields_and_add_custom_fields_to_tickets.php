<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('ticket_fields')) {
            Schema::create('ticket_fields', function (Blueprint $table) {
                $table->id();
                $table->string('field_name')->unique();
                $table->string('label');
                $table->string('field_type')->default('text'); // text, number, select, textarea, file, date
                $table->json('options')->nullable();
                $table->boolean('is_required')->default(false);
                $table->boolean('show_in_form')->default(true);
                $table->boolean('show_in_list')->default(true);
                $table->integer('sort_order')->default(0);
                $table->string('help_text')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('tickets') && !Schema::hasColumn('tickets', 'custom_fields')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->json('custom_fields')->nullable()->after('feedback');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tickets') && Schema::hasColumn('tickets', 'custom_fields')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('custom_fields');
            });
        }

        Schema::dropIfExists('ticket_fields');
    }
};
