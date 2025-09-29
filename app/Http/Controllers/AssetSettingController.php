<?php

namespace App\Http\Controllers;

use App\Models\AssetSetting;
use Illuminate\Http\Request;

class AssetSettingController extends Controller
{
    /**
     * Display asset settings.
     */
    public function index()
    {
        $settings = AssetSetting::orderBy('key')->get();
        return view('assets.settings.index', compact('settings'));
    }

    /**
     * Update asset tag prefix.
     */
    public function updatePrefix(Request $request)
    {
        $validated = $request->validate([
            'asset_tag_prefix' => 'required|string|max:10',
        ]);

        AssetSetting::setValue(
            'asset_tag_prefix', 
            $validated['asset_tag_prefix'],
            'Prefix used for auto-generated asset tags'
        );

        return redirect()->route('asset-settings.index')
            ->with('success', 'Asset tag prefix updated successfully.');
    }

    /**
     * Create or update a setting.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100',
            'value' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        AssetSetting::setValue(
            $validated['key'],
            $validated['value'],
            $validated['description'] ?? null
        );

        return redirect()->route('asset-settings.index')
            ->with('success', 'Setting saved successfully.');
    }

    /**
     * Delete a setting.
     */
    public function destroy(AssetSetting $assetSetting)
    {
        // Prevent deletion of critical settings
        if ($assetSetting->key === 'asset_tag_prefix') {
            return redirect()->route('asset-settings.index')
                ->with('error', 'Cannot delete the asset tag prefix setting.');
        }

        $assetSetting->delete();

        return redirect()->route('asset-settings.index')
            ->with('success', 'Setting deleted successfully.');
    }
}