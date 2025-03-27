<?php

namespace App\Http\Controllers\Cron;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlertController extends Controller
{
    public function index()
    {
        //Obtenemos de la tb_citas los registros que tengan el alert en 0
        $sql = "SELECT * FROM tb_cita as t1
        INNER JOIN tb_sede_horario as t2 ON t1.id_sede_horario = t2.id_sede_horario
        INNER JOIN tb_sede as t3 ON t2.id_sede = t3.id_sede
        WHERE t1.alert LIKE '0'";
        $data = DB::select($sql);
        //Recorremos los registros obtenidos y los actualizamos
        foreach ($data as $row) {
            $id_cita = $row->id_cita;
            $state = 'show';
            $id_sede = $row->id_sede;
            //Registramos el alert en la tabla tb_alert
            $sql = "INSERT INTO tb_alert (id_cita, state, id_sede) VALUES ('$id_cita', '$state', '$id_sede')";
            $save = DB::insert($sql);
            if ($save) {
                //Actualizamos el campo alert en la tabla tb_cita
                $sql = "UPDATE tb_cita SET alert = '1' WHERE id_cita = '$id_cita'";
                $update = DB::update($sql);
                if ($update) {
                    Log::info('Alerta registrada y actualizada correctamente');
                } else {
                    Log::error('Error al actualizar el campo alert en la tabla tb_cita');
                }
            } else {
                Log::error('Error al registrar la alerta en la tabla tb_alert');
            }
        }
    }
}
