<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use App\Services\CampaignService;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;

class CampaignResults extends Page
{
    use WithPagination;

    protected static string $resource = CampaignResource::class;

    protected static string $view = 'filament.resources.campaign-resource.pages.campaign-results';

    public array $results = [];

    public $record;

    public int $offset = 0;

    public int $limit = 100;

    public function mount($record): void
    {
        $this->record = \App\Models\Campaign::findOrFail($record);
        $this->loadResults();
    }

    public function loadResults(): void
    {
        $newResults = CampaignService::getCampaignResults($this->record, $this->offset, $this->limit);
        $this->results = array_merge($this->results, $newResults);
    }

    public function loadMore(): void
    {
        $this->offset += $this->limit;
        $this->loadResults();
    }

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'user']);
    }
}
