<?php

namespace App\Providers;

use App\Models\DocumentRequest;
use App\Models\RequestDocument;
use App\Observers\DocumentRequestObserver;
use App\Observers\RequestDocumentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register DocumentRequest observer untuk auto-create reminder
        DocumentRequest::observe(DocumentRequestObserver::class);
        
        // Register RequestDocument observer untuk auto-create PSW/PS forms
        RequestDocument::observe(RequestDocumentObserver::class);
    }
}
