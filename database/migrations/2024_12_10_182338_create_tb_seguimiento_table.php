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
		Schema::create('tb_seguimiento', function (Blueprint $table) {
			$table->id('id_seguimiento'); // Clave primaria
			$table->string('titulo_seguimiento', 45)->nullable(); // Título del seguimiento
			$table->longText('nota_seguimiento')->nullable(); // Nota del seguimiento
			$table->unsignedBigInteger('id_cita'); // Clave foránea a tb_cita
			$table->string('id_user', 45); // Usuario relacionado
			$table->timestamps();

			// Relaciones
			$table->foreign('id_cita')->references('id_cita')->on('tb_cita')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('tb_seguimiento');
	}
};
