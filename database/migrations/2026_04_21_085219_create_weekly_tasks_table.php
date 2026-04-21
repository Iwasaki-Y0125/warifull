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
        Schema::create('weekly_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->unsignedTinyInteger('weekday');
            $table->time('start_time');
            $table->timestamps();
        });

        DB::statement(
            'ALTER TABLE weekly_tasks
                ADD CONSTRAINT weekly_tasks_weekday_between_1_and_5
                CHECK (weekday BETWEEN 1 AND 5)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_tasks');
    }
};
