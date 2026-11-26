<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('field_mission_manual_targets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('field_mission_id')->constrained('field_missions')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('geometry_type', 12);
            $table->json('geometry');  // Coordenadas métricas en el CRS UTM de la misión.
            $table->string('crs', 32);
            $table->string('probable_species', 3)->default('ND');
            $table->string('reason', 30);
            $table->string('priority', 12)->default('normal');
            $table->string('status', 12)->default('active');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['field_mission_id', 'status'], 'fm_manual_mission_status_idx');
        });

        Schema::create('field_mission_manual_target_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_target_id')->constrained('field_mission_manual_targets')->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('action', 20);
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->text('justification')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['manual_target_id', 'created_at'], 'fm_manual_events_target_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_mission_manual_target_events');
        Schema::dropIfExists('field_mission_manual_targets');
    }
};
