<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EstadisticasHelper {
    public static function obtenerDatosProgressBarDashboardEmpresa($id_empresa = null) {
        $user = Auth::user();

        if ($id_empresa) {
            $empresaUser = DB::table('tb_empresa')->where('id_empresa', $id_empresa)->first();
        } else {
            $sedeUser = DB::table('tb_sede')->where('id_sede', $user->id_sede)->first();
            $empresaUser = DB::table('tb_empresa')->where('id_empresa', $sedeUser->id_empresa)->first();
        }

        //Se busca si existe un paquete activo relacionado a la empresa
        $empresaPaqueteActivo = DB::table('tb_empresa_paquete')
            ->select(
                'tb_empresa_paquete.*',
                'tb_paquete.*'
                )
            ->join('tb_paquete', 'tb_empresa_paquete.id_paquete', '=', 'tb_paquete.id_paquete')
            ->where('tb_empresa_paquete.id_empresa', $empresaUser->id_empresa)
            ->where('tb_empresa_paquete.estado', 'ACTIVO')
            ->first();
        
        if ($empresaPaqueteActivo) {
            $empresaPaqueteActivo->citas_faltantes = $empresaPaqueteActivo->numero_citas - $empresaPaqueteActivo->citas_consumidas;

            // Se consulta el estado Asistió
            $estadoAsistio = DB::table('tb_estado')
            ->select('tb_estado.*')
            ->where('tb_estado.nombre_estado', 'Asistió')
            ->first();

            // Se realiza el calculo de las citas según el paquete activo
            // Se obtienen las citas con estado de liquidador confirmado
            $empresaPaqueteActivo->citasConfirmadas = DB::table('tb_cita')
                ->join('tb_liquidador', 'tb_cita.id_cita', '=', 'tb_liquidador.id_cita')
                ->where('tb_cita.id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_cita.id_estado_verificado', $estadoAsistio->id_estado)
                ->where('tb_liquidador.estado_liquidador', 'Confirmado')
                ->count();

            // Se obtienen las citas con estado de liquidador errado
            $empresaPaqueteActivo->citasErradas = DB::table('tb_cita')
                ->join('tb_liquidador', 'tb_cita.id_cita', '=', 'tb_liquidador.id_cita')
                ->where('tb_cita.id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_cita.id_estado_verificado', $estadoAsistio->id_estado)
                ->where('tb_liquidador.estado_liquidador', 'Errado')
                ->count();
            
            // Se obtienen las citas con estado de liquidador pendiente
            $empresaPaqueteActivo->citasPendientes = DB::table('tb_cita')
                ->join('tb_liquidador', 'tb_cita.id_cita', '=', 'tb_liquidador.id_cita')
                ->where('tb_cita.id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_cita.id_estado_verificado', $estadoAsistio->id_estado)
                ->where('tb_liquidador.estado_liquidador', 'Pendiente')
                ->count();

            // Se obtienen las citas con estado de liquidador En validación
            $empresaPaqueteActivo->citasEnValidacion = DB::table('tb_cita')
                ->join('tb_liquidador', 'tb_cita.id_cita', '=', 'tb_liquidador.id_cita')
                ->where('tb_cita.id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_cita.id_estado_verificado', $estadoAsistio->id_estado)
                ->where('tb_liquidador.estado_liquidador', 'En validación')
                ->count();
        } else {
            return false;
        }
        return $empresaPaqueteActivo;
    }

    public static function obtenerDatosLineBarMixedDashboardEmpresa($id_empresa = null) {
        $user = Auth::user();

        if ($id_empresa) {
            $empresaUser = DB::table('tb_empresa')->where('id_empresa', $id_empresa)->first();
        } else {
            $sedeUser = DB::table('tb_sede')->where('id_sede', $user->id_sede)->first();
            $empresaUser = DB::table('tb_empresa')->where('id_empresa', $sedeUser->id_empresa)->first();
        }

        $empresaPaqueteActivo = DB::table('tb_empresa_paquete')
            ->select(
                'tb_empresa_paquete.*',
                'tb_paquete.*'
                )
            ->join('tb_paquete', 'tb_empresa_paquete.id_paquete', '=', 'tb_paquete.id_paquete')
            ->where('tb_empresa_paquete.id_empresa', $empresaUser->id_empresa)
            ->where('tb_empresa_paquete.estado', 'ACTIVO')
            ->first();
        
        if ($empresaPaqueteActivo) {
            // Se obtienen las citas existentes por mes actual para el paquete activo.
            $citasPorDiaAgendadas = DB::table('tb_cita')
                ->select(DB::raw('DAY(reserva_cita) as dia, COUNT(*) as total_citas'))
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->whereBetween('reserva_cita', [
                    now()->startOfMonth()->format('Y-m-d H:i:s'),
                    now()->endOfMonth()->format('Y-m-d H:i:s')
                ])
                ->groupBy(DB::raw('DAY(reserva_cita)'))
                ->orderBy('dia')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->dia => $item->total_citas];
                });

            // Completa los días faltantes
            $diasMes = range(1, now()->daysInMonth);
            $citasCompletasAgendadas = array_fill_keys($diasMes, 0);
            
            foreach ($citasPorDiaAgendadas as $dia => $total) {
                $citasCompletasAgendadas[$dia] = $total;
            }

            // Se guarda el valor resultante
            $empresaPaqueteActivo->citasAgendadasPorMesActual = array_values($citasCompletasAgendadas);

            // Se obtienen las citas asistidas por mes actual para el paquete activo.
            $citasPorDiaAsistidas = DB::table('tb_cita')
                ->select(DB::raw('DAY(reserva_cita) as dia, COUNT(*) as total_citas'))
                ->join('tb_estado', 'tb_cita.id_estado_verificado', '=', 'tb_estado.id_estado')
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_estado.nombre_estado', 'Asistió')
                ->whereBetween('reserva_cita', [
                    now()->startOfMonth()->format('Y-m-d H:i:s'),
                    now()->endOfMonth()->format('Y-m-d H:i:s')
                ])
                ->groupBy(DB::raw('DAY(reserva_cita)'))
                ->orderBy('dia')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->dia => $item->total_citas];
                });
            
            // Completa los días faltantes
            $diasMes = range(1, now()->daysInMonth);
            $citasCompletasAsistidas = array_fill_keys($diasMes, 0);
            
            foreach ($citasPorDiaAsistidas as $dia => $total) {
                $citasCompletasAsistidas[$dia] = $total;
            }

            // Se guarda el valor resultante
            $empresaPaqueteActivo->citasAsistidasPorMesActual = array_values($citasCompletasAsistidas);
        } else {
            return false;
        }
        return $empresaPaqueteActivo;
    }

    public static function obtenerDatosHistorialPaquetes($id_empresa = null) {
        $user = Auth::user();

        if ($id_empresa) {
            $empresaUser = DB::table('tb_empresa')->where('id_empresa', $id_empresa)->first();
        } else {
            $sedeUser = DB::table('tb_sede')->where('id_sede', $user->id_sede)->first();
            $empresaUser = DB::table('tb_empresa')->where('id_empresa', $sedeUser->id_empresa)->first();
        }

        $empresaPaquetes = DB::table('tb_empresa_paquete')
            ->select(
                'tb_empresa_paquete.*',          
                'tb_paquete.*'
            )
            ->join('tb_paquete', 'tb_empresa_paquete.id_paquete', '=', 'tb_paquete.id_paquete')
            ->where('tb_empresa_paquete.id_empresa', $empresaUser->id_empresa)
            ->whereNot('tb_empresa_paquete.estado', 'PENDIENTE PAGO')
            ->whereNot('tb_paquete.tipo_paquete', 'AUXILIAR')
            ->orderBy('fecha_inicio', 'desc')
            ->get();
        
        if ($empresaPaquetes) {
            foreach ($empresaPaquetes as $empresaPaquete) {
                $empresaPaquete->porcentaje = ($empresaPaquete->citas_consumidas / $empresaPaquete->numero_citas) * 100;
            }
            return $empresaPaquetes;
        } else {
            return null;
        }
    }
}