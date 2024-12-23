<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface Navigation {
    public function getPages(): Collection;
}
