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
		Schema::create('tb_servicio', function (Blueprint $table) {
			$table->id('id_servicio'); // Clave primaria
			$table->string('tipo_servicio', 45)->nullable(); // Tipo de servicio
			$table->longText('desc_servicio')->nullable(); // Descripción del servicio
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('tb_servicio');
	}
};
