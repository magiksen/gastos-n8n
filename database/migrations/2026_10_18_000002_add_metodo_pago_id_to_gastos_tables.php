<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->foreignId('metodo_pago_id')->nullable()->after('moneda')->constrained('metodos_pago')->nullOnDelete();
        });

        Schema::table('gastos_mensuales', function (Blueprint $table) {
            $table->foreignId('metodo_pago_id')->nullable()->after('fecha_pago')->constrained('metodos_pago')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gastos_mensuales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('metodo_pago_id');
        });

        Schema::table('gastos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('metodo_pago_id');
        });
    }
};
