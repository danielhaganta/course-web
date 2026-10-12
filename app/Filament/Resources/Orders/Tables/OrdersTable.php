<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Midtrans;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('user.email')
                    ->label('User')
                    ->description(fn (Order $record): string => $record->user->name)
                    ->searchable(),
                TextColumn::make('item')
                    ->label('Item')
                    ->state(fn (Order $record): string => $record->itemName())
                    ->description(fn (Order $record): string => $record->purchasable_type === 'plan' ? 'Plan' : 'Course'),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR', locale: 'id', decimalPlaces: 0),
                TextColumn::make('payment_method')
                    ->label('Method')
                    ->state(fn (Order $record): ?string => app(Midtrans::class)->paymentMethodLabel($record))
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('paid_at')
                    ->label('Paid')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(OrderStatus::class),
                SelectFilter::make('purchasable_type')
                    ->label('Item type')
                    ->options(['plan' => 'Plan', 'course' => 'Course']),
                Filter::make('created_at')
                    ->label('Order date')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    // Tanggal dipilih dalam WIB, kolom disimpan dalam UTC
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date) => $query
                            ->where('created_at', '>=', Carbon::parse($date, 'Asia/Jakarta')->startOfDay()->utc()))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date) => $query
                            ->where('created_at', '<=', Carbon::parse($date, 'Asia/Jakarta')->endOfDay()->utc()))),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
