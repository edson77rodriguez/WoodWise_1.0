<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_sync_notifications', function (Blueprint $table) {
            $table->id();

            /*
             * Identificador estable enviado por la app.
             * Un mismo sync_id puede involucrar varios productores.
             */
            $table->string('sync_id', 64);

            $table->unsignedBigInteger('id_tecnico');
            $table->unsignedBigInteger('id_productor');

            /*
             * Resumen exacto que posteriormente recibirá n8n.
             */
            $table->json('payload');

            /*
             * Permite detectar si alguien intenta reutilizar
             * el mismo sync_id con contenido diferente.
             */
            $table->char('payload_hash', 64);

            /*
             * Estados futuros:
             * pending | processing | notified | failed
             */
            $table
                ->string('status', 20)
                ->default('pending');

            $table
                ->unsignedInteger('attempts')
                ->default(0);

            $table
                ->timestamp('notified_at')
                ->nullable();

            $table
                ->text('last_error')
                ->nullable();

            $table->timestamps();

            /*
             * Regla principal de idempotencia:
             * un Productor solamente puede tener una notificación
             * para el mismo evento de sincronización.
             */
            $table->unique(
                ['sync_id', 'id_productor'],
                'mobile_sync_notifications_sync_productor_unique'
            );

            $table->index('status');
            $table->index('id_tecnico');
            $table->index('id_productor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'mobile_sync_notifications'
        );
    }
};