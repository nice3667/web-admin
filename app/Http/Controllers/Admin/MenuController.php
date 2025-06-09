<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use BalajiDharma\LaravelAdminCore\Actions\Menu\MenuCreateAction;
use BalajiDharma\LaravelAdminCore\Actions\Menu\MenuUpdateAction;
use BalajiDharma\LaravelAdminCore\Data\Menu\MenuCreateData;
use BalajiDharma\LaravelAdminCore\Data\Menu\MenuUpdateData;
use BalajiDharma\LaravelMenu\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $this->authorize('adminViewAny', Menu::class);
        $menus = (new Menu)->newQuery();

        if (request()->has('search')) {
            $menus->where('name', 'Like', '%'.request()->input('search').'%');
        }

        if (request()->query('sort')) {
            $attribute = request()->query('sort');
            $sort_order = 'ASC';
            if (strncmp($attribute, '-', 1) === 0) {
                $sort_order = 'DESC';
                $attribute = substr($attribute, 1);
            }
            $menus->orderBy($attribute, $sort_order);
        } else {
            $menus->latest();
        }

        $menus = $menus->paginate(config('admin.paginate.per_page'))
                    ->onEachSide(config('admin.paginate.each_side'))
                    ->appends(request()->query());

        return Inertia::render('Admin/Menu/Index', [
            'menus' => $menus,
            'filters' => request()->all('search'),
            'can' => [
                'create' => Auth::user()->can('menu create'),
                'edit' => Auth::user()->can('menu edit'),
                'delete' => Auth::user()->can('menu delete'),
                'manage' => Auth::user()->can('menu.item index'),
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
        $this->authorize('adminCreate', Menu::class);
        return Inertia::render('Admin/Menu/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(MenuCreateData $data, MenuCreateAction $menuCreateAction)
    {
        $this->authorize('adminCreate', Menu::class);
        $menuCreateAction->handle($data);

        return redirect()->route('admin.menu.index')
            ->with('message', 'Menu created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Inertia\Response
     */
    public function edit(Menu $menu)
    {
        $this->authorize('adminUpdate', $menu);
        return Inertia::render('Admin/Menu/Edit', [
            'menu' => $menu,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(MenuUpdateData $data, Menu $menu, MenuUpdateAction $menuUpdateAction)
    {
        $this->authorize('adminUpdate', $menu);
        $menuUpdateAction->handle($data, $menu);

        return redirect()->route('admin.menu.index')
            ->with('message', 'Menu updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Menu $menu)
    {
        $this->authorize('adminDelete', $menu);
        $menu->delete();

        return redirect()->route('admin.menu.index')
            ->with('message', __('Menu deleted successfully'));
    }
}
