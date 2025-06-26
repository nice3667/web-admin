<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Illuminate\Support\Facades\Route;

// Dashboard
Breadcrumbs::for('dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Dashboard', route('dashboard'));
});

// Admin Dashboard
Breadcrumbs::for('admin.dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Admin Dashboard', route('admin.dashboard'));
});

// Admin > Customers
Breadcrumbs::for('admin.customers.index', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Customers', route('admin.customers.index'));
});

// Admin > Reports > Clients
Breadcrumbs::for('admin.reports.clients', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Reports');
    $trail->push('Clients', route('admin.reports.clients'));
});

// Admin > Reports > Client Account
Breadcrumbs::for('admin.reports.client-account', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Reports');
    $trail->push('Client Account', route('admin.reports.client-account'));
});

// Admin > Reports > Reward History
Breadcrumbs::for('admin.reports.reward-history', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Reports');
    $trail->push('Reward History', route('admin.reports.reward-history'));
});

// Admin > Reports > Client Transactions
Breadcrumbs::for('admin.reports.client-transaction', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Reports');
    $trail->push('Client Transactions', route('admin.reports.client-transaction'));
});

// Admin > Reports > Transactions Pending
Breadcrumbs::for('admin.reports.transactions-pending', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Reports');
    $trail->push('Transactions Pending', route('admin.reports.transactions-pending'));
});

Breadcrumbs::macro('resource', function (string $name, string $title, ?string $parentName = null) {
    if ($parentName) {
        Breadcrumbs::for("{$name}.index", function (BreadcrumbTrail $trail, $model) use ($name, $title, $parentName) {
            $trail->parent("{$parentName}.show", $model);
            $trail->push($title, route("{$name}.index", $model));
        });

        Breadcrumbs::for("{$name}.create", function (BreadcrumbTrail $trail, $model) use ($name) {
            $trail->parent("{$name}.index", $model);
            $trail->push('Create', route("{$name}.create", $model));
        });

        Breadcrumbs::for("{$name}.show", function (BreadcrumbTrail $trail, $model, $item) use ($name) {
            $trail->parent("{$name}.index", $model);
            if (Route::has("{$name}.show")) {
                $trail->push($item->name ?? $model, route("{$name}.show", [$model, $item]));
            } else {
                $trail->push($item->name ?? $model);
            }
        });

        Breadcrumbs::for("{$name}.edit", function (BreadcrumbTrail $trail, $model, $item) use ($name) {
            $trail->parent("{$name}.show", $model, $item);
            $trail->push('Edit', route("{$name}.edit", [$model, $item]));
        });

    } else {
        Breadcrumbs::for("{$name}.index", function (BreadcrumbTrail $trail) use ($name, $title) {
            $trail->parent('admin.dashboard');
            $trail->push($title, route("{$name}.index"));
        });

        Breadcrumbs::for("{$name}.create", function (BreadcrumbTrail $trail) use ($name) {
            $trail->parent("{$name}.index");
            $trail->push('Create', route("{$name}.create"));
        });

        Breadcrumbs::for("{$name}.show", function (BreadcrumbTrail $trail, $model) use ($name) {
            $trail->parent("{$name}.index");
            if (Route::has("{$name}.show")) {
                $trail->push($model->name ?? $model, route("{$name}.show", $model));
            } else {
                $trail->push($model->name ?? $model);
            }
        });

        Breadcrumbs::for("{$name}.edit", function (BreadcrumbTrail $trail, $model) use ($name) {
            $trail->parent("{$name}.show", $model);
            $trail->push('Edit', route("{$name}.edit", $model));
        });
    }
});

Breadcrumbs::resource('admin.permission', 'Permissions');
Breadcrumbs::resource('admin.role', 'Roles');
Breadcrumbs::resource('admin.user', 'Users');
Breadcrumbs::resource('admin.media', 'Media');
Breadcrumbs::resource('admin.menu', 'Menu');
Breadcrumbs::resource('admin.menu.item', 'Menu Items', 'admin.menu');
Breadcrumbs::resource('admin.category.type', 'Category Types');
Breadcrumbs::resource('admin.category.type.item', 'Items', 'admin.category.type');

// admin account Info
Breadcrumbs::for('admin.account.info', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Account Info', route('admin.account.info'));
});
