<?php

namespace App\Http\Middleware\Admin;

use BalajiDharma\LaravelMenu\Models\Menu;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;

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
        $menu = Menu::getMenuTree('admin', false, true);
        $user = Auth::user();

        // Filter menu items based on user role
        $filteredMenu = array_filter($menu, function($item) use ($user) {
            // If no permission is required, show the item
            if (!isset($item['permission'])) {
                return true;
            }

            // If permission is required, check if user has the role
            return $user->hasRole($item['permission']);
        });

        return [
            ...parent::share($request),
            'navigation' => [
                'menu' => array_values($filteredMenu),
                'breadcrumbs' => $this->getBreadcrumbs($request),
            ],
        ];
    }

    protected function getBreadcrumbs(Request $request)
    {
        if($request->isMethod('get'))
        {
            return Breadcrumbs::generate();
        }
    }
}
