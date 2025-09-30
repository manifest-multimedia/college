<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssetController extends Controller
{
    /**
     * Display a listing of assets.
     */
    public function index(Request $request)
    {
        $query = Asset::with(['category', 'department', 'createdBy', 'updatedBy']);

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by state
        if ($request->filled('state')) {
            $query->withState($request->state);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->withCategory($request->category_id);
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $assets = $query->paginate(20);
        $categories = AssetCategory::all();
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();

        return view('assets.index', compact('assets', 'categories', 'departments'));
    }

    /**
     * Show the form for creating a new asset.
     */
    public function create()
    {
        $categories = AssetCategory::all();
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        return view('assets.create', compact('categories', 'departments'));
    }

    /**
     * Store a newly created asset in storage.
     */
    public function store(StoreAssetRequest $request)
    {
        $asset = Asset::create($request->validated());

        return redirect()->route('admin.assets.show', $asset)
            ->with('success', 'Asset created successfully with tag: ' . $asset->asset_tag);
    }

    /**
     * Display the specified asset.
     */
    public function show(Asset $asset)
    {
        $asset->load(['category', 'department', 'createdBy', 'updatedBy', 'histories.user']);
        
        return view('assets.show', compact('asset'));
    }

    /**
     * Show the form for editing the specified asset.
     */
    public function edit(Asset $asset)
    {
        $categories = AssetCategory::all();
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        return view('assets.edit', compact('asset', 'categories', 'departments'));
    }

        /**
     * Update the specified asset in storage.
     */
    public function update(UpdateAssetRequest $request, Asset $asset)
    {
        $asset->update($request->validated());

        return redirect()->route('admin.assets.show', $asset)
            ->with('success', 'Asset updated successfully.');
    }

    /**
     * Remove the specified asset from storage.
     */
    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('admin.assets.index')
            ->with('success', 'Asset deleted successfully.');
    }
}