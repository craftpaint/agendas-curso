<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\AdminHelper;

class PaquetesController extends Controller {
    public function index() {
        $paquetes = DB::table('tb_paquete')->orderBy('id_paquete', 'desc')->get();
        $user = Auth::user();

        $data = [
            'page'      => 'Paquete',
            'subpage'   => 'Listado',
            'rol'       => $user->getRoleNames()->first(),
            'user'      => $user,
            'paquetes'  => $paquetes
        ];

        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.paquetes.index', $data);
        echo view('layouts.footer', $data);
    }
}
