<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use BalajiDharma\LaravelAdminCore\Actions\Permission\PermissionCreateAction;
use BalajiDharma\LaravelAdminCore\Actions\Permission\PermissionUpdateAction;
use BalajiDharma\LaravelAdminCore\Data\Permission\PermissionCreateData;
use BalajiDharma\LaravelAdminCore\Data\Permission\PermissionUpdateData;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $this->authorize('adminViewAny', Permission::class);
        $permissions = (new Permission)->newQuery();

        if (request()->has('search')) {
            $permissions->where('name', 'Like', '%'.request()->input('search').'%');
        }

        if (request()->query('sort')) {
            $attribute = request()->query('sort');
            $sort_order = 'ASC';
            if (strncmp($attribute, '-', 1) === 0) {
                $sort_order = 'DESC';
                $attribute = substr($attribute, 1);
            }
            $permissions->orderBy($attribute, $sort_order);
        } else {
            $permissions->latest();
        }

        $permissions = $permissions->paginate(config('admin.paginate.per_page'))
                                ->onEachSide(config('admin.paginate.each_side'))
                                ->appends(request()->query());

        return Inertia::render('Admin/Permission/Index', [
            'permissions' => $permissions,
            'filters' => request()->all('search'),
            'can' => [
                'create' => Auth::user()->can('permission create'),
                'edit' => Auth::user()->can('permission edit'),
                'delete' => Auth::user()->can('permission delete'),
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
        $this->authorize('adminCreate', Permission::class);
        return Inertia::render('Admin/Permission/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PermissionCreateData $data, PermissionCreateAction $permissionCreateAction)
    {
        $this->authorize('adminCreate', Permission::class);
        $permissionCreateAction->handle($data);

        return redirect()->route('admin.permission.index')
            ->with('message', __('Permission created successfully.'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Inertia\Response
     */
    public function show(Permission $permission)
    {
        $this->authorize('adminView', $permission);
        return Inertia::render('Admin/Permission/Show', [
            'permission' => $permission,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Inertia\Response
     */
    public function edit(Permission $permission)
    {
        $this->authorize('adminUpdate', $permission);
        return Inertia::render('Admin/Permission/Edit', [
            'permission' => $permission,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PermissionUpdateData $data, Permission $permission, PermissionUpdateAction $permissionUpdateAction)
    {
        $this->authorize('adminUpdate', $permission);
        $permissionUpdateAction->handle($data, $permission);

        return redirect()->route('admin.permission.index')
            ->with('message', __('Permission updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Permission $permission)
    {
        $this->authorize('adminDelete', $permission);
        $permission->delete();

        return redirect()->route('admin.permission.index')
            ->with('message', __('Permission deleted successfully'));
    }
}
