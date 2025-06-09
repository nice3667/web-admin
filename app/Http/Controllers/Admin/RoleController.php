<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use BalajiDharma\LaravelAdminCore\Actions\Role\RoleCreateAction;
use BalajiDharma\LaravelAdminCore\Actions\Role\RoleUpdateAction;
use BalajiDharma\LaravelAdminCore\Data\Role\RoleCreateData;
use BalajiDharma\LaravelAdminCore\Data\Role\RoleUpdateData;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $this->authorize('adminViewAny', Role::class);
        $roles = (new Role)->newQuery();

        if (request()->has('search')) {
            $roles->where('name', 'Like', '%'.request()->input('search').'%');
        }

        if (request()->query('sort')) {
            $attribute = request()->query('sort');
            $sort_order = 'ASC';
            if (strncmp($attribute, '-', 1) === 0) {
                $sort_order = 'DESC';
                $attribute = substr($attribute, 1);
            }
            $roles->orderBy($attribute, $sort_order);
        } else {
            $roles->latest();
        }

        $roles = $roles->paginate(config('admin.paginate.per_page'))
                    ->onEachSide(config('admin.paginate.each_side'))
                    ->appends(request()->query());

        return Inertia::render('Admin/Role/Index', [
            'roles' => $roles,
            'filters' => request()->all('search'),
            'can' => [
                'create' => Auth::user()->can('role create'),
                'edit' => Auth::user()->can('role edit'),
                'delete' => Auth::user()->can('role delete'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $this->authorize('adminCreate', Role::class);
        $permissions = Permission::all()->pluck('name', 'id');

        return Inertia::render('Admin/Role/Create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(RoleCreateData $data, RoleCreateAction $roleCreateAction)
    {
        $this->authorize('adminCreate', Role::class);
        $roleCreateAction->handle($data);

        return redirect()->route('admin.role.index')
            ->with('message', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @return \Inertia\Response
     */
    public function show(Role $role)
    {
        $this->authorize('adminView', $role);
        $permissions = Permission::all()->pluck('name', 'name');
        $roleHasPermissions = array_column(json_decode($role->permissions, true), 'name');

        return Inertia::render('Admin/Role/Show', [
            'role' => $role,
            'permissions' => $permissions,
            'roleHasPermissions' => $roleHasPermissions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Inertia\Response
     */
    public function edit(Role $role)
    {
        $this->authorize('adminUpdate', $role);
        $permissions = Permission::all()->pluck('name', 'name');
        $roleHasPermissions = array_column(json_decode($role->permissions, true), 'name');

        return Inertia::render('Admin/Role/Edit', [
            'role' => $role,
            'permissions' => $permissions,
            'roleHasPermissions' => $roleHasPermissions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(RoleUpdateData $data, Role $role, RoleUpdateAction $roleUpdateAction)
    {
        $this->authorize('adminUpdate', $role);
        $roleUpdateAction->handle($data, $role);

        return redirect()->route('admin.role.index')
            ->with('message', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Role $role)
    {
        $this->authorize('adminDelete', $role);
        $role->delete();

        return redirect()->route('admin.role.index')
            ->with('message', __('Role deleted successfully'));
    }
}
