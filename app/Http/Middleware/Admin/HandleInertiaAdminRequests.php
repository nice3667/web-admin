<?php

namespace App\Http\Middleware\Admin;

use BalajiDharma\LaravelMenu\Models\Menu;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class HandleInertiaAdminRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $menu = Menu::where('machine_name', 'admin')->first();
        $menuItems = $menu ? Menu::getMenuTree('admin', true) : [];

        // Show all menu items to all users
        $filteredMenu = collect($menuItems);

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'menu' => $filteredMenu->values(),
            'navigation' => [
                'breadcrumbs' => $this->getBreadcrumbs($request),
            ],
        ]);
    }

    protected function getBreadcrumbs(Request $request)
    {
        if (!$request->isMethod('get')) {
            return [];
        }

        try {
            $routeName = Route::currentRouteName();
            if ($routeName) {
                return Breadcrumbs::generate($routeName);
            }
        } catch (\Exception $e) {
            // Return empty array if breadcrumb not found
            return [];
        }

        return [];
    }
}
