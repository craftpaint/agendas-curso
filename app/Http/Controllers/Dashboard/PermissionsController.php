<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\AdminHelper;
use App\Models\User;
use Carbon\Carbon;

class PermissionsController extends Controller
{
    /**
     * Muestra el listado de permisos.
     */
    public function index()
    {

        $user = Auth::user();
        $roles = Role::all();
        $permissions = Permission::orderBy('name')->get();

        $data = [
            'page' => 'Configuracion',
            'subpage' => 'Permisos',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.permissions.index', $data);
        echo view('layouts.footer', $data);
    }

    /**
     * Muestra el formulario para crear un permiso.
     */
    public function create()
    {
        return view('dashboard.permissions.create');
    }

    /**
     * Almacena el nuevo permiso.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name'
        ]);

        Permission::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        return redirect()->route('permissions.index')->with('success', 'Permiso creado correctamente.');
    }

    /**
     * Muestra el formulario para editar un permiso.
     */
    public function edit(Permission $permission)
    {
        return view('dashboard.permissions.edit', compact('permission'));
    }

    /**
     * Actualiza el permiso.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update([
            'name' => $request->name
        ]);

        return redirect()->route('permissions.index')->with('success', 'Permiso actualizado correctamente.');
    }

    /**
     * Elimina el permiso.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('permissions.index')->with('success', 'Permiso eliminado correctamente.');
    }
}
