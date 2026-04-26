<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use Illuminate\View\View;

class ContentPageController extends Controller
{
    public function show(ContentPage $contentPage): View
    {
        abort_unless($contentPage->is_published, 404);
        abort_if($contentPage->published_at !== null && $contentPage->published_at->isFuture(), 404);

        return view('content-page.show', [
            'page' => $contentPage,
        ]);
    }
}
