<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemConfigController extends Controller
{
    public function index()
    {
        $configs = SystemConfig::all()->groupBy('group');

        return view('admin.configs.index', compact('configs'));
    }

    public function update(Request $request)
    {
        $inputs = $request->except(['_token', '_method']);

        $updated = [];
        foreach ($inputs as $key => $value) {
            $config = SystemConfig::where('key', $key)->first();
            if ($config) {
                $oldValue = $config->value;
                $config->value = is_array($value) ? json_encode($value) : (string) $value;
                $config->save();
                $updated[$key] = ['old' => $oldValue, 'new' => $config->value];
            }
        }

        AuditLog::record(
            action: 'CONFIG_UPDATED',
            targetType: 'SystemConfig',
            targetId: 'all',
            description: "Pengaturan konfigurasi sistem diperbarui oleh " . Auth::user()->name,
            metadata: $updated
        );

        return back()->with('success', 'Konfigurasi sistem berhasil diperbarui.');
    }
}
