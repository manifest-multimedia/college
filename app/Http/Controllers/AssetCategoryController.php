<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssetCategoryRequest;
use App\Http\Requests\UpdateAssetCategoryRequest;
use App\Models\AssetCategory;

class AssetCategoryController extends Controller
{
    /**
     * Display a listing of asset categories.
     */
    public function index()
    {
        $categories = AssetCategory::with(['parent', 'children'])
            ->orderBy('name')
            ->get();

        return view('assets.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new asset category.
     */
    public function create()
    {
        $categories = AssetCategory::all();

        return view('assets.categories.create', compact('categories'));
    }

    /**
     * Store a newly created asset category in storage.
     */
    public function store(StoreAssetCategoryRequest $request)
    {
        AssetCategory::create($request->validated());

        return redirect()->route('admin.asset-categories.index')
            ->with('success', 'Asset category created successfully.');
    }

    /**
     * Display the specified asset category.
     */
    public function show(AssetCategory $assetCategory)
    {
        $assetCategory->load(['parent', 'children', 'assets']);

        return view('assets.categories.show', compact('assetCategory'));
    }

    /**
     * Show the form for editing the specified asset category.
     */
    public function edit(AssetCategory $assetCategory)
    {
        $categories = AssetCategory::where('id', '!=', $assetCategory->id)->get();

        return view('assets.categories.edit', compact('assetCategory', 'categories'));
    }

    /**
     * Update the specified asset category in storage.
     */
    public function update(UpdateAssetCategoryRequest $request, AssetCategory $assetCategory)
    {
        $assetCategory->update($request->validated());

        return redirect()->route('admin.asset-categories.show', $assetCategory)
            ->with('success', 'Asset category updated successfully.');
    }

    /**
     * Remove the specified asset category from storage.
     */
    public function destroy(AssetCategory $assetCategory)
    {
        // Check if category has assets or children
        if ($assetCategory->assets()->exists()) {
            return redirect()->route('admin.asset-categories.index')
                ->with('error', 'Cannot delete category that has assets assigned to it.');
        }

        if ($assetCategory->children()->exists()) {
            return redirect()->route('admin.asset-categories.index')
                ->with('error', 'Cannot delete category that has child categories.');
        }

        $assetCategory->delete();

        return redirect()->route('admin.asset-categories.index')
            ->with('success', 'Asset category deleted successfully.');
    }
}
