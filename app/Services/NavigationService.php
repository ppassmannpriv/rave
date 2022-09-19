<?php

namespace App\Services;

use App\Contracts\Navigation;
use App\Models\ContentPage;
use Illuminate\Database\Eloquent\Collection;

class NavigationService implements Navigation {
    public function getPages(): Collection
    {
        return ContentPage::all();
    }

    public function isActive(): bool
    {
        return request()->routeIs(request()->route()->getName());
    }
}
