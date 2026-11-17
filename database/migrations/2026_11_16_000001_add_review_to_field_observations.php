<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('field_observations', function (Blueprint $table) {
            $table->string('review_status', 20)->default('pendiente')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
        });

        Schema::create('field_observation_review_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_observation_id')->constrained('field_observations')->restrictOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->string('previous_status', 20);
            $table->string('new_status', 20);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['field_observation_id', 'created_at'], 'field_review_events_observation_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_observation_review_events');
        Schema::table('field_observations', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['review_status', 'reviewed_by', 'reviewed_at', 'review_note']);
        });
    }
};
