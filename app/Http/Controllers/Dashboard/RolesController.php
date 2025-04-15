<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\AdminHelper;
use App\Models\User;
use Carbon\Carbon;

class RolesController extends Controller
{
    // Mostrar la lista de roles
    public function index()
    {
        $user = Auth::user();
        $roles = Role::all();
        $permissions = Permission::all();


        $data = [
            'page' => 'Configuracion',
            'subpage' => 'Roles',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.roles.index', $data);
        echo view('layouts.footer', $data);
    }

    // Mostrar el formulario para crear un nuevo rol
    public function create()
    {
        $user = Auth::user();
        $permissions = Permission::all();

        $data = [
            'page' => 'Configuracion',
            'subpage' => 'Roles',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user,
            'permissions' => $permissions
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.roles.create', $data);
        echo view('layouts.footer', $data);
    }

    // Guardar el rol creado y asignar sus permisos
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'icon' => 'nullable|string'
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web', // o el guard que estés usando
            'icon' => $request->icon,
        ]);

        if ($request->has('permissions')) {
            $permissionModels = Permission::whereIn('id', $request->permissions ?? [])->get();
            $role->syncPermissions($permissionModels);
        }

        return redirect()->route('roles.index')->with('success', 'Rol creado correctamente.');
    }

    // Mostrar el formulario para editar un rol existente
    public function edit(Role $role)
    {
        // Obtenemos los permisos actuales asociados al rol (en forma de array de IDs)

        $user = Auth::user();
        $permissions = Permission::all();

        $rolePermissions = $role->permissions->pluck('id')->toArray();
        $data = [
            'page' => 'Configuracion',
            'subpage' => 'Roles',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user,
            'permissions' => $permissions,
            'role' => $role,
            'rolePermissions' => $rolePermissions
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.roles.edit', $data);
        echo view('layouts.footer', $data);
    }

    // Actualizar los datos del rol y sincronizar permisos
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'icon' => 'nullable|string'
        ]);

        $role->update([
            'name' => $request->name,
            'icon' => $request->icon,
        ]);

        $permissionModels = Permission::whereIn('id', $request->permissions ?? [])->get();
        $role->syncPermissions($permissionModels);

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }


    // Eliminar un rol
    public function destroy(Role $role)
    {
        // Puedes agregar validación para evitar la eliminación de roles críticos
        $role->delete();
        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }
}
