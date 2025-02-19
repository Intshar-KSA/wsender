<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\Column;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure TextInput globally
        TextInput::configureUsing(function (TextInput $textInput): void {
            $textInput->translateLabel(); // Apply translation to all TextInput labels
        });

        // Configure Select globally
        Select::configureUsing(function (Select $select): void {
            $select->translateLabel();
        });

        // Configure Textarea globally
        Textarea::configureUsing(function (Textarea $textarea): void {
            $textarea->translateLabel();
        });

        // Configure Checkbox globally
        Checkbox::configureUsing(function (Checkbox $checkbox): void {
            $checkbox->translateLabel();
        });

        // Configure Radio globally
        Radio::configureUsing(function (Radio $radio): void {
            $radio->translateLabel();
        });

        // Configure DatePicker globally
        DatePicker::configureUsing(function (DatePicker $datePicker): void {
            $datePicker->translateLabel();
        });

        // Configure DateTimePicker globally
        DateTimePicker::configureUsing(function (DateTimePicker $dateTimePicker): void {
            $dateTimePicker->translateLabel();
        });

        // Configure TimePicker globally
        TimePicker::configureUsing(function (TimePicker $timePicker): void {
            $timePicker->translateLabel();
        });

        // Configure FileUpload globally
        FileUpload::configureUsing(function (FileUpload $fileUpload): void {
            $fileUpload->translateLabel();
        });

        // Configure RichEditor globally
        RichEditor::configureUsing(function (RichEditor $richEditor): void {
            $richEditor->translateLabel();
        });

        // Configure Toggle globally
        Toggle::configureUsing(function (Toggle $toggle): void {
            $toggle->translateLabel();
        });

        // Configure ColorPicker globally
        ColorPicker::configureUsing(function (ColorPicker $colorPicker): void {
            $colorPicker->translateLabel();
        });

        // Configure Table Columns globally
        Column::configureUsing(function (Column $column): void {
            $column->translateLabel();
        });

        // Configure Section globally
        Section::configureUsing(function (Section $section): void {
            $section->translateLabel(); // Translate the section label
        });

        Action::configureUsing(function (Action $action): void {
            $action->translateLabel();  // Apply translation
        });

        // Configure LanguageSwitch
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales(['ar', 'en']); // Set supported locales
        });
    }
}
