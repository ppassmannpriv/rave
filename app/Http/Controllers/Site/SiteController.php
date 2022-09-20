<?php

namespace App\Http\Controllers\Site;

use App\Models\ContentPage;
use Illuminate\Http\Request;

class SiteController extends WebController
{
    public function index(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $contentPage = ContentPage::findOrFail(1);
        return $this->respond('site.index', ['contentPage' => $contentPage]);
    }

    public function page(Request $request)
    {
        $pathInfo = \Str::replaceFirst('/', '', $request->getPathInfo());
        $contentPage = ContentPage::where('path', '=', $pathInfo)->first();
        return $this->respond('site.index', ['contentPage' => $contentPage]);
    }
}
