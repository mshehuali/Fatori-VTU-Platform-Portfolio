<?php
namespace App\Services;

use App\Models\Setting;

class SettingsService {
    public function getTheme(): string {
        return Setting::get('app_theme', 'light');
    }

    public function setTheme(string $theme): void {
        Setting::set('app_theme', $theme);
    }

    public function toggleTheme(): string {
        $current = $this->getTheme();
        $new = $current === 'dark' ? 'light' : 'dark';
        $this->setTheme($new);
        return $new;
    }
}
