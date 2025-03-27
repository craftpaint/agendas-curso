<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\AdminHelper;
use App\Models\User;

class UsersController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Usuarios',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $sql = "SELECT * FROM tb_sede";
        $data['sedes'] = DB::select($sql);;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.users.index', $data);
        echo view('layouts.footer', $data);
    }
    //Obtener las actividades de la base de datos
    public function get(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {
                $length = $request->request->get('length');
                $start = $request->request->get('start');
                $draw = $request->request->get('draw');
                //Verificamos si existe un filtro de busqueda
                $search = $_POST['search']['value'];
                $sql = "SELECT * FROM users LEFT JOIN tb_sede ON users.id_sede = tb_sede.id_sede LIMIT " . $start . ", " . $length . "";
                if ($search) {
                    $sql = "SELECT * users LEFT JOIN tb_sede ON users.id_sede = tb_sede.id_sede WHERE email LIKE '%" . $search . "%' LIMIT " . $start . ", " . $length . "";
                }
                //Ejecutamos la query
                $data = DB::select($sql);;
                $total_response = 999999;

                //Recorremos los datos para insertar el rol
                foreach ($data as $key => $value) {
                    $user = User::find($value->id);
                    $data[$key]->role = $user->getRoleNames()[0];
                }


                //Retornamos la respuesta
                $objLoad = array(
                    "draw" => $draw,
                    "recordsTotal" => $total_response,
                    "recordsFiltered" => $total_response,
                    "data" => $data,
                    "validate" => true
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $objLoad['text'] = 'Error al obtener los datos';
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //Obtener las actividades de la base de datos
    public function get_user(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {
                $id = $request->request->get('id');
                $sql = "SELECT * FROM users LEFT JOIN tb_sede ON users.id_sede = tb_sede.id_sede WHERE id = " . $id . "";
                $data = DB::select($sql);
                //Obtenemos el rol del usuario
                $user = User::find($id);
                $data[0]->role = $user->getRoleNames()[0];
                $user = $data[0];
                $objLoad = array(
                    "usuario" => $user,
                    "validate" => true
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $objLoad['text'] = 'Error al obtener los datos';
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }

    //Obtener las actividades de la base de datos
    public function save(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar al usuario'
            ];
            //Ejecución de la funcion
            try {
                $id_sede = $request->request->get('id_sede');
                $name = $request->request->get('name');
                $email = $request->request->get('email');
                $password = $request->request->get('password');
                $role = $request->request->get('role');
                $callcenter_habilitado_estado = $request->request->get('callcenter_habilitado');
                if ($callcenter_habilitado_estado == 'on') {
                    $callcenter_habilitado = 1;
                } else {
                    $callcenter_habilitado = 0;
                }
                //Guardamos al usuario
                $user = new User();
                $user->id_sede = $id_sede;
                $user->name = $name;
                $user->email = $email;
                $user->password = bcrypt($password);
                $user->callcenter_habilitado = $callcenter_habilitado;
                $user->save();
                $user->assignRole($role);
                $objLoad = array(
                    "validate" => true,
                    "text" => 'Usuario guardado correctamente'
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //Obtener las actividades de la base de datos
    public function update(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar la sede'
            ];
            try {
                $id_user = $request->request->get('id_user');
                $id_sede = $request->request->get('id_sede');
                $name = $request->request->get('name');
                $email = $request->request->get('email');
                $password = $request->request->get('password');
                $role = $request->request->get('role');
                $callcenter_habilitado_estado = $request->request->get('callcenter_habilitado');
                if ($callcenter_habilitado_estado == 'on') {
                    $callcenter_habilitado = 1;
                } else {
                    $callcenter_habilitado = 0;
                }
                $user = User::find($id_user);
                $user->id_sede = $id_sede;
                $user->name = $name;
                $user->email = $email;
                if ($password) {
                    $user->password = bcrypt($password);
                }
                $user->callcenter_habilitado = $callcenter_habilitado;
                $user->save();
                $user->syncRoles([$role]);
                $objLoad = array(
                    "validate" => true,
                    "text" => 'Usuario actualizado correctamente'
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Vamos a borrar la sede
    public function delete(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al borrar la sede'
            ];
            //Ejecución de la funcion
            try {
                $id = $request->request->get('id');
                $sql = "DELETE FROM tb_sede_horario WHERE id_sede = " . $id . "";
                DB::delete($sql);
                $sql = "DELETE FROM tb_sede WHERE id_sede = " . $id . "";
                DB::delete($sql);
                $objLoad = array(
                    "validate" => true,
                    "text" => 'Sede borrada correctamente'
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //Cerrar sesion
    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
