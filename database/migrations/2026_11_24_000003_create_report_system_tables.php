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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->nullOnDelete();
            $table->string('name');
            $table->string('code', 30);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hotel_id', 'is_active']);
        });

        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->nullOnDelete();
            $table->string('name');
            $table->json('config');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hotel_id', 'is_default']);
        });

        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('report_template_id')->nullable()->constrained('report_templates')->nullOnDelete();
            $table->date('report_date');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('draft'); // draft, completed
            $table->text('guest_experience')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'report_date']);
            $table->index(['status', 'report_date']);
        });

        Schema::create('report_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained('daily_reports')->cascadeOnDelete();
            $table->string('tab_key', 50); // general_information, guest_experience, departmental_activities, operational_updates, activities_works_plan
            $table->string('section_key', 50);
            $table->string('title');
            $table->string('field_type', 20)->default('list'); // list or text
            $table->integer('sort_order')->default(0);
            $table->text('content')->nullable();
            $table->timestamps();

            $table->index(['daily_report_id', 'tab_key']);
            $table->index(['daily_report_id', 'section_key']);
        });

        Schema::create('report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_section_id')->constrained('report_sections')->cascadeOnDelete();
            $table->text('content');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['report_section_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_items');
        Schema::dropIfExists('report_sections');
        Schema::dropIfExists('daily_reports');
        Schema::dropIfExists('report_templates');
        Schema::dropIfExists('departments');
    }
};
