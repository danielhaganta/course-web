<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Str;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state, string $operation): void {
                        // Jangan timpa slug yang sudah diubah manual
                        if ($operation !== 'create' || ($get('slug') ?? '') !== Str::slug($old ?? '')) {
                            return;
                        }

                        $set('slug', Str::slug($state ?? ''));
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->alphaDash()
                    ->unique(),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Price')
                    ->prefix('Rp')
                    ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                    ->stripCharacters('.')
                    ->integer()
                    ->minValue(0)
                    ->required(),
                TextInput::make('duration_days')
                    ->label('Duration')
                    ->suffix('days')
                    ->integer()
                    ->minValue(1)
                    ->required(),
                Toggle::make('can_access_exclusive')
                    ->label('Exclusive lessons'),
                Toggle::make('can_download')
                    ->label('Practice file downloads'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->helperText('Turning this off only hides the plan from the pricing page. Existing subscriptions stay valid.')
                    ->default(true),
            ]);
    }
}
