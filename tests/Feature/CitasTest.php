<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CitasTest extends TestCase {
    use WithFaker;

    public function test_save_cita_dashboard(): void {
        dump("\n INICIO DE PRUEBA DE SAVE_CITA_DASHBOARD");

        // SE USAN LOS PERMISOS NECESARIOS PARA ACCEDER A LA RUTA
        $user = User::where('email', 'jrubio@zocodigital.com')->first();
        $this->actingAs($user);

        // SE CREAN LOS BODY DEL USUARIO Y SE ENVÍA
        $bodyCliente = [
            'nombre_cliente' => $this->faker->name,
            'apellido_cliente' => $this->faker->lastName . ' TEST AUTOMATIZADO',
            'email_cliente' => 'jrubio@zocodigital.com',
            'tipo_doc_cliente' => 'CC',
            'doc_cliente' => $this->faker->numerify('###########'),
            'telefono_cliente' => '+57' . $this->faker->numerify('3########'),
            'desc_cliente' => 'USUARIO PARA TEST AUTOMATIZADO'
        ];

        $responseCliente = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson('/dashboard/clientes/save', $bodyCliente);
        $responseCliente->assertStatus(200)->assertJson(['validate' => true]);
        $responseCliente->dump();
        dump("\n SE INGRESÓ EL CLIENTE NUEVO");

        $responseClienteData = $responseCliente->decodeResponseJson();

        $bodyCita = [
            'id_sede' => 34,
            'reserva_cita' => $this->faker->dateTimeBetween('now', '+7 days')->format('d/m/Y'),
            'id_sede_horario' => 38370,
            'id_estado' => 1,
            'id_estado_verificado' => 1,
            'id_servicio_liquidador' => 1,
            'id_cliente' => $responseClienteData['id'],
            'id_vehiculo' => '',
            'desc_cita' => 'ESTA ES UNA CITA DE PRUEBA AUTOMATIZADA',
            'titulo_seguimiento' => '',
            'nota_seguimiento' => ''
        ];

        $responseCliente = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson('/dashboard/citas/save', $bodyCita);
        $responseCliente->assertStatus(200)->assertJson(['validate' => true]);
        $responseCliente->dump();

        dump("\n PRUEBA DE SAVE_CITA_DASHBOARD FINALIZADA");
    }
}
