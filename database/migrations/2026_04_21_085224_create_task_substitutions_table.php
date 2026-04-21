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
        Schema::create('task_substitutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('weekly_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('original_member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('substitute_member_id')->constrained('members')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(['vacation_id', 'weekly_task_id']);
        });

        DB::statement(
            "ALTER TABLE task_substitutions
                ADD CONSTRAINT task_substitutions_status_allowed_values
                CHECK (status IN ('pending', 'assigned'))"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_substitutions');
    }
};
