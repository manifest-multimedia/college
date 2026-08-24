<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\DeviceDetector;
use App\Http\Controllers\Controller;
use App\Models\ExamDevice;
use App\Services\Exams\RegisteredExamDeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class ExamDeviceController extends Controller
{
    public function index()
    {
        return view('admin.exam-devices.index', ['devices' => ExamDevice::with('registeredBy')->latest()->paginate(20)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'device_type' => ['required', 'in:mobile,laptop,desktop,tablet'],
            'location' => ['nullable', 'string', 'max:100'],
        ]);

        $secret = Str::random(64);
        $device = ExamDevice::create([
            ...$data,
            'public_id' => (string) Str::uuid(),
            'token_hash' => hash('sha256', $secret),
            'status' => 'active',
            'device_metadata' => (new DeviceDetector)->getDeviceInfo(),
            'registered_by' => $request->user()->id,
        ]);

        // Keep the credential unavailable to JavaScript. The production fallback
        // protects deployments behind a TLS-terminating reverse proxy.
        $secureCookie = $request->isSecure() || app()->environment('production');

        Cookie::queue(cookie(
            RegisteredExamDeviceService::COOKIE,
            $device->public_id.'.'.$secret,
            60 * 24 * 365,
            '/', null, $secureCookie, true, false, 'Strict'
        ));

        return back()->with('success', "{$device->label} is registered on this browser device.");
    }

    public function suspend(ExamDevice $examDevice)
    {
        $examDevice->update(['status' => 'suspended']);
        return back()->with('success', 'Device suspended.');
    }

    public function activate(ExamDevice $examDevice)
    {
        $examDevice->update(['status' => 'active', 'revoked_at' => null, 'revoked_by' => null, 'revocation_reason' => null]);
        return back()->with('success', 'Device activated.');
    }

    public function revoke(Request $request, ExamDevice $examDevice)
    {
        $examDevice->update(['status' => 'revoked', 'revoked_at' => now(), 'revoked_by' => $request->user()->id, 'revocation_reason' => $request->input('reason')]);
        return back()->with('success', 'Device revoked.');
    }
}
