<?php

namespace App\Http\Controllers\Site;

use App\Models\ContentPage;
use Illuminate\Http\Request;

class SiteController extends WebController
{
    public function index(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $contentPage = $this->navigationService->getIndexPage();
        return $this->respond('site.index', ['contentPage' => $contentPage, 'siteType' => 'index']);
    }

    public function page(Request $request)
    {
        $pathInfo = \Str::replaceFirst('/', '', $request->getPathInfo());
        $contentPage = ContentPage::where('path', '=', $pathInfo)->first();
        return $this->respond('site.index', ['contentPage' => $contentPage, 'siteType' => 'cms']);
    }

    public function error(Request $request)
    {
        return $this->respond('site.error', ['error' => $request->get('error'), 'siteType' => 'error']);
    }

    public function noIndex(Request $request)
    {
        return $this->respond('site.noIndex', ['siteType' => 'index']);
    }
}
