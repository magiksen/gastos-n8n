<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gastos_mensuales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_id')->constrained('gastos')->cascadeOnDelete();
            $table->unsignedSmallInteger('mes');
            $table->unsignedSmallInteger('anio');
            $table->boolean('pagado')->default(false);
            $table->date('fecha_pago')->nullable();
            $table->string('comprobante_path')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['gasto_id', 'mes', 'anio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos_mensuales');
    }
};
