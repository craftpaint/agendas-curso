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
		Schema::create('tb_horario', function (Blueprint $table) {
			$table->id('id_horario'); // Clave primaria
			$table->string('rango_horario', 45)->nullable(); // Rango horario
			$table->time('inicio_horario')->nullable(); // Hora de inicio
			$table->time('fin_horario')->nullable(); // Hora de fin
			$table->timestamps(); // Campos created_at y updated_at
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('tb_horario');
	}
};
