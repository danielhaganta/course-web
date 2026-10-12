<?php

namespace App\Filament\Resources\Plans\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id', decimalPlaces: 0),
                TextColumn::make('duration_days')
                    ->label('Durasi')
                    ->suffix(' hari'),
                IconColumn::make('can_access_exclusive')
                    ->label('Exclusive')
                    ->boolean(),
                IconColumn::make('can_download')
                    ->label('Download')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            // Tanpa delete: plan yang punya langganan tidak bisa dihapus, nonaktifkan saja
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
