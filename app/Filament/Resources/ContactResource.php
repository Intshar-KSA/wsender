<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Contact;
use Filament\Forms\Form;
use App\Models\ContactCat;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ContactResource\Pages;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->default('Unknown')
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(), // السماح بالبحث في الاسم

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone Number')
                    ->sortable()
                    ->searchable(), // السماح بالبحث في رقم الهاتف

                Tables\Columns\TextColumn::make('contactCat.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(), // السماح بالبحث في التصنيف

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(), // السماح بترتيب تاريخ الإنشاء
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('contact_cat_id')
                    ->label('Filter by Category')

                    ->options(ContactCat::pluck('name', 'id')->toArray()), // إضافة فلتر حسب التصنيف

                Tables\Filters\TrashedFilter::make(), // دعم الفلترة للعناصر المحذوفة إذا كنت تستخدم Soft Deletes
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // زر التعديل
                Tables\Actions\DeleteAction::make(), // زر الحذف
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(), // الحذف الجماعي
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
            'bulk-create' => Pages\BulkCreateContacts::route('/bulk-create'),

        ];
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
{
    return parent::getEloquentQuery()
        ->where('user_id', auth()->id()) // تصفية السجلات الخاصة بالمستخدم الحالي
        ->with(['user', 'contactCat']); // تحميل العلاقات لتجنب مشاكل N+1
}

}
