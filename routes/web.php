<?php

use Illuminate\Support\Facades\Route;
use Statamic\Facades\Site;

Route::get('/api/sites', function () {
    $sites = collect(Site::all())->map(function ($site) {
        return [
	    'handle' => $site->handle(),
            'name' => $site->name(),
            'locale' => $site->locale(),
            'url' => $site->url(),
            'short_locale' => str_replace('_', '-', $site->locale()), // e.g. "en_US" → "en-US"
        ];
    });

    return response()->json([
        'data' => $sites->values(),
        'count' => $sites->count(),
    ]);
});

