<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CiudadService
{
    public static function obtenerCiudadPorSede(int $idSede)
    {
        return DB::table('tb_ciudad as c')
            ->join('tb_sede as s', 's.id_ciudad', '=', 'c.id_ciudad')
            ->where('s.id_sede', $idSede)
            ->where('c.estado', 'ACTIVO')
            ->select(
                'c.id_ciudad',
                'c.nombre as nombre_ciudad'
            )
            ->first();
    }
}
