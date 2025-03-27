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
		Schema::create('tb_estado', function (Blueprint $table) {
			$table->id('id_estado'); // Clave primaria
			$table->string('nombre_estado', 45)->nullable(); // Nombre del estado
			$table->longText('desc_estado')->nullable(); // Descripción del estado
			$table->string('color_estado', 7)->nullable(); // Campo para el color en formato hexadecimal
			$table->timestamps(); // Crea 'created_at' y 'updated_at'
		});

		Schema::table('tb_estado', function (Blueprint $table) {
			$table->string('color_estado', 7)->nullable()->after('desc_estado'); // Campo para el color en formato hexadecimal
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('tb_estado');
		Schema::table('tb_estado', function (Blueprint $table) {
			$table->dropColumn('color_estado'); // Eliminar el campo si se hace rollback
		});
	}
};
