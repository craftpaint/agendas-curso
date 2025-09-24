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
        $roles = DB::table('roles')->orderBy('id', 'desc')->get();
        $data['roles'] = $roles;
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $sql = "SELECT * FROM tb_sede";
        $data['sedes'] = DB::select($sql);;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
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

                $user = Auth::user();

                $length = $request->request->get('length');
                $start = $request->request->get('start');
                $draw = $request->request->get('draw');
                $search = $request->input('search.value', '');

                $query = DB::table('users')
                    ->leftJoin('tb_sede', 'users.id_sede', '=', 'tb_sede.id_sede')
                    ->select('users.*', 'tb_sede.nombre_sede', 'tb_sede.tel_sede', 'tb_sede.id_empresa');

                // Agregar filtro de búsqueda si existe
                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('users.email', 'like', '%' . $search . '%')
                            ->orWhere('users.name', 'like', '%' . $search . '%')
                            ->orWhere('tb_sede.nombre_sede', 'like', '%' . $search . '%');
                    });
                }

                // Filtro por permiso de empresa aliada
                if ($user->can('global.Pertenece a empresa aliada.v')) {
                    $sedeUsuario = DB::table('tb_sede')->where('id_sede', $user->id_sede)->first();

                    if ($sedeUsuario && isset($sedeUsuario->id_empresa)) {
                        $query->where('tb_sede.id_empresa', $sedeUsuario->id_empresa);
                    } else {
                        // Si no tiene sede o empresa, no debe ver ningún dato
                        $query->whereRaw('1 = 0');
                    }
                }

                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('users.email', 'like', '%' . $search . '%')
                            ->orWhere('users.name', 'like', '%' . $search . '%')
                            ->orWhere('tb_sede.nombre_sede', 'like', '%' . $search . '%');
                    });
                }


                // Obtener el total filtrado antes del paginado
                $totalFiltered = $query->count();

                // Aplicar paginación
                $users = $query
                    ->offset($start)
                    ->limit($length)
                    ->get();

                foreach ($users as $user) {
                    $userModel = User::find($user->id);
                    $user->role = optional($userModel->getRoleNames())->first();
                }


                //Retornamos la respuesta
                $objLoad = [
                    'draw' => $draw,
                    'recordsTotal' => $totalFiltered, // O puedes usar User::count() si quieres el total sin filtro
                    'recordsFiltered' => $totalFiltered,
                    'data' => $users,
                    'validate' => true
                ];
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
                $id_user_sendpulse = $request->request->get('id_user_sendpulse');
                $id_chatbot_sendpulse = $request->request->get('id_chatbot_sendpulse');
                $id_plantilla_sendpulse = $request->request->get('id_plantilla_sendpulse');
                //Guardamos al usuario
                $user = new User();
                $user->id_sede = $id_sede;
                $user->name = $name;
                $user->email = $email;
                $user->password = bcrypt($password);
                $user->callcenter_habilitado = $callcenter_habilitado;
                $user->id_user_sendpulse = $id_user_sendpulse;
                $user->id_chatbot_sendpulse = $id_chatbot_sendpulse;
                $user->id_plantilla_sendpulse = $id_plantilla_sendpulse;
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
                $id_user_sendpulse = $request->request->get('id_user_sendpulse');
                $id_chatbot_sendpulse = $request->request->get('id_chatbot_sendpulse');
                $id_plantilla_sendpulse = $request->request->get('id_plantilla_sendpulse');
                $user = User::find($id_user);
                $user->id_sede = $id_sede;
                $user->name = $name;
                $user->email = $email;
                if ($password) {
                    $user->password = bcrypt($password);
                }
                $user->id_user_sendpulse = $id_user_sendpulse;
                $user->id_chatbot_sendpulse = $id_chatbot_sendpulse;
                $user->id_plantilla_sendpulse = $id_plantilla_sendpulse;
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
                //Se debe corregir para usar soft delete
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
