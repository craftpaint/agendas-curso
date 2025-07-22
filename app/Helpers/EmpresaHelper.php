<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

    public static function registrarEmpresaPaquete ($data_empresa, $paquete_seleccionado, $referencia_pago) {
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

        // Se rellenan algunos datos de PAGO_EMPRESA
        $data_pago_empresa = [
            'id_empresa_paquete' => $id_empresa_paquete,
            'monto_pago' => $paquete->valor,
            'referencia_pago' => $referencia_pago,
            'estado_pago_wompi' => 'PENDIENTE',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
    }
}