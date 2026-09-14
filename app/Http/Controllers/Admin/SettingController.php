<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLog\ActivityLogService;
use App\Services\Setting\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    /** File-type setting keys and the subdirectory to store them under. */
    private const FILE_SETTINGS = [
        'site_logo' => 'settings',
        'site_favicon' => 'settings',
        'og_image' => 'settings',
    ];

    public function __construct(
        private readonly SettingService $settingService,
        private readonly ActivityLogService $activityLog
    ) {}

    /**
     * Display the global website settings management form.
     */
    public function index(): View
    {
        $groupedSettings = $this->settingService->getSettingsForForm();

        return view('admin.settings.index', compact('groupedSettings'));
    }

    /**
     * Update global website settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'site_logo' => ['nullable', 'image', 'max:2048'],
            'site_favicon' => ['nullable', 'image', 'max:512'],
            'og_image' => ['nullable', 'image', 'max:2048'],
            'admin_accent_color' => ['nullable', 'string', Rule::in(array_keys(SettingService::ACCENT_PALETTES))],
            'admin_sidebar_theme' => ['nullable', 'string', Rule::in(array_keys(SettingService::SIDEBAR_THEMES))],
        ]);

        // Check ACL for appearance settings
        if ($request->hasAny(['admin_accent_color', 'admin_sidebar_theme'])) {
            $user = $request->user();
            if (! $user->can('settings.appearance') && ! $user->hasRole('Administrator')) {
                abort(403, 'You do not have permission to modify dashboard appearance settings.');
            }
        }

        $settingsData = $request->except(['_token', '_method']);

        foreach (self::FILE_SETTINGS as $key => $directory) {
            if ($request->hasFile($key)) {
                // Delete the old file if one exists
                $existingPath = $this->settingService->getAllSettings()[$key] ?? null;
                if ($existingPath) {
                    $relativePath = ltrim(parse_url($existingPath, PHP_URL_PATH), '/');
                    $storageRelative = preg_replace('#^storage/#', '', $relativePath);
                    Storage::disk('public')->delete($storageRelative);
                }

                $path = $request->file($key)->store($directory, 'public');
                $settingsData[$key] = Storage::disk('public')->url($path);
            } else {
                // No new file — preserve the existing value
                unset($settingsData[$key]);
            }
        }

        $this->settingService->saveSettings($settingsData);

        $this->activityLog->log(auth()->user(), 'updated', 'Updated website settings.');

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
