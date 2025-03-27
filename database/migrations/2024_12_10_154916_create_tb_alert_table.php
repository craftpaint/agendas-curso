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
		Schema::create('tb_alert', function (Blueprint $table) {
			$table->id(); // Clave primaria
			$table->unsignedBigInteger('id_cita')->nullable(); // Clave foránea
			$table->unsignedBigInteger('id_sede')->nullable(); // Clave foránea
			$table->string('state', 50)->nullable();
			$table->timestamps(); // Crea 'created_at' y 'updated_at'

			// Definir las claves foráneas
			$table->foreign('id_cita')->references('id_cita')->on('tb_cita')->onDelete('set null');
			$table->foreign('id_sede')->references('id_sede')->on('tb_sede')->onDelete('set null');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('tb_alert');
	}
};
