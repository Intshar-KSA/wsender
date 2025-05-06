<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Support\Facades\Redirect;

class RedirectorResource extends Resource
{
    protected static ?string $model = null; // لا يوجد موديل فعلي

    protected static ?string $navigationLabel = 'وثائق API';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'الدعم';

    protected static ?int $navigationSort = 100;

    public static function getPages(): array
    {
        return [
            'index' => fn () => Redirect::to('https://documenter.getpostman.com/view/17450141/2sAYdhKqnx'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
