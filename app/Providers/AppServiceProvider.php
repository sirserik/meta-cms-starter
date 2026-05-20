<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Meta\AdminCore\Facades\AdminCore;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Enable the full built-in block library shipped with meta/admin-core.
        // Drop handles you don't need, or list explicitly:
        //   AdminCore::useBlocks(['hero', 'content', 'cta', 'features']);
        if (class_exists(AdminCore::class)) {
            AdminCore::useBlocks('*');
        }
    }
}
