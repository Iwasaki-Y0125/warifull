<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_task_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('weekly_task_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('skill_level');
            $table->timestamps();

            $table->unique(['member_id', 'weekly_task_id']);
        });

        DB::statement(
            'ALTER TABLE member_task_skills
                ADD CONSTRAINT member_task_skills_skill_level_between_0_and_3
                CHECK (skill_level BETWEEN 0 AND 3)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_task_skills');
    }
};
