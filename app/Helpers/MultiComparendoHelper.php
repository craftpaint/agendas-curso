<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MultiComparendoHelper
{
    /**
     * Validar que no exceda el límite de comparendos
     */
    public static function validarLimiteComparendos($comparendos)
    {
        return count($comparendos) <= 3;
    }
    
    /**
     * Formatear fechas de comparendos
     */
    public static function formatearFechasComparendos($comparendos)
    {
        foreach ($comparendos as &$comparendo) {
            // Formatear fecha de cita
            if (!empty($comparendo['reserva_cita'])) {
                $date = \DateTime::createFromFormat('d/m/Y', $comparendo['reserva_cita']);
                if ($date) {
                    $comparendo['reserva_cita'] = $date->format('Y-m-d');
                }
            }
            
            // Formatear fecha de notificación
            if (!empty($comparendo['fecha_notificacion_comparendo'])) {
                $dateNotif = \DateTime::createFromFormat('d/m/Y', $comparendo['fecha_notificacion_comparendo']);
                if ($dateNotif) {
                    $comparendo['fecha_notificacion_comparendo'] = $dateNotif->format('Y-m-d');
                }
            }
        }
        
        return $comparendos;
    }
    
    /**
     * Crear registros de comparendos relacionados
     */
    public static function crearComparendosRelacionados($id_cita, $comparendoData)
    {
        return DB::table('tb_comparendo_cita')->insert([
            'id_cita' => $id_cita,
            'codigo_comparendo' => $comparendoData['codigo_comparendo'] ?? null,
            'fecha_notificacion' => $comparendoData['fecha_notificacion_comparendo'] ?? null,
            'tipo_vehiculo' => $comparendoData['tipo_vehiculo'] ?? null,
            'placa_vehiculo' => $comparendoData['placa_vehiculo'] ?? null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
    
    /**
     * Validar disponibilidad para múltiples citas
     */
    public static function validarDisponibilidadMultiple($id_sede, $fecha, $horario_id)
    {
        $horario_sedes = AdminHelper::get_horario_by_id($horario_id);
        if (empty($horario_sedes)) {
            return false;
        }
        
        $cupo_sede_horario = $horario_sedes['cupo_sede_horario'];
        $id_horario = $horario_sedes['id_horario'];
        $horario = AdminHelper::get_horarios_by_id($id_horario);
        $rango_horario = $horario['rango_horario'];
        
        // Contar citas existentes en ese horario
        $citasCount = DB::table('tb_cita')
            ->where('id_sede', $id_sede)
            ->where('reserva_cita', $fecha)
            ->where('rango_horario', $rango_horario)
            ->count();
        
        return $citasCount < $cupo_sede_horario;
    }
    
    /**
     * Generar resumen de comparendos
     */
    public static function generarResumen($comparendos)
    {
        $resumen = [];
        foreach ($comparendos as $index => $comparendo) {
            $resumen[] = [
                'numero' => $index + 1,
                'fecha' => $comparendo['reserva_cita'] ?? 'No definida',
                'horario' => $comparendo['rango_horario'] ?? 'No definido',
                'vehiculo' => $comparendo['tipo_vehiculo'] ?? 'No especificado',
                'placa' => $comparendo['placa_vehiculo'] ?? 'No especificada',
                'codigo' => $comparendo['codigo_comparendo'] ?? 'No especificado'
            ];
        }
        return $resumen;
    }
}