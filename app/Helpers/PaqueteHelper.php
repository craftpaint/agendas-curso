<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaqueteHelper {
    public static function obtenerPaqueteActivoEmpresa ($id_sede) {
        $empresa = DB::table('tb_empresa')
            ->join('tb_sede', 'tb_empresa.id_empresa', '=', 'tb_sede.id_empresa')
            ->where('tb_sede.id_sede', $id_sede)
            ->select('tb_empresa.*')
            ->first();
        
        $empresaPaqueteActivo = DB::table('tb_empresa_paquete')
            ->join('tb_empresa', 'tb_empresa_paquete.id_empresa', '=', 'tb_empresa.id_empresa')
            ->join('tb_paquete', 'tb_empresa_paquete.id_paquete', '=', 'tb_paquete.id_paquete')
            ->where('tb_empresa.id_empresa', $empresa->id_empresa)
            ->where('tb_empresa_paquete.estado', 'ACTIVO')
            ->select('tb_empresa_paquete.id_empresa_paquete')
            ->first();

        if ($empresaPaqueteActivo) {
            return $empresaPaqueteActivo->id_empresa_paquete;
        } else {
            return null;
        }
    }

    public static function validarDescuentoCitaPaqueteEmpresa ($id_cita, $id_estado) {
        $cita = DB::table('tb_cita')
            ->where('tb_cita.id_cita', $id_cita)
            ->select('tb_cita.*')
            ->first();
        
        $estado = DB::table('tb_estado')
            ->where('tb_estado.id_estado', $id_estado)
            ->select('tb_estado.*')
            ->first();
        
        if ($cita && $estado) {
            if ($cita->id_empresa_paquete) {
                $empresa_paquete = DB::table('tb_empresa_paquete')
                    ->where('id_empresa_paquete', $cita->id_empresa_paquete)
                    ->select('tb_empresa_paquete.*')
                    ->first();

                if ($empresa_paquete) {
                    $nuevo_valor = $empresa_paquete->citas_consumidas;

                    if ($estado->nombre_estado == "Asistió") {
                        $nuevo_valor -= 1;
                    } else {
                        $nuevo_valor += 1;
                    }

                    DB::table('tb_empresa_paquete')
                        ->where('id_empresa_paquete', $cita->id_empresa_paquete)
                        ->update(['citas_consumidas' => $nuevoValor]);
                }
            }
        }
    }
}