<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Yatmo\Laravel\Facades\Yatmo;

// Demo listings; a real app reads them from its database.
$listings = [
    'rue-de-la-loi-16' => (object) ['slug' => 'rue-de-la-loi-16', 'title' => 'Bright 2-bedroom apartment, Rue de la Loi', 'price' => 395000, 'latitude' => 50.8461, 'longitude' => 4.3664, 'discreet' => false,
        'description' => '92 m² on the third floor of a 1930s building, south-facing living room, fitted kitchen, cellar. Energy class C.'],
    'zuid-antwerp-loft' => (object) ['slug' => 'zuid-antwerp-loft', 'title' => 'Loft in Antwerp Zuid', 'price' => 549000, 'latitude' => 51.2093, 'longitude' => 4.3936, 'discreet' => true,
        'description' => '140 m² loft in a converted warehouse, 4.2 m ceilings, roof terrace. Address on request.'],
];

Route::get('/', fn () => view('home', ['listings' => $listings]));

Route::get('/listings/{slug}', function (string $slug) use ($listings) {
    $listing = $listings[$slug] ?? abort(404);
    $paragraphs = Yatmo::text($listing->latitude, $listing->longitude)->paragraphs;
    $description = Str::limit($paragraphs[0]->text() ?? '', 155);

    return view('listing', ['listing' => $listing, 'metaDescription' => $description]);
});
