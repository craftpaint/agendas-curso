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
		Schema::create('tb_sede_horario', function (Blueprint $table) {
			$table->id('id_sede_horario'); // Clave primaria
			$table->unsignedBigInteger('id_sede'); // Clave foránea a tb_sede
			$table->unsignedBigInteger('id_horario'); // Clave foránea a tb_horario
			$table->integer('cupo_sede_horario')->nullable(); // Cupo por horario
			$table->string('dia_sede_horario', 45)->nullable(); // Día del horario
			$table->string('estado_sede_horario', 45)->nullable(); // Estado del horario
			$table->timestamps();

			// Relaciones
			$table->foreign('id_sede')->references('id_sede')->on('tb_sede')->onDelete('cascade');
			$table->foreign('id_horario')->references('id_horario')->on('tb_horario')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('tb_sede_horario');
	}
};
