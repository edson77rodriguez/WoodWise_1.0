<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('field_observations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            // La base heredada utiliza id_parcela, no id.
            $table->unsignedBigInteger('id_parcela');
            $table->foreign('id_parcela')->references('id_parcela')->on('parcelas')->restrictOnDelete();
            $table->char('grupo', 1); // A: IA, B: censo, C: remedición
            $table->string('tree_id', 80)->nullable();
            $table->string('candidate_id', 80)->nullable();
            $table->json('payload'); // instantánea auditada de variables V0.8A
            $table->char('payload_sha256', 64);
            $table->timestamp('client_created_at');
            $table->timestamp('synced_at');
            $table->timestamps();
            $table->index(['id_parcela', 'grupo']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_observations');
    }
};
