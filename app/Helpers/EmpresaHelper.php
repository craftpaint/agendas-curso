<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\Helpers\PaqueteHelper;

class EmpresaHelper {
    public static function validarExistenciaEmpresa ($tipo_documento, $documento_empresa) {
        $empresaExistente = DB::table('tb_empresa')
            ->select('tb_empresa.*')
            ->where('tb_empresa.tipo_documento_empresa', $tipo_documento)
            ->where('tb_empresa.documento_empresa', $documento_empresa)
            ->first();
        
        if($empresaExistente) {
            return $empresaExistente;
        } else {
            return null;
        }
    }

    public static function registrarEmpresaPaquete ($data_empresa, $paquete_seleccionado) {
        $empresaExistente = self::validarExistenciaEmpresa($data_empresa['tipo_documento_empresa'], $data_empresa['documento_empresa']);

        // Si la empresa no existe  se crea
        if (!$empresaExistente) {
            DB::table('tb_empresa')->insert($data_empresa);
            $empresaExistente = self::validarExistenciaEmpresa($data_empresa['tipo_documento_empresa'], $data_empresa['documento_empresa']);
        }

        $data_empresa_paquete = [
            'id_empresa' => $empresaExistente->id_empresa,
            'id_paquete' => $paquete_seleccionado,
            'estado' => 'PENDIENTE'
        ];

        // Se crea la relación en EMPRESA_PAQUETE con el paquete seleccionado
        $id_empresa_paquete = DB::table('tb_empresa_paquete')->insertGetId($data_empresa_paquete);
        
        // Consultar paquete que se ha seleccionado para obtener algunos datos
        $paquete = DB::table('tb_paquete')
            ->select('tb_paquete.*')
            ->where('tb_paquete.id_paquete', $paquete_seleccionado)
            ->first();

        // Genera los parámetros de WOMPI
        $wompiParametros = self::generarParametrosWompi($paquete, $empresaExistente, $id_empresa_paquete);

        // Se rellenan algunos datos de PAGO_EMPRESA
        $data_pago_empresa = [
            'id_empresa_paquete' => $id_empresa_paquete,
            'monto_pago' => $wompiParametros['amount-in-cents'],
            'referencia_pago' => $wompiParametros['reference'],
            'estado_pago_wompi' => 'PENDING',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        // Inserta los datos en la tabla PAGO_EMPRESA
        DB::table('tb_pago_empresa')->insert($data_pago_empresa);

        $data = [
            'empresa_paquete' => $id_empresa_paquete,
            'wompi_redirect' => 'https://checkout.wompi.co/p/?' . http_build_query($wompiParametros)
        ];

        return $data;
    }

    private static function generarParametrosWompi($paquete, $empresa, $id_empresa_paquete) {
        $publicKey = env('WOMPI_PUBLIC_KEY');
        $privateKey = env('WOMPI_PRIVATE_KEY');
        $integrityKey = env('WOMPI_INTEGRITY_KEY');
        $random = Str::random(5);

        //Crea la referencia única
        $referencia = 'PAQUETE-' . $paquete->tipo_paquete . '-' . $paquete->id_paquete . '-' . $id_empresa_paquete . '-' . $random;
        
        //Convertir monto a centavos
        $montoCentavos = $paquete->valor * 100;
        $integridad = $referencia . $montoCentavos . 'COP' . $integrityKey;
        $integridadEncriptada = hash('sha256', $integridad);

        // Se encripta el ID del paquete
        $id_paquete_encriptado = Crypt::encryptString($paquete->id_paquete);

        // Se agrega la URL de retorno
        $url_retorno = env('PAGE_GRACIAS_URL') . $id_paquete_encriptado;

        return [
            'public-key' => $publicKey,
            'currency' => 'COP',
            'amount-in-cents' => $montoCentavos,
            'reference' => $referencia,
            'signature:integrity' => $integridadEncriptada,
            'redirect-url' => $url_retorno,
            'customer-data:email' => $empresa->correo_empresa,
            'customer-data:full-name' => $empresa->Nombre,
            'customer-data:phone-number' => $empresa->telefono_empresa,
            'customer-data:phone-number-prefix' => '+57',
            'customer-data:legal-id' => $empresa->documento_empresa,
            'customer-data:legal-id-type' => $empresa->tipo_documento_empresa
        ];
    }

    public static function handleWebhook($header, $body) {
        $json = json_decode($body, true);
        if ($json === null) {
            Log::error('Webhook Wompi: json_decode falló. Error: ' . json_last_error_msg());
            return false;
        }

        // Acceso seguro a los campos
        $checksumHeader = $header;
        $checksumEvent = isset($json['signature']['checksum']) ? $json['signature']['checksum'] : null;
        $properties = isset($json['signature']['properties']) ? $json['signature']['properties'] : [];
        $timestamp = isset($json['timestamp']) ? $json['timestamp'] : null;
        $secret = env('WOMPI_EVENTOS_KEY');
        $sentAt = isset($json['sent_at']) ? $json['sent_at'] : null;

        // Validar que existan los datos mínimos
        if (!$checksumEvent || !$timestamp || !$secret) {
            Log::error('Webhook Wompi: Faltan datos para validar el checksum');
            return false;
        }

        // Concatenar los valores de las propiedades en orden
        $concat = '';
        foreach ($properties as $property) {
            // Las propiedades son tipo 'transaction.id', 'transaction.status', etc.
            $value = data_get($json['data'], $property);
            if ($value === null) {
                $value = data_get($json, $property);
            }
            $concat .= $value;
        }
        $concat .= $timestamp;
        $concat .= $secret;

        $calculatedChecksum = hash('sha256', $concat);

        if (!hash_equals(strtolower($checksumEvent), strtolower($calculatedChecksum))) {
            Log::error('Webhook Wompi: Checksum inválido');
            return false;
        }

        $eventType = $json['event'] ?? null;
        $data = $json['data'] ?? [];

        try {
            if ($eventType === 'transaction.updated' && isset($data['transaction'])) {
                $transaction = $data['transaction'];
                $reference = $transaction['reference'] ?? '';
                $matches = [];
                $id_empresa_paquete = null;

                if (preg_match('/PAQUETE-[A-Z]+-(\\d+)-/', $reference, $matches)) {
                    $id_empresa_paquete = $matches[2];
                } elseif (preg_match('/PAQUETE-(\\d+)-/', $reference, $matches)) {
                    $id_empresa_paquete = $matches[2];
                }

                // Convertir sent_at a formato datetime compatible con MySQL
                $fechaNotificacion = null;

                if ($sentAt) {
                    try {
                        // Wompi envía en UTC, restamos 5 horas para Colombia (-05:00)
                        $fechaNotificacion = Carbon::parse($sentAt)->subHours(5)->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        $fechaNotificacion = null;
                    }
                }

                if ($id_empresa_paquete) {
                    DB::table('tb_pago_empresa')->updateOrInsert(
                        [
                            'id_empresa_paquete' => $id_empresa_paquete,
                            'referencia_pago' => $reference
                        ],
                        [
                            'referencia_pago' => $reference,
                            'id_pago_wompi' => $transaction['id'] ?? null,
                            'monto_pago' => $transaction['amount_in_cents'] ?? null,
                            'correo_pago_wompi' => $transaction['customer_email'] ?? null,
                            'metodo_pago' => $transaction['payment_method_type'] ?? null,
                            'estado_pago_wompi' => $transaction['status'] ?? null,
                            'estado_verificado_pago_wompi' => $transaction['status'] ?? null,
                            'checksum_wompi' => $checksumHeader,
                            'fecha_notificacion_wompi' => $fechaNotificacion,
                            'updated_at' => Carbon::now()
                        ]
                    );

                    // VALIDA SI DEBE ACTIVAR EL PAQUETE DIRECTAMENTE O DEBE ESTAR EN ESPERA Y OTROS PROCESOS MÁS
                    return PaqueteHelper::validacionActivacionPaqueteComprado($id_empresa_paquete);
                } else {
                    Log::error('No se pudo extraer id_cita de la referencia: ' . $reference);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error procesando webhook: ' . $e->getMessage());
        }
        return true;
    }
}