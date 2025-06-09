<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use BalajiDharma\LaravelAdminCore\Actions\MenuItem\MenuItemCreateAction;
use BalajiDharma\LaravelAdminCore\Actions\MenuItem\MenuItemUpdateAction;
use BalajiDharma\LaravelAdminCore\Data\MenuItem\MenuItemCreateData;
use BalajiDharma\LaravelAdminCore\Data\MenuItem\MenuItemUpdateData;
use BalajiDharma\LaravelMenu\Models\Menu;
use BalajiDharma\LaravelMenu\Models\MenuItem;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MenuItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Menu $menu)
    {
        $this->authorize('adminViewAny', MenuItem::class);
        $items = (new MenuItem)->toTree($menu->id, true);

        return Inertia::render('Admin/Menu/Item/Index', [
            'menu' => $menu,
            'items' => $items,
            'can' => [
                'create' => Auth::user()->can('menu.item create'),
                'edit' => Auth::user()->can('menu.item edit'),
                'delete' => Auth::user()->can('menu.item delete'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create(Menu $menu)
    {
        $this->authorize('adminCreate', MenuItem::class);
        $itemOptions = MenuItem::selectOptions($menu->id, null, true);
        $roles = Role::all()->pluck('name', 'name');

        return Inertia::render('Admin/Menu/Item/Create', compact('menu', 'itemOptions', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(MenuItemCreateData $data, Menu $menu, MenuItemCreateAction $menuItemCreateAction)
    {
        $this->authorize('adminCreate', MenuItem::class);
        $menuItemCreateAction->handle($data, $menu);

        return redirect()->route('admin.menu.item.index', $menu->id)
            ->with('message', 'Menu Item created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Inertia\Response
     */
    public function edit(Menu $menu, MenuItem $item)
    {
        $this->authorize('adminUpdate', $item);
        $itemOptions = MenuItem::selectOptions($menu->id, $item->parent_id ?? $item->id);
        $roles = Role::all()->pluck('name', 'name');
        $itemHasRoles = array_column(json_decode($item->roles, true), 'name');

        return Inertia::render('Admin/Menu/Item/Edit', compact('menu', 'item', 'itemOptions', 'roles', 'itemHasRoles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(MenuItemUpdateData $data, Menu $menu, MenuItem $item, MenuItemUpdateAction $menuItemUpdateAction)
    {
        $this->authorize('adminUpdate', $item);
        $menuItemUpdateAction->handle($data, $item);

        return redirect()->route('admin.menu.item.index', $menu->id)
            ->with('message', 'Menu Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \BalajiDharma\LaravelMenu\Models\MenuItem  $menuItem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Menu $menu, MenuItem $item)
    {
        $this->authorize('adminDelete', $item);
        $item->delete();

        return redirect()->route('admin.menu.item.index', $menu->id)
            ->with('message', __('Menu deleted successfully'));
    }
}
