<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoadTest extends TestCase {
    use WithFaker;

    public function test_save_cita(): void {
        dump("\n INICIO DE PRUEBA DE SAVE_CITA");

        // USUARIOS NUEVOS
        $body = [
            'id_sede' => 34,
            'reserva_cita' => $this->faker->dateTimeBetween('now', '+7 days')->format('d/m/Y'),
            'id_sede_horario' => 38370,
            'nombre_cliente' => $this->faker->name,
            'apellido_cliente' => $this->faker->lastName . ' TEST AUTOMATIZADO',
            'email_cliente' => 'jrubio@zocodigital.com',
            'telefono_cliente' => '+57' . $this->faker->numerify('3########'),
            'tipo_doc_cliente' => 'CC',
            'doc_cliente' => $this->faker->numerify('###########'),
            'servicio_liquidador' => 6,
            'codigo_comparendo' => '[{"value":"A01"}]',
            'tipo_vehiculo' => 'Motocicleta',
            'placa_vehiculo' => $this->faker->regexify('[A-Z]{3}-[0-9]{3}'),
        ];

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson('/savecita', $body);
        $response->assertStatus(200)->assertJsonMissing(['validate' => false]);
        $response->dump();
        dump("\n PRUEBA DE USUARIOS NUEVOS FINALIZADA");

        // USUARIOS NUEVOS - DUPLICADOS
        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson('/savecita', $body);
        $response->assertStatus(200);
        $response->dump();
        dump("\n PRUEBA DE USUARIOS NUEVOS - DUPLICADOS FINALIZADA");
        dump("\n PRUEBA DE SAVE_CITA FINALIZADA");
    }
}
