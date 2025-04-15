<?php

namespace App\Helpers;
//Modelos
use App\Models\SedesModel;
use App\Models\SedesHorariosModel;
use App\Models\ServiciosModel;
use App\Models\HorariosModel;
use App\Models\FestivosModel;
use App\Models\ClientesModel;
use App\Models\VehiculoModel;
use App\Models\CitasModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminHelper
{
    public static function get_cliente_by_doc($documento)
    {
        try {
            $checkIntent = optional(ClientesModel::where('doc_cliente', $documento)->first())->toArray();
            if (is_array($checkIntent) && !empty($checkIntent)) {
                return $checkIntent;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('get_cliente_by_doc error: ' . $e->getMessage());
            return false;
        }
    }
    public static function get_sede_by_id($id)
    {
        try {
            $checkIntent = optional(SedesModel::where('id_sede', $id)->first())->toArray();
            if (is_array($checkIntent) && !empty($checkIntent)) {
                return $checkIntent;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('get_sede_by_id error: ' . $e->getMessage());
            return false;
        }
    }
    public static function get_sede_horarios($id)
    {
        try {
            $checkIntent = optional(SedesHorariosModel::where('id_sede', $id)->get())->toArray();
            if (is_array($checkIntent) && !empty($checkIntent)) {
                return $checkIntent;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('get_sede_horarios error: ' . $e->getMessage());
            return false;
        }
    }
    public static function get_servicios()
    {
        try {
            return optional(ServiciosModel::get())->toArray();
        } catch (\Exception $e) {
            Log::error('get_servicios error: ' . $e->getMessage());
            return false;
        }
    }
    public static function get_horarios()
    {
        try {
            return optional(HorariosModel::orderBy('inicio_horario', 'asc')->get())->toArray();
        } catch (\Exception $e) {
            Log::error('get_servicios error: ' . $e->getMessage());
            return false;
        }
    }
    public static function get_festivos()
    {
        try {
            return optional(FestivosModel::get())->toArray();
        } catch (\Exception $e) {
            Log::error('get_servicios error: ' . $e->getMessage());
            return false;
        }
    }
    public static function get_horarios_by_id($id)
    {
        try {
            $checkIntent = optional(HorariosModel::where('id_horario', $id)->first())->toArray();
            if (is_array($checkIntent) && !empty($checkIntent)) {
                return $checkIntent;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('get_servicios error: ' . $e->getMessage());
            return false;
        }
    }
    public static function get_cliente_by_id($id)
    {
        try {
            $checkIntent = optional(ClientesModel::where('id_cliente', $id)->first())->toArray();
            if (is_array($checkIntent) && !empty($checkIntent)) {
                return $checkIntent;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('get_cliente_by_id error: ' . $e->getMessage());
            return false;
        }
    }

    public static function get_vehiculo_by_id($id)
    {
        try {
            $vehiculo = VehiculoModel::where('id_vehiculo', $id)->first();
            return $vehiculo ? $vehiculo->toArray() : null;
        } catch (\Exception $e) {
            Log::error('get_vehiculo_by_id error: ' . $e->getMessage());
            return null;
        }
    }

    public static function get_vehiculo_by_placa($placa)
    {
        try {
            $checkIntent = optional(VehiculoModel::where('placa_vehiculo', $placa)->get())->toArray();
            if (is_array($checkIntent) && !empty($checkIntent)) {
                return $checkIntent;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('get_vehiculo_by_id error: ' . $e->getMessage());
            return false;
        }
    }

    public static function get_vehiculo_by_id_cliente($id)
    {
        try {
            $checkIntent = optional(VehiculoModel::where('id_cliente', $id)->get())->toArray();
            if (is_array($checkIntent) && !empty($checkIntent)) {
                return $checkIntent;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('get_vehiculo_by_id error: ' . $e->getMessage());
            return false;
        }
    }

    public static function get_horario_by_id($id)
    {
        try {
            $checkIntent = optional(SedesHorariosModel::where('id_sede_horario', $id)->first())->toArray();
            if (is_array($checkIntent) && !empty($checkIntent)) {
                return $checkIntent;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('get_horario_by_id error: ' . $e->getMessage());
            return false;
        }
    }

    public static function get_cita_by_id($id)
    {
        try {
            $sql = "SELECT *, t1.created_at as fecha_create, t1.updated_at as fecha_update FROM tb_cita as t1
            INNER JOIN tb_cliente as t2 ON t1.id_cliente = t2.id_cliente
            INNER JOIN tb_estado as t3 ON t1.id_estado = t3.id_estado
            INNER JOIN tb_sede as t5 ON t1.id_sede = t5.id_sede WHERE t1.id_cita = $id";
            $data = DB::select($sql);
            return $data;
        } catch (\Exception $e) {
            Log::error('get_cita_by_id error: ' . $e->getMessage());
            return false;
        }
    }

    public static function get_count_alert($rol, $id_sede)
    {
        try {
            $user = Auth::user();
            //Verificamos si existe en cache el conteo de alertas
            $cache = cache()->get('count_alert');
            if ($cache) {
                return $cache;
            }
            if ($user->can('global.Solo ver sede asignada.v')) {
                $sql = "SELECT id FROM tb_alert WHERE state LIKE 'show' AND id_sede = $id_sede";
            } else {
                $sql = "SELECT id FROM tb_alert WHERE state LIKE 'show'";
            }
            $data = DB::select($sql);
            //Guardamos en cache el conteo de alertas
            cache()->put('count_alert', count($data), 60);
            return count($data);
        } catch (\Exception $e) {
            Log::error('get_alert error: ' . $e->getMessage());
            return false;
        }
    }

    public static function change_status_alert($rol, $id_sede)
    {
        try {
            $user = Auth::user();
            if ($user->can('global.Solo ver sede asignada.v')) {
                $sql = "UPDATE tb_alert SET state = 'hide' WHERE state LIKE 'show' AND id_sede = $id_sede";
            } else {
                $sql = "UPDATE tb_alert SET state = 'hide' WHERE state LIKE 'show'";
            }
            $data = DB::select($sql);
            return count($data);
        } catch (\Exception $e) {
            Log::error('get_alert error: ' . $e->getMessage());
            return false;
        }
    }
}
