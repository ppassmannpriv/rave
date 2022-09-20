<?php

namespace App\Services;

use App\Contracts\Navigation;
use App\Models\ContentPage;
use Illuminate\Database\Eloquent\Collection;

class NavigationService implements Navigation {
    public function getPages(): Collection
    {
        return ContentPage::where('enabled', '=', true)->get();
    }

    public function isActive(string $routeName): bool
    {
        return request()->routeIs($routeName);
    }
}
