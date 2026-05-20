<?php

use Illuminate\Support\Facades\Route;
use Meta\AdminCore\Models\PageBlock;

/**
 * Public-side routes.
 *
 * Admin routes (`/admin/*`) come from meta/admin-core's package routes —
 * no need to register them here.
 *
 * Add your own pages below the home route.
 */

Route::get('/', function () {
    $blocks = PageBlock::where('page_name', 'home')
        ->where('status', 'published')
        ->orderBy('sort_order')
        ->with('translations')
        ->get();

    return view('home', ['blocks' => $blocks]);
})->name('home');
