<?php

namespace App\Http\Controllers\Site;

use App\Models\ContentPage;
use Illuminate\Http\Request;
use App\Contracts\Navigation;

class SiteController extends WebController
{
    public function index(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $contentPage = ContentPage::findOrFail(1);
        return $this->respond('site.index', ['contentPage' => $contentPage]);
    }
}
