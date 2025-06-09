<?php

namespace App\Http\Middleware\Admin;

use BalajiDharma\LaravelMenu\Models\Menu;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Inertia\Middleware;

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
        return [
            ...parent::share($request),
            'navigation' => [
                'menu' => Menu::getMenuTree('admin', false, true),
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
