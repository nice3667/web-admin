<?php

namespace Database\Seeders;

use BalajiDharma\LaravelCategory\Models\CategoryType;
use BalajiDharma\LaravelMenu\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Arr;


class AdminCoreSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'admin user',
            'permission list',
            'permission create',
            'permission edit',
            'permission delete',
            'role list',
            'role create',
            'role edit',
            'role delete',
            'user list',
            'user create',
            'user edit',
            'user delete',
            'menu list',
            'menu create',
            'menu edit',
            'menu delete',
            'menu.item list',
            'menu.item create',
            'menu.item edit',
            'menu.item delete',
            'category list',
            'category create',
            'category edit',
            'category delete',
            'category.type list',
            'category.type create',
            'category.type edit',
            'category.type delete',
            'media list',
            'media create',
            'media edit',
            'media delete',
            'comment list',
            'comment create',
            'comment edit',
            'comment delete',
            'thread list',
            'thread create',
            'thread edit',
            'thread delete',
            'activitylog list',
            'activitylog delete',
            'attribute list',
            'attribute create',
            'attribute edit',
            'attribute delete',
            'reaction list',
            'reaction create',
            'reaction edit',
            'reaction delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $role1 = Role::firstOrCreate(['name' => 'super-admin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        $role2 = Role::firstOrCreate(['name' => 'admin']);
        foreach ($permissions as $permission) {
            $role2->givePermissionTo($permission);
        }

        // create roles and assign existing permissions
        $role3 = Role::firstOrCreate(['name' => 'writer']);
        $role3->givePermissionTo('admin user');
        foreach ($permissions as $permission) {
            if (Str::contains($permission, 'list')) {
                $role3->givePermissionTo($permission);
            }
        }

        // create demo users
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            array_merge(\App\Models\User::factory()->raw(), [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com'
            ])
        );
        $user->assignRole($role1);

        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@example.com'],
            array_merge(\App\Models\User::factory()->raw(), [
                'name' => 'Admin User',
                'email' => 'admin@example.com'
            ])
        );
        $user->assignRole($role2);

        // writer test
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            array_merge(\App\Models\User::factory()->raw(), [
                'name' => 'Example User',
                'email' => 'test@example.com'
            ])
        );
        $user->assignRole($role3);

        // create menu
        $menu = Menu::firstOrCreate(
            ['machine_name' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Admin Menu',
            ]
        );

        $menu_items = [
            [
                'name' => 'Dashboard',
                'uri' => '/<admin>',
                'enabled' => 1,
                'weight' => 0,
                'icon' => 'M13,3V9H21V3M13,21H21V11H13M3,21H11V15H3M3,13H11V3H3V13Z',
            ],

            [
                'name' => 'Roles',
                'uri' => '/<admin>/role',
                'enabled' => 1,
                'weight' => 2,
                'icon' => 'M12,5.5A3.5,3.5 0 0,1 15.5,9A3.5,3.5 0 0,1 12,12.5A3.5,3.5 0 0,1 8.5,9A3.5,3.5 0 0,1 12,5.5M5,8C5.56,8 6.08,8.15 6.53,8.42C6.38,9.85 6.8,11.27 7.66,12.38C7.16,13.34 6.16,14 5,14A3,3 0 0,1 2,11A3,3 0 0,1 5,8M19,8A3,3 0 0,1 22,11A3,3 0 0,1 19,14C17.84,14 16.84,13.34 16.34,12.38C17.2,11.27 17.62,9.85 17.47,8.42C17.92,8.15 18.44,8 19,8M5.5,18.25C5.5,16.18 8.41,14.5 12,14.5C15.59,14.5 18.5,16.18 18.5,18.25V20H5.5V18.25M0,20V18.5C0,17.11 1.89,15.94 4.45,15.6C3.86,16.28 3.5,17.22 3.5,18.25V20H0M24,20H20.5V18.25C20.5,17.22 20.14,16.28 19.55,15.6C22.11,15.94 24,17.11 24,18.5V20Z',
                'permission' => 'super-admin',
            ],
            [
                'name' => 'Users',
                'uri' => '/<admin>/user',
                'enabled' => 1,
                'weight' => 3,
                'icon' => 'M16 17V19H2V17S2 13 9 13 16 17 16 17M12.5 7.5A3.5 3.5 0 1 0 9 11A3.5 3.5 0 0 0 12.5 7.5M15.94 13A5.32 5.32 0 0 1 18 17V19H22V17S22 13.37 15.94 13M15 4A3.39 3.39 0 0 0 13.07 4.59A5 5 0 0 1 13.07 10.41A3.39 3.39 0 0 0 15 11A3.5 3.5 0 0 0 15 4Z',
                'permission' => 'super-admin',
            ],
        ];

        // Create menu items
        foreach ($menu_items as $item) {
            $menu->menuItems()->updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }

        // Create Report parent menu item
        $reportMenu = $menu->menuItems()->updateOrCreate(
            ['name' => 'Report'],
            [
                'name' => 'Report',
                'uri' => '<nolink>',
                'enabled' => 1,
                'weight' => 4,
                'icon' => 'M9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4zm2.5 2.1h-15V5h15v14.1zm0-16.1h-15c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h15c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z'
            ]
        );

        // Create Report child menu items
        $reportChildItems = [
            [
                'name' => 'Clients',
                'uri' => '/admin/reports/clients',
                'enabled' => 1,
                'weight' => 1,
                'parent_id' => $reportMenu->id
            ],
            [
                'name' => 'Client account',
                'uri' => '/admin/reports/client-account',
                'enabled' => 1,
                'weight' => 2,
                'parent_id' => $reportMenu->id
            ],
            [
                'name' => 'Reward history',
                'uri' => '/admin/reports/reward-history',
                'enabled' => 1,
                'weight' => 3,
                'parent_id' => $reportMenu->id
            ],
            [
                'name' => 'Client transactions',
                'uri' => '/admin/reports/client-transaction',
                'enabled' => 1,
                'weight' => 4,
                'parent_id' => $reportMenu->id
            ],
            [
                'name' => 'Transactions pending payment',
                'uri' => '/admin/reports/transactions-pending',
                'enabled' => 1,
                'weight' => 5,
                'parent_id' => $reportMenu->id
            ]
        ];

        // Create child menu items
        foreach ($reportChildItems as $item) {
            $menu->menuItems()->updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }

        // Create Rebates menu item
        $menu->menuItems()->updateOrCreate(
            ['name' => 'Rebates'],
            [
                'name' => 'Rebates',
                'uri' => '/<admin>/rebates',
                'enabled' => 1,
                'weight' => 5,
                'icon' => 'M12,8H4A2,2 0 0,0 2,10V14A2,2 0 0,0 4,16H5V20A1,1 0 0,0 6,21H8A1,1 0 0,0 9,20V16H12L17,20V4L12,8M21.5,12C21.5,13.71 20.54,15.26 19,16V8C20.53,8.75 21.5,10.3 21.5,12Z'
            ]
        );

        // Create Promo menu item
        $promoMenu = $menu->menuItems()->updateOrCreate(
            ['name' => 'Promo'],
            [
                'name' => 'Promo',
                'uri' => '<nolink>',
                'enabled' => 1,
                'weight' => 6,
                'icon' => 'M20 2H4C2.89 2 2 2.89 2 4V20C2 21.11 2.89 22 4 22H20C21.11 22 22 21.11 22 20V4C22 2.89 21.11 2 20 2M20 20H4V4H20V20M18 11H6V9H18V11M18 15H6V13H18V15M18 7H6V5H18V7Z'
            ]
        );

        // Create Promo child menu items
        $promoChildItems = [
            [
                'name' => 'Active Promotions',
                'uri' => '/<admin>/promo/active',
                'enabled' => 1,
                'weight' => 1,
                'parent_id' => $promoMenu->id
            ],
            [
                'name' => 'Promotion History',
                'uri' => '/<admin>/promo/history',
                'enabled' => 1,
                'weight' => 2,
                'parent_id' => $promoMenu->id
            ],
            [
                'name' => 'Create Promotion',
                'uri' => '/<admin>/promo/create',
                'enabled' => 1,
                'weight' => 3,
                'parent_id' => $promoMenu->id
            ]
        ];

        // Create Promo child menu items
        foreach ($promoChildItems as $item) {
            $menu->menuItems()->updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }

        // Create Support menu item
        $supportMenu = $menu->menuItems()->updateOrCreate(
            ['name' => 'Support'],
            [
                'name' => 'Support',
                'uri' => '<nolink>',
                'enabled' => 1,
                'weight' => 7,
                'icon' => 'M20,2H4A2,2 0 0,0 2,4V22L6,18H20A2,2 0 0,0 22,16V4A2,2 0 0,0 20,2M20,16H6L4,18V4H20V16M6,12H8V14H6V12M6,8H8V10H6V8M10,8H18V10H10V8M10,12H15V14H10V12Z'
            ]
        );

        // Create Support child menu items
        $supportChildItems = [
            [
                'name' => 'Tickets',
                'uri' => '/<admin>/support/tickets',
                'enabled' => 1,
                'weight' => 1,
                'parent_id' => $supportMenu->id
            ],
            [
                'name' => 'Live Chat',
                'uri' => '/<admin>/support/chat',
                'enabled' => 1,
                'weight' => 2,
                'parent_id' => $supportMenu->id
            ],
            [
                'name' => 'FAQ Management',
                'uri' => '/<admin>/support/faq',
                'enabled' => 1,
                'weight' => 3,
                'parent_id' => $supportMenu->id
            ]
        ];

        // Create Support child menu items
        foreach ($supportChildItems as $item) {
            $menu->menuItems()->updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }

        $category_types = [
            [
                'name' => 'Category',
                'machine_name' => 'category',
                'description' => 'Main Category',
            ],
            [
                'name' => 'Tag',
                'machine_name' => 'tag',
                'description' => 'Site Tags',
                'is_flat' => true,
            ],
            [
                'name' => 'Admin Tag',
                'machine_name' => 'admin_tag',
                'description' => 'Admin Tags',
                'is_flat' => true,
            ],
            [
                'name' => 'Forum Category',
                'machine_name' => 'forum_category',
                'description' => 'Forum Category',
            ],
            [
                'name' => 'Forum Tag',
                'machine_name' => 'forum_tag',
                'description' => 'Forum Tags',
                'is_flat' => true,
            ]
        ];

        foreach ($category_types as $category_type) {
            CategoryType::updateOrCreate(
                ['machine_name' => $category_type['machine_name']],
                $category_type
            );
        }

        $forumCategoryType = CategoryType::firstWhere(['machine_name' => 'forum_category']);

        $forumCategoryType->categories()->updateOrCreate(
            ['name' => 'General'],
            [
                'description' => 'General Forum Category',
                'name' => 'General'
            ]
        );
    }
}
