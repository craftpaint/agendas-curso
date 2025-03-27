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
		Schema::create('tb_vehiculo', function (Blueprint $table) {
			$table->id('id_vehiculo'); // Clave primaria
			$table->unsignedBigInteger('id_cliente'); // Clave foránea a tb_cliente
			$table->string('tipo_vehiculo', 45)->nullable(); // Tipo de vehículo
			$table->string('placa_vehiculo', 45)->nullable(); // Placa del vehículo
			$table->string('modelo_vehiculo', 45)->nullable(); // Modelo del vehículo
			$table->timestamps();

			// Relaciones
			$table->foreign('id_cliente')->references('id_cliente')->on('tb_cliente')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('tb_vehiculo');
	}
};
