<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('field_crown_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_mission_id')->constrained('field_missions')->restrictOnDelete();
            $table->string('operational_id', 100);
            $table->foreignId('field_observation_id')->nullable()->constrained('field_observations')->restrictOnDelete();
            $table->string('decision', 28); // confirmada | dudosa | sin_correspondencia
            $table->string('evidence_type', 32);
            $table->text('justification');
            $table->foreignId('reviewed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at');
            $table->timestamps();
            $table->unique(['field_mission_id', 'operational_id'], 'field_crown_one_current_decision');
            // NULL permite múltiples copas sin correspondencia; un registro no puede vincularse
            // con dos copas distintas dentro de una misma misión.
            $table->unique(['field_mission_id', 'field_observation_id'], 'field_crown_one_observation_per_mission');
            $table->index(['field_mission_id', 'decision'], 'field_crown_links_mission_decision_idx');
        });

        Schema::create('field_crown_link_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_crown_link_id')->constrained('field_crown_links')->restrictOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('previous_observation_id')->nullable()->constrained('field_observations')->restrictOnDelete();
            $table->foreignId('new_observation_id')->nullable()->constrained('field_observations')->restrictOnDelete();
            $table->string('previous_decision', 28)->nullable();
            $table->string('new_decision', 28);
            $table->string('evidence_type', 32);
            $table->text('justification');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['field_crown_link_id', 'created_at'], 'field_crown_link_history_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_crown_link_events');
        Schema::dropIfExists('field_crown_links');
    }
};
