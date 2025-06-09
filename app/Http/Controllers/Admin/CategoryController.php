<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use BalajiDharma\LaravelAdminCore\Actions\Category\CategoryCreateAction;
use BalajiDharma\LaravelAdminCore\Actions\Category\CategoryUpdateAction;
use BalajiDharma\LaravelAdminCore\Data\Category\CategoryCreateData;
use BalajiDharma\LaravelAdminCore\Data\Category\CategoryUpdateData;
use BalajiDharma\LaravelCategory\Models\Category;
use BalajiDharma\LaravelCategory\Models\CategoryType;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(CategoryType $type)
    {
        $this->authorize('adminViewAny', Category::class);
        $items = (new Category)->toTree($type->id, true);

        return Inertia::render('Admin/Category/Item/Index', [
            'categoryType' => $type,
            'items' => $items,
            'can' => [
                'create' => Auth::user()->can('category create'),
                'edit' => Auth::user()->can('category edit'),
                'delete' => Auth::user()->can('category delete'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create(CategoryType $type)
    {
        $this->authorize('adminCreate', Category::class);
        $itemOptions = Category::selectOptions($type->id, null, true);

        return Inertia::render('Admin/Category/Item/Create', [
            'categoryType' => $type,
            'itemOptions' => $itemOptions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CategoryCreateData $data, CategoryType $type, CategoryCreateAction $categoryCreateAction)
    {
        $this->authorize('adminCreate', Category::class);
        $categoryCreateAction->handle($data, $type);

        return redirect()->route('admin.category.type.item.index', $type->id)
            ->with('message', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Inertia\Response
     */
    public function edit(CategoryType $type, Category $item)
    {
        $this->authorize('adminUpdate', $item);
        $itemOptions = Category::selectOptions($type->id, $item->parent_id ?? $item->id);

        return Inertia::render('Admin/Category/Item/Edit', [
            'categoryType' => $type,
            'item' => $item,
            'itemOptions' => $itemOptions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(CategoryUpdateData $data, CategoryType $type, Category $item, CategoryUpdateAction $categoryUpdateAction)
    {
        $this->authorize('adminUpdate', $item);
        $categoryUpdateAction->handle($data, $item);

        return redirect()->route('admin.category.type.item.index', $type->id)
            ->with('message', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \BalajiDharma\LaravelCategory\Models\Category  $typeItem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(CategoryType $type, Category $item)
    {
        $this->authorize('adminDelete', $item);
        $item->delete();

        return redirect()->route('admin.category.type.item.index', $type->id)
            ->with('message', __('Category deleted successfully'));
    }
}
