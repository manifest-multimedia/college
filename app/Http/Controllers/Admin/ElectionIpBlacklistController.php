<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElectionIpBlacklist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ElectionIpBlacklistController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search')->value());

        $entries = ElectionIpBlacklist::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.elections.ip-blacklist', [
            'entries' => $entries,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ip_address' => ['required', 'ip'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        ElectionIpBlacklist::query()->updateOrCreate(
            ['ip_address' => $validated['ip_address']],
            [
                'reason' => $validated['reason'] ?? null,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]
        );

        return redirect()
            ->route('admin.elections.ip-blacklist.index')
            ->with('success', 'IP has been added to the election blacklist.');
    }

    public function toggle(ElectionIpBlacklist $entry): RedirectResponse
    {
        $entry->update([
            'is_active' => ! $entry->is_active,
        ]);

        return redirect()
            ->route('admin.elections.ip-blacklist.index')
            ->with('success', 'Blacklist status updated successfully.');
    }

    public function destroy(ElectionIpBlacklist $entry): RedirectResponse
    {
        $entry->delete();

        return redirect()
            ->route('admin.elections.ip-blacklist.index')
            ->with('success', 'IP removed from election blacklist.');
    }
}
