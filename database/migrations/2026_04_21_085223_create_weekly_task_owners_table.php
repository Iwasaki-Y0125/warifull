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
        Schema::create('weekly_task_owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('main');
            $table->timestamps();
        });

        DB::statement(
            "ALTER TABLE weekly_task_owners
                ADD CONSTRAINT weekly_task_owners_role_allowed_values
                CHECK (role IN ('main', 'sub'))"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_task_owners');
    }
};
