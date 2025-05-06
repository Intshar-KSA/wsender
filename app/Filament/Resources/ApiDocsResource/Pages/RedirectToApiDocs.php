<?php

namespace App\Filament\Resources\ApiDocsResource\Pages;

use Filament\Resources\Pages\Page;

class RedirectToApiDocs extends Page
{
    protected static string $resource = \App\Filament\Resources\RedirectorResource::class;

    protected static string $view = 'filament.resources.api-docs.pages.redirect-to-api-docs';

    public function mount(): void
    {
        redirect('https://documenter.getpostman.com/view/17450141/2sAYdhKqnx');
    }
}
