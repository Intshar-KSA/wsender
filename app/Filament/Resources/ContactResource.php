<?php

namespace App\Filament\Resources;

use App\Models\Contact;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\ContactResource\Pages;
use Illuminate\Database\Eloquent\Builder;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Contact Name'),
                Forms\Components\TextInput::make('phone')
                    ->required()
                    ->label('Phone Number'),
                // Forms\Components\Select::make('user_id')
                //     ->relationship('user', 'name')
                //     ->searchable()
                //     ->required()
                //     ->label('User'),
                Forms\Components\Hidden::make('user_id')
                ->default(auth()->id())
                ->required(),
            
            
                Forms\Components\Select::make('contact_cat_id')
                ->relationship('contactCat', 'name', fn (Builder $query) => $query->where('user_id', auth()->id())) // تصفية الفئات بناءً على المستخدم الحالي
                ->searchable()
                ->required()
                ->preload()
                ->label('Category'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Contact Name'),
                Tables\Columns\TextColumn::make('phone')->label('Phone'),
                Tables\Columns\TextColumn::make('user.name')->label('User'),
                Tables\Columns\TextColumn::make('contactCat.name')->label('Category'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Created At'),
            ])
            ->filters([
                // Tables\Filters\SelectFilter::make('contact_cat_id')
                // ->label('Category')
                // ->options(fn () => \App\Models\ContactCat::pluck('name', 'id')->toArray())
                // ->query(function ($query, $state) {
                //     return $query->where('contact_cat_id', $state);
                // }),
                // Tables\Filters\Filter::make('By User')
                //     ->form([
                //         Forms\Components\Select::make('user_id')
                //             ->relationship('user', 'name')
                //             ->searchable()
                //             ->label('User'),
                //     ])
                //     ->query(function ($query, array $data) {
                //         return $query->where('user_id', $data['user_id']);
                //     }),
                // Tables\Filters\Filter::make('By Category')
                //     ->form([
                //         Forms\Components\Select::make('contact_cat_id')
                //             ->relationship('contactCat', 'name')
                //             ->searchable()
                //             ->label('Category'),
                //     ])
                //     ->query(function ($query, array $data) {
                //         return $query->where('contact_cat_id', $data['contact_cat_id']);
                //     }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
{
    return parent::getEloquentQuery()
        ->where('user_id', auth()->id()) // تصفية السجلات الخاصة بالمستخدم الحالي
        ->with(['user', 'contactCat']); // تحميل العلاقات لتجنب مشاكل N+1
}

}