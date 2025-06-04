<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Helpers\AdminHelper;


class EmpresasController extends Controller
{
    /**
     * Muestra el listado de empresas.
     */
    public function index()
    {
        // Usamos Query Builder para obtener las empresas
        // Asumiendo que la tabla se llama "empresas"
        $empresas = DB::table('tb_empresa')->orderBy('id_empresa', 'asc')->get();

        // Por cada empresa, obtener sedes asociadas y calcular la suma de usuarios de dichas sedes.
        foreach ($empresas as $empresa) {
            // Asume que en la tabla 'sedes' existe la columna 'empresa_id' que relaciona la sede con la empresa.
            $sedes = DB::table('tb_sede')->where('id_empresa', $empresa->id_empresa)->get();
            $empresa->sedes_count = $sedes->count();

            $usuarios_count = 0;
            // Se asume que en la tabla 'users' existe la columna 'id_sede' para relacionar un usuario con una sede.
            foreach ($sedes as $sede) {
                $usuarios_count += DB::table('users')->where('id_sede', $sede->id_sede)->count();
            }
            $empresa->usuarios_count = $usuarios_count;
        }

        $user = Auth::user();

        $data = [
            'page'      => 'Configuracion',
            'subpage'   => 'Empresa',
            'rol'       => $user->getRoleNames()->first(),
            'user'      => $user,
            'empresas'  => $empresas
        ];

        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.empresas.index', $data);
        echo view('layouts.footer', $data);
    }

    /**
     * Muestra el formulario para crear una nueva empresa.
     */
    public function create()
    {
        $user = Auth::user();
        $data = [
            'page'    => 'Configuracion',
            'subpage' => 'Empresa',
            'rol'     => $user->getRoleNames()->first(),
            'user'    => $user,
        ];

        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.empresas.create', $data);
        echo view('layouts.footer', $data);
    }

    /**
     * Guarda la nueva empresa en la base de datos.
     */
    public function store(Request $request)
    {
        // Validar datos
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo'        => 'nullable|string'  // Se espera la URL o path del logo
        ]);

        try {
            DB::table('tb_empresa')->insert([
                'Nombre'    => $request->input('name'),
                'Descripcion'       => $request->input('description'),
                'logo'              => $request->input('logo'),
                'created_at'        => now(),
                'updated_at'        => now()
            ]);
            return redirect()->route('empresas.index')->with('success', 'Empresa creada correctamente');
        } catch (\Exception $e) {
            Log::error('Error al crear empresa: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al crear la empresa');
        }
    }

    /**
     * Muestra el formulario para editar una empresa existente.
     */
    public function edit($id)
    {
        $empresa = DB::table('tb_empresa')->where('id_empresa', $id)->first();
        if (!$empresa) {
            return redirect()->back()->withErrors('Empresa no encontrada');
        }

        $user = Auth::user();
        $data = [
            'page'      => 'Configuracion',
            'subpage'   => 'Empresa',
            'rol'       => $user->getRoleNames()->first(),
            'user'      => $user,
            'empresa'   => $empresa
        ];

        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.empresas.edit', $data);
        echo view('layouts.footer', $data);
    }

    /**
     * Actualiza la empresa en la base de datos.
     */
    public function update(Request $request, $id)
    {
        // Validar datos
        $request->validate([
            'ame'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo'        => 'nullable|string'
        ]);

        try {
            DB::table('tb_empresa')->where('id_empresa', $id)->update([
                'Nombre'    => $request->input('name'),
                'Descripcion'       => $request->input('description'),
                'logo'              => $request->input('logo'),
                'updated_at'        => now()
            ]);
            return redirect()->route('empresas.index')->with('success', 'Empresa actualizada correctamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar empresa: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al actualizar la empresa');
        }
    }

    /**
     * Elimina una empresa.
     */
    public function destroy($id)
    {
        try {
            DB::table('tb_empresa')->where('id_empresa', $id)->delete();
            return redirect()->route('empresas.index')->with('success', 'Empresa eliminada correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar empresa: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al eliminar la empresa');
        }
    }

    public function uploadLogo(Request $request)
    {
        try {
            // Validar que se suba un archivo y que sea una imagen
            $request->validate([
                'file' => 'required|image|max:2048' // Máximo 2MB, ajusta según tus necesidades
            ]);

            // Almacena el archivo en una carpeta pública: 'empresas/logos'
            // El método storePublicly() devuelve la ruta relativa en el disco 'public'
            $path = $request->file('file')->store('empresas/logos', 'public_html');

            // Puedes generar la URL del logo utilizando Storage::url($path)
            $filePath = Storage::url($path);

            // Retorna el archivo como respuesta JSON
            return response()->json(['filePath' => $filePath], 200);
        } catch (\Exception $e) {
            Log::error('Error al subir el logo de la empresa: ' . $e->getMessage());
            return response()->json(['error' => 'Error al subir el logo.'], 500);
        }
    }
}
