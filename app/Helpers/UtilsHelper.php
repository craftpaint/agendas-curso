<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\WhatsappService;

class UtilsHelper {

    protected $sendPulseWhatsapp;

    public function __construct(WhatsappService $sendPulseWhatsapp) {
        $this->sendPulseWhatsapp = $sendPulseWhatsapp;
    }

    public static function enviarWhatsappCliente($numero_telefono) {
        $datosUsuario = $this->sendPulseWhatsapp->searchContactByPhone($numero_telefono);

        if($datosUsuario){
            // METODO SI EL USUARIO YA EXISTE
        } else {
            // METODO SI EL USUARIO NO EXISTE
        }
    }
}