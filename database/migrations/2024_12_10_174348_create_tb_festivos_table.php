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
		Schema::create('tb_festivos', function (Blueprint $table) {
			$table->id(); // Clave primaria autoincremental
			$table->string('fecha', 500)->nullable(); // Fecha como cadena de texto
			$table->timestamps(); // Crea 'created_at' y 'updated_at'
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('tb_festivos');
	}
};
