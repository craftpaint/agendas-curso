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
		Schema::create('tb_sede', function (Blueprint $table) {
			$table->id('id_sede'); // Clave primaria
			$table->string('idrun_sede', 45)->nullable(); // RUN de la sede
			$table->string('nombre_sede', 45)->nullable(); // Nombre de la sede
			$table->longText('direccion_sede')->nullable(); // Dirección de la sede
			$table->string('tel_sede', 45)->nullable(); // Teléfono de la sede
			$table->string('estado_sede', 45)->nullable(); // Estado de la sede
			$table->longText('festivos_sede')->nullable(); // Festivos de la sede
			$table->unsignedBigInteger('id_servicio'); // Clave foránea a tb_servicio
			$table->timestamps();

			// Relaciones
			$table->foreign('id_servicio')->references('id_servicio')->on('tb_servicio')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('tb_sede');
	}
};
