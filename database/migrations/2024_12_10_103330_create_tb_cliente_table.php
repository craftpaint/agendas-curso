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
		Schema::create('tb_cliente', function (Blueprint $table) {
			$table->id('id_cliente'); // Clave primaria autoincremental
			$table->string('nombre_cliente', 45)->nullable(); // Nombre del cliente
			$table->string('apellido_cliente', 45)->nullable(); // Apellido del cliente
			$table->string('tipo_doc_cliente', 45)->nullable(); // Tipo de documento
			$table->string('doc_cliente', 45)->nullable(); // Documento de identificación
			$table->string('telefono_cliente', 45)->nullable(); // Teléfono del cliente
			$table->string('email_cliente', 45)->nullable(); // Email del cliente
			$table->longText('desc_cliente')->nullable(); // Descripción del cliente
			$table->timestamps(); // Crea 'created_at' y 'updated_at'
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('tb_cliente');
	}
};
