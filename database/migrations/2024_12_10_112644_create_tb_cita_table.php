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
		Schema::create('tb_cita', function (Blueprint $table) {
			$table->id('id_cita'); // Clave primaria
			$table->unsignedBigInteger('id_cliente'); // Clave foránea hacia cliente
			$table->unsignedBigInteger('id_sede')->nullable(); // Clave foránea hacia sede
			$table->unsignedBigInteger('id_estado'); // Clave foránea hacia estado
			$table->dateTime('reserva_cita')->nullable(); // Fecha y hora
			$table->string('rango_horario', 250)->nullable(); // Texto de hasta 250 caracteres
			$table->longText('desc_cita')->nullable(); // Texto largo para descripción
			$table->boolean('alert')->default(0); // Alerta como booleano (tinyint)
			$table->timestamps(); // Crea 'created_at' y 'updated_at'

			// Claves foráneas
			$table->foreign('id_cliente')->references('id_cliente')->on('tb_cliente')->onDelete('cascade');
			$table->foreign('id_estado')->references('id_estado')->on('tb_estado')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('tb_cita');
	}
};
