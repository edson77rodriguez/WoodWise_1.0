<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('field_mission_revision_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_mission_id')->constrained('field_missions')->restrictOnDelete();
            $table->foreignId('edited_by')->constrained('users')->restrictOnDelete();
            $table->unsignedSmallInteger('previous_count');
            $table->unsignedSmallInteger('new_count');
            $table->string('previous_title', 120);
            $table->string('new_title', 120);
            $table->json('added_ids');
            $table->json('removed_ids');
            $table->char('before_sha256', 64);
            $table->char('after_sha256', 64);
            $table->string('reason', 500);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['field_mission_id', 'created_at'], 'field_mission_revision_events_history');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_mission_revision_events');
    }
};
