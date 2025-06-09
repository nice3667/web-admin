<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use BalajiDharma\LaravelAdminCore\Actions\Media\MediaCreateAction;
use BalajiDharma\LaravelAdminCore\Actions\Media\MediaUpdateAction;
use BalajiDharma\LaravelAdminCore\Data\Media\MediaCreateData;
use BalajiDharma\LaravelAdminCore\Data\Media\MediaUpdateData;
use BalajiDharma\LaravelAdminCore\Data\Media\MediaData;
use Plank\Mediable\Media;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $this->authorize('adminViewAny', Media::class);
        $mediaItems = (new Media)->newQuery();
        $mediaItems->whereIsOriginal();
        if (request()->has('search')) {
            $mediaItems->where('filename', 'Like', '%'.request()->input('search').'%');
        }

        if (request()->query('sort')) {
            $attribute = request()->query('sort');
            $sort_order = 'ASC';
            if (strncmp($attribute, '-', 1) === 0) {
                $sort_order = 'DESC';
                $attribute = substr($attribute, 1);
            }
            $mediaItems->orderBy($attribute, $sort_order);
        } else {
            $mediaItems->latest();
        }

        $mediaItems = $mediaItems->paginate(config('admin.paginate.per_page'))
            ->onEachSide(config('admin.paginate.each_side'));


        return Inertia::render('Admin/Media/Index', [
            'items' => MediaData::collect($mediaItems),
            'filters' => request()->all('search'),
            'can' => [
                'create' => Auth::user()->can('media create'),
                'edit' => Auth::user()->can('media edit'),
                'delete' => Auth::user()->can('media delete'),
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
        $this->authorize('adminCreate', Media::class);
        $typeOptions = media_type_as_options();
        return Inertia::render('Admin/Media/Create', [
            'typeOptions' => $typeOptions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(MediaCreateData $data, MediaCreateAction $mediaCreateAction)
    {
        $this->authorize('adminCreate', Media::class);
        $mediaCreateAction->handle($data);

        return redirect()->route('admin.media.index')
            ->with('message', __('Media created successfully.'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Inertia\Response
     */
    public function show($id)
    {
        $media = Media::findOrFail($id);
        $this->authorize('adminView', $media);

        return Inertia::render('Admin/Media/Show', [
            'media' => MediaData::from($media),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Inertia\Response
     */
    public function edit($id)
    {
        $media = Media::findOrFail($id);
        $this->authorize('adminUpdate', $media);
        $typeOptions = media_type_as_options();

        return Inertia::render('Admin/Media/Edit', [
            'media' => MediaData::from($media),
            'typeOptions' => $typeOptions,
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(MediaUpdateData $mediaUpdateData, $id, MediaUpdateAction $mediaUpdateAction)
    {
        $media = Media::findOrFail($id);
        $this->authorize('adminUpdate', $media);
        $mediaUpdateAction->handle($mediaUpdateData, $media);

        return redirect()->route('admin.media.index')
            ->with('message', __('Media updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $media = Media::findOrFail($id);
        $this->authorize('adminDelete', $media);
        $media->getAllVariantsAndSelf()->each(function (Media $variant) {
            $variant->delete();
        });

        return redirect()->route('admin.media.index')
            ->with('message', __('Media deleted successfully.'));
    }
}
