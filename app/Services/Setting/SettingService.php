<?php

namespace App\Services\Setting;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Available accent color palettes for the admin dashboard.
     *
     * @var array<string, array{name: string, preview: string, shades: array<int, string>}>
     */
    public const ACCENT_PALETTES = [
        'emerald' => [
            'name' => 'Emerald Green',
            'preview' => '#10b981',
            'shades' => [
                50 => '#ecfdf5', 100 => '#d1fae5', 200 => '#a7f3d0', 300 => '#6ee7b7', 400 => '#34d399',
                500 => '#10b981', 600 => '#059669', 700 => '#047857', 800 => '#065f46', 900 => '#064e3b', 950 => '#022c22',
            ],
        ],
        'teal' => [
            'name' => 'Teal Ocean',
            'preview' => '#14b8a6',
            'shades' => [
                50 => '#f0fdfa', 100 => '#ccfbf1', 200 => '#99f6e4', 300 => '#5eead4', 400 => '#2dd4bf',
                500 => '#14b8a6', 600 => '#0d9488', 700 => '#0f766e', 800 => '#115e59', 900 => '#134e4a', 950 => '#042f2e',
            ],
        ],
        'blue' => [
            'name' => 'Royal Blue',
            'preview' => '#3b82f6',
            'shades' => [
                50 => '#eff6ff', 100 => '#dbeafe', 200 => '#bfdbfe', 300 => '#93c5fd', 400 => '#60a5fa',
                500 => '#3b82f6', 600 => '#2563eb', 700 => '#1d4ed8', 800 => '#1e40af', 900 => '#1e3a8a', 950 => '#172554',
            ],
        ],
        'indigo' => [
            'name' => 'Indigo Purple',
            'preview' => '#6366f1',
            'shades' => [
                50 => '#eef2ff', 100 => '#e0e7ff', 200 => '#c7d2fe', 300 => '#a5b4fc', 400 => '#818cf8',
                500 => '#6366f1', 600 => '#4f46e5', 700 => '#4338ca', 800 => '#3730a3', 900 => '#312e81', 950 => '#1e1b4b',
            ],
        ],
        'violet' => [
            'name' => 'Deep Violet',
            'preview' => '#8b5cf6',
            'shades' => [
                50 => '#f5f3ff', 100 => '#ede9fe', 200 => '#ddd6fe', 300 => '#c4b5fd', 400 => '#a78bfa',
                500 => '#8b5cf6', 600 => '#7c3aed', 700 => '#6d28d9', 800 => '#5b21b6', 900 => '#4c1d95', 950 => '#2e1065',
            ],
        ],
        'rose' => [
            'name' => 'Rose Crimson',
            'preview' => '#f43f5e',
            'shades' => [
                50 => '#fff1f2', 100 => '#ffe4e6', 200 => '#fecdd3', 300 => '#fda4af', 400 => '#fb7185',
                500 => '#f43f5e', 600 => '#e11d48', 700 => '#be123c', 800 => '#9f1239', 900 => '#881337', 950 => '#4c0519',
            ],
        ],
        'amber' => [
            'name' => 'Amber Gold',
            'preview' => '#f59e0b',
            'shades' => [
                50 => '#fffbeb', 100 => '#fef3c7', 200 => '#fde68a', 300 => '#fcd34d', 400 => '#fbbf24',
                500 => '#f59e0b', 600 => '#d97706', 700 => '#b45309', 800 => '#92400e', 900 => '#78350f', 950 => '#451a03',
            ],
        ],
        'slate' => [
            'name' => 'Slate Monochrome',
            'preview' => '#64748b',
            'shades' => [
                50 => '#f8fafc', 100 => '#f1f5f9', 200 => '#e2e8f0', 300 => '#cbd5e1', 400 => '#94a3b8',
                500 => '#64748b', 600 => '#475569', 700 => '#334155', 800 => '#1e293b', 900 => '#0f172a', 950 => '#020617',
            ],
        ],
    ];

    /**
     * Available sidebar theme options for the admin dashboard.
     *
     * @var array<string, array{name: string, description: string, preview_bg: string, preview_border: string, preview_text: string}>
     */
    public const SIDEBAR_THEMES = [
        'brand_dark' => [
            'name' => 'Dark Accent',
            'description' => 'Sidebar menyatu dengan warna aksen dalam nuansa gelap pekat.',
            'preview_bg' => 'bg-brand-950',
            'preview_border' => 'border-brand-900/60',
            'preview_text' => 'text-brand-100',
        ],
        'dark' => [
            'name' => 'Dark Slate',
            'description' => 'Sidebar hitam slate klasik dengan kontras tegas dan elegan.',
            'preview_bg' => 'bg-slate-900',
            'preview_border' => 'border-slate-800',
            'preview_text' => 'text-slate-200',
        ],
        'white' => [
            'name' => 'Clean White',
            'description' => 'Sidebar putih bersih bernuansa modern minimalis dan rapi.',
            'preview_bg' => 'bg-white',
            'preview_border' => 'border-slate-200',
            'preview_text' => 'text-slate-700',
        ],
    ];

    /**
     * Get shade hex values for a given accent key.
     *
     * @return array<int, string>
     */
    public static function getAccentShades(?string $accent = null): array
    {
        $accent = $accent ?: Setting::get('admin_accent_color', 'emerald');

        return self::ACCENT_PALETTES[$accent]['shades'] ?? self::ACCENT_PALETTES['emerald']['shades'];
    }

    /**
     * Definitions for all editable settings.
     * Used by the seeder and the settings form to render fields correctly.
     *
     * @return list<array{key: string, group: string, type: string, label: string, default: string}>
     */
    public function getSettingDefinitions(): array
    {
        return [
            // Identity
            ['key' => 'site_name', 'group' => 'identity', 'type' => 'text', 'label' => 'Website Name', 'default' => 'CMS'],
            ['key' => 'site_description', 'group' => 'identity', 'type' => 'textarea', 'label' => 'Description', 'default' => ''],
            ['key' => 'site_tagline', 'group' => 'identity', 'type' => 'text', 'label' => 'Tagline', 'default' => ''],
            ['key' => 'site_logo', 'group' => 'identity', 'type' => 'file', 'label' => 'Logo', 'default' => ''],
            ['key' => 'site_favicon', 'group' => 'identity', 'type' => 'file', 'label' => 'Favicon', 'default' => ''],

            // Contact
            ['key' => 'contact_address', 'group' => 'contact', 'type' => 'textarea', 'label' => 'Address', 'default' => ''],
            ['key' => 'contact_city', 'group' => 'contact', 'type' => 'text', 'label' => 'City', 'default' => ''],
            ['key' => 'contact_phone', 'group' => 'contact', 'type' => 'text', 'label' => 'Phone', 'default' => ''],
            ['key' => 'contact_whatsapp', 'group' => 'contact', 'type' => 'text', 'label' => 'WhatsApp', 'default' => ''],
            ['key' => 'contact_email', 'group' => 'contact', 'type' => 'email', 'label' => 'Email', 'default' => ''],
            ['key' => 'contact_hours', 'group' => 'contact', 'type' => 'text', 'label' => 'Operating Hours', 'default' => ''],

            // Social Media
            ['key' => 'social_facebook', 'group' => 'social', 'type' => 'url', 'label' => 'Facebook URL', 'default' => ''],
            ['key' => 'social_instagram', 'group' => 'social', 'type' => 'url', 'label' => 'Instagram URL', 'default' => ''],
            ['key' => 'social_linkedin', 'group' => 'social', 'type' => 'url', 'label' => 'LinkedIn URL', 'default' => ''],
            ['key' => 'social_youtube', 'group' => 'social', 'type' => 'url', 'label' => 'YouTube URL', 'default' => ''],

            // SEO
            ['key' => 'seo_meta_title', 'group' => 'seo', 'type' => 'text', 'label' => 'Global Meta Title', 'default' => ''],
            ['key' => 'seo_meta_description', 'group' => 'seo', 'type' => 'textarea', 'label' => 'Global Meta Description', 'default' => ''],
            ['key' => 'seo_meta_keywords', 'group' => 'seo', 'type' => 'text', 'label' => 'Global Meta Keywords', 'default' => ''],
            ['key' => 'og_image', 'group' => 'seo', 'type' => 'file', 'label' => 'Default OG / Share Image', 'default' => ''],

            // Appearance (Dashboard Theme)
            ['key' => 'admin_accent_color', 'group' => 'appearance', 'type' => 'select', 'label' => 'Admin Accent Color', 'default' => 'emerald'],
            ['key' => 'admin_sidebar_theme', 'group' => 'appearance', 'type' => 'select', 'label' => 'Sidebar Background Theme', 'default' => 'brand_dark'],
        ];
    }

    /**
     * Bulk update settings from a key-value array (form POST data).
     *
     * @param  array<string, string>  $values
     */
    public function saveSettings(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::set($key, $value);
        }

        Cache::forget('cms_settings');
    }

    /**
     * Return all settings as a flat key-value array (cached).
     *
     * @return array<string, string|null>
     */
    public function getAllSettings(): array
    {
        return Cache::rememberForever('cms_settings', function () {
            return Setting::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Return all settings grouped by their group for form rendering.
     *
     * @return array<string, list<Setting>>
     */
    public function getSettingsForForm(): array
    {
        $existingValues = $this->getAllSettings();
        $grouped = [];

        foreach ($this->getSettingDefinitions() as $definition) {
            $definition['value'] = $existingValues[$definition['key']] ?? $definition['default'];
            $grouped[$definition['group']][] = $definition;
        }

        return $grouped;
    }
}
