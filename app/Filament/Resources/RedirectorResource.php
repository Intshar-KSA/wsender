<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;

class RedirectorResource extends Resource
{
    protected static ?string $model = null; // لا يوجد موديل فعلي

    // protected static ?string $navigationLabel = 'وثائق API';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'الدعم';

    protected static ?int $navigationSort = 100;

    public static function getModelLabel(): string
    {
        return _('API Documentation');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ApiDocsResource\Pages\RedirectToApiDocs::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
