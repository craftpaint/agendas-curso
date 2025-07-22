<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaqueteHelper {
    // Se retorna el ID del la empresa_paquete para que este sea adjunto a la cita
    public static function obtenerPaqueteActivoEmpresa ($id_sede) {
        $empresa = DB::table('tb_empresa')
            ->select('tb_empresa.*')
            ->join('tb_sede', 'tb_empresa.id_empresa', '=', 'tb_sede.id_empresa')
            ->where('tb_sede.id_sede', $id_sede)
            ->first();
        
        $empresaPaqueteActivo = DB::table('tb_empresa_paquete')
            ->select('tb_empresa_paquete.id_empresa_paquete')
            ->join('tb_empresa', 'tb_empresa_paquete.id_empresa', '=', 'tb_empresa.id_empresa')
            ->join('tb_paquete', 'tb_empresa_paquete.id_paquete', '=', 'tb_paquete.id_paquete')
            ->where('tb_empresa.id_empresa', $empresa->id_empresa)
            ->where('tb_empresa_paquete.estado', 'ACTIVO')
            ->first();

        if ($empresaPaqueteActivo) {
            return $empresaPaqueteActivo->id_empresa_paquete;
        } else {
            return null;
        }
    }

    // Se valida si el paquete es PREPAGO O POSPAGO, para realizar acciones de descuento o no
    public static function validarCambioEstado ($id_cita, $id_estado_nuevo) {
        $cita = DB::table('tb_cita')
            ->select('tb_cita.*')
            ->where('tb_cita.id_cita', $id_cita)
            ->first();

        if ($cita->id_estado_verificado != $id_estado_nuevo) {
            self::validarDescuentoCitaPaqueteEmpresa($id_cita, $id_estado_nuevo, $cita->id_estado_verificado);
        }
    }

    // Se realiza el descuento de cita o aumento según el estado que se selecciona
    private static function validarDescuentoCitaPaqueteEmpresa ($id_cita, $id_estado, $id_estado_actual) {
        // Se consulta la cita
        $cita = DB::table('tb_cita')
            ->select('tb_cita.*')
            ->where('tb_cita.id_cita', $id_cita)
            ->first();
        
        // Se consulta el estado nuevo
        $estado = DB::table('tb_estado')
            ->select('tb_estado.*')
            ->where('tb_estado.id_estado', $id_estado)
            ->first();
        
        // Se consulta el estado actual
        $estadoActual = DB::table('tb_estado')
            ->select('tb_estado.*')
            ->where('tb_estado.id_estado', $id_estado_actual)
            ->first();
        
        if ($cita && $estado) {
            if ($cita->id_empresa_paquete) {
                // Se consulta la empresa paquete
                $empresa_paquete = DB::table('tb_empresa_paquete')
                    ->select('tb_empresa_paquete.*')
                    ->where('id_empresa_paquete', $cita->id_empresa_paquete)
                    ->first();

                if ($empresa_paquete) {
                    $nuevo_valor = $empresa_paquete->citas_consumidas;

                    if ($estado->nombre_estado == "Asistió") {
                        $nuevo_valor += 1;

                    } else if ($estado->nombre_estado != "Asistió" && $estadoActual->nombre_estado == "Asistió" && $nuevo_valor > 0) {
                        self::validarReactivacionPaquete($empresa_paquete->id_empresa_paquete);
                        $nuevo_valor -= 1;
                    }

                    // Se actualiza la empresa paquete con el nuevo valor
                    DB::table('tb_empresa_paquete')
                        ->where('id_empresa_paquete', $empresa_paquete->id_empresa_paquete)
                        ->update([
                            'citas_consumidas' => $nuevo_valor,
                            'updated_at' => Carbon::now()
                        ]);
                    
                    // Se valida el paquete actual de la empresa
                    $paquete_cita = DB::table('tb_paquete')
                        ->select('tb_paquete.*')
                        ->join('tb_empresa_paquete', 'tb_paquete.id_paquete', '=', 'tb_empresa_paquete.id_paquete')
                        ->join('tb_cita', 'tb_empresa_paquete.id_empresa_paquete', '=', 'tb_cita.id_empresa_paquete')
                        ->where('tb_cita.id_cita', $id_cita)
                        ->first();
                    
                    if ($paquete_cita->tipo_paquete == "PREPAGO") {
                        self::validarPaqueteEmpresaPrepago($empresa_paquete->id_empresa_paquete, $id_cita);
                    }
                }
            }
        }
    }

    //Se valida si el paquete debe ser reactivado al momento de colocar en No Asistió en una cita
    private static function validarReactivacionPaquete($id_empresa_paquete) {
        $empresa_paquete = DB::table('tb_empresa_paquete')
            ->select('tb_empresa_paquete.*')
            ->where('id_empresa_paquete', $id_empresa_paquete)
            ->first();
        
        $paquete = DB::table('tb_paquete')
            ->select('tb_paquete.*')
            ->join('tb_empresa_paquete', 'tb_paquete.id_paquete', '=', 'tb_empresa_paquete.id_paquete')
            ->where('tb_empresa_paquete.id_empresa_paquete', $id_empresa_paquete)
            ->first();

        $empresa = DB::table('tb_empresa')
            ->select('tb_empresa.*')
            ->join('tb_empresa_paquete', 'tb_empresa.id_empresa', '=', 'tb_empresa_paquete.id_empresa')
            ->where('tb_empresa_paquete.id_empresa_paquete', $id_empresa_paquete)
            ->first();
        
        if ($empresa_paquete->citas_consumidas >= $paquete->numero_citas && $empresa_paquete->estado == 'CONSUMIDO') {
            // Busca el paquete activo actualmente para realizar la reasignación
            $empresa_paquete_actual = DB::table('tb_empresa_paquete')
                ->select('tb_empresa_paquete.*')
                ->where('tb_empresa_paquete.id_empresa', $empresa->id_empresa)
                ->where('tb_empresa_paquete.estado', 'ACTIVO')
                ->first();
            
            //Busca el paquete activo actualmente y lo pone en PENDIENTE
            DB::table('tb_empresa_paquete')
                ->where('tb_empresa_paquete.id_empresa', $empresa->id_empresa)
                ->where('tb_empresa_paquete.estado', 'ACTIVO')
                ->update([
                    'estado' => 'PENDIENTE',
                    'updated_at' => Carbon::now()
                ]);

            // Vuelve a activar el paquete al que se le hará el descuento
            DB::table('tb_empresa_paquete')
                ->where('id_empresa_paquete', $id_empresa_paquete)
                ->update([
                    'estado' => 'ACTIVO',
                    'fecha_fin' => null,
                    'updated_at' => Carbon::now()
                ]);
            
            // Realiza la reasignación de citas
            self::reasignacionCitasEmpresaPaquete($empresa_paquete_actual->id_empresa_paquete, $id_empresa_paquete);
        }
    }

    // Se valida si el paquete ya fue consumido para paquetes PREPAGO
    private static function validarPaqueteEmpresaPrepago($id_empresa_paquete, $id_cita) {
        $empresa_paquete = DB::table('tb_empresa_paquete')
            ->select('tb_empresa_paquete.*')
            ->where('id_empresa_paquete', $id_empresa_paquete)
            ->first();
        
        $paquete = DB::table('tb_paquete') 
            ->select('tb_paquete.*')
            ->join('tb_empresa_paquete', 'tb_paquete.id_paquete', '=', 'tb_empresa_paquete.id_paquete')
            ->where('tb_empresa_paquete.id_empresa_paquete', $id_empresa_paquete)
            ->first();

        // Si el paquete ya fue consumido se cambia el estado del mismo
        if ($empresa_paquete->citas_consumidas >= $paquete->numero_citas) {
            //Consultamos el estado asistió
            $estadoAsistio = DB::table('tb_estado')
            ->select('tb_estado.*')
            ->where('tb_estado.nombre_estado', 'Asistió')
            ->first();

            //Actualizamos la última cita para que esta se quede en el paquete correcto
            DB::table('tb_cita')
                ->where('id_cita', $id_cita)
                ->update([
                    'id_estado_verificado' => $estadoAsistio->id_estado,
                    'updated_at' => Carbon::now()
                ]);

            // Se cambia el estado del paquete actual a CONSUMIDO
            DB::table('tb_empresa_paquete')
                ->where('id_empresa_paquete', $empresa_paquete->id_empresa_paquete)
                ->update([
                    'estado' => 'CONSUMIDO',
                    'fecha_fin' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            
            // Se consulta si tiene un paquete con estado PENDIENTE
            $empresa_paquete_pendiente = DB::table('tb_empresa_paquete')
                ->select('tb_empresa_paquete.*')
                ->where('estado', 'PENDIENTE')
                ->orderBy('created_at', 'asc')
                ->first();
            
            if ($empresa_paquete_pendiente) {
                DB::table('tb_empresa_paquete')
                    ->where('id_empresa_paquete', $empresa_paquete_pendiente->id_empresa_paquete)
                    ->update([
                        'estado' => 'ACTIVO',
                        'fecha_inicio' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                self::reasignacionCitasEmpresaPaquete($empresa_paquete->id_empresa_paquete, $empresa_paquete_pendiente->id_empresa_paquete);
            } else {
                // En caso de no encontrar un paquete pendiente, inactiva todas las sedes de la empresa
                self::inactivarSedesEmpresa($empresa_paquete->id_empresa);
                self::creacionPaqueteTemporal($empresa_paquete, $id_cita);
            }
        }
    }

    // Se inactivan las sedes de la empresa
    private static function inactivarSedesEmpresa($id_empresa) {
        DB::table('tb_sede')
            ->where('id_empresa', $id_empresa)
            ->update([
                'estado_sede' => 'Inactivo',
                'updated_at' => Carbon::now()
            ]);
    }

    // Se crea el paquete temporal cuando no se encuentran más paquetes pedientes por su activación
    private static function creacionPaqueteTemporal($empresa_paquete, $id_cita) {
        // Se consultan los datos necesarios 
        $estadoAsistio = DB::table('tb_estado')
            ->select('tb_estado.*')
            ->where('tb_estado.nombre_estado', 'Asistió')
            ->first();
        
        $paqueteTemporal = DB::table('tb_paquete')
            ->select('tb_paquete.*')
            ->where('tb_paquete.tipo_paquete', 'AUXILIAR')
            ->first();

        //Actualizamos la última cita para que esta se quede en el paquete correcto
        DB::table('tb_cita')
            ->where('id_cita', $id_cita)
            ->update([
                'id_estado_verificado' => $estadoAsistio->id_estado,
                'updated_at' => Carbon::now()
            ]);
        
        $empresaPaqueteTemporalExistente = DB::table('tb_empresa_paquete')
            ->where('id_empresa', $empresa_paquete->id_empresa)
            ->where('id_paquete', $paqueteTemporal->id_paquete)
            ->first();
        
        $idEmpresaPaqueteTemporal = null;

        if(!$empresaPaqueteTemporalExistente) {
            // Se realiza la inserción del paquete empresa para asignar el nuevo paquete Auxiliar
            $data = [
                'id_empresa' => $empresa_paquete->id_empresa,
                'id_paquete' => $paqueteTemporal->id_paquete,
                'fecha_inicio' => Carbon::now(),
                'estado' => 'ACTIVO'
            ];

            $idEmpresaPaqueteTemporal = DB::table('tb_empresa_paquete')->insertGetId($data);
        } else {
           $idEmpresaPaqueteTemporal = $empresaPaqueteTemporalExistente->id_empresa_paquete;
        }
            
        self::reasignacionCitasEmpresaPaquete($empresa_paquete->id_empresa_paquete, $idEmpresaPaqueteTemporal);
    }

    // Se realiza la reasignación de las citas, según el paquete entrante y el paquete cambiante
    private static function reasignacionCitasEmpresaPaquete($id_empresa_paquete, $id_empresa_paquete_cambiar) {
        // Se consultan los datos necesarios 
        $estadoAsistio = DB::table('tb_estado')
            ->select('tb_estado.*')
            ->where('tb_estado.nombre_estado', 'Asistió')
            ->first();

        $estadoNoAsistio = DB::table('tb_estado')
            ->select('tb_estado.*')
            ->where('tb_estado.nombre_estado', 'No Asistió')
            ->first();

        // Actualizamos las citas pendientes
        DB::table('tb_cita')
            ->where('tb_cita.id_empresa_paquete', $id_empresa_paquete)
            ->whereNot('tb_cita.id_estado_verificado', $estadoAsistio->id_estado)
            ->whereNot('tb_cita.id_estado_verificado', $estadoNoAsistio->id_estado)
            ->update([
                'tb_cita.id_empresa_paquete' => $id_empresa_paquete_cambiar
            ]);
    }
}