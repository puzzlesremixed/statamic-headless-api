<?php

use Illuminate\Support\Facades\Route;
use Statamic\Facades\Site;
use Statamic\Facades\Collection;

Route::prefix('api')->group(function () {
    Route::get("/sites", function () {
        $sites = collect(Site::all())->map(function ($site) {
            return [
                "handle" => $site->handle(),
                "name" => $site->name(),
                "locale" => $site->locale(),
                "url" => $site->url(),
                "short_locale" => str_replace("_", "-", $site->locale()), // e.g. "en_US" → "en-US"
            ];
        });

        return response()->json([
            "data" => $sites->values(),
            "count" => $sites->count(),
        ]);
    });

    Route::get('/sitemap-data', function () {
        $collections = Collection::all()->mapWithKeys(function ($collection) {
            $entries = $collection->queryEntries()->get();

            return [
                $collection->handle() => $entries->map(function ($entry) {
                    return [
                        'id' => $entry->id(),
                        'locale' => $entry->locale(),
                        'slug' => $entry->slug(),
                        'collection' => $entry->collection()->handle(),
                        'updated_at' => $entry->lastModified(),
                        'date' => $entry->date(),
                        'url' => $entry->url(),
                        'order' => $entry->order(),

                        'parent' => [
                            'parent_id' => $entry->parent()?->id,
                            'parent_url' => $entry->parent()?->url
                        ],

                        'origin' => [
                            'origin_locale' => $entry->origin()?->locale ?? $entry->locale(),
                            'origin_id' => $entry->origin()?->id ?? $entry->id(),
                            'origin_url' => $entry->origin()?->url ?? $entry->url(),
                        ],

                        'seo' => [

                            'robots' => $entry->robots ? array_map(function ($item) {
                                return $item['value'];
                            }, $entry->robots) : null,

                            'sitemap' => [
                                'priority' => $entry->sitemap_priority,
                                'include' => $entry->sitemap_include,
                            ]
                        ]
                    ];
                }),
            ];
        });

        return response()->json([
            'collections' => $collections,
            "count" => $collections->count(),
        ]);
    });
});
