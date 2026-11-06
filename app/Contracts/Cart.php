<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface Cart
{
    public function getTickets(): Collection;

    public function content();

    public function clear(): void;
}
