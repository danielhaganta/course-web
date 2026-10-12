<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Services\Midtrans;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('code')
                            ->label('Code')
                            ->copyable(),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        TextEntry::make('user.name')
                            ->label('User')
                            ->helperText(fn (Order $record): string => $record->user->email),
                        TextEntry::make('item')
                            ->label('Item')
                            ->state(fn (Order $record): string => $record->itemName())
                            ->helperText(fn (Order $record): string => $record->purchasable_type === 'plan' ? 'Plan' : 'Course'),
                        TextEntry::make('amount')
                            ->label('Amount')
                            ->money('IDR', locale: 'id', decimalPlaces: 0),
                        TextEntry::make('payment_method')
                            ->label('Payment method')
                            ->state(fn (Order $record): ?string => app(Midtrans::class)->paymentMethodLabel($record))
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('expires_at')
                            ->label('Payment deadline')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('paid_at')
                            ->label('Paid')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                    ]),
                Section::make('Payment gateway data')
                    ->description('Latest notification or status check result.')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('gateway_payload')
                            ->hiddenLabel()
                            ->state(fn (Order $record): ?HtmlString => $record->gateway_payload
                                ? new HtmlString('<pre style="white-space: pre; overflow-x: auto; font-size: 0.75rem;">'
                                    .e((string) json_encode($record->gateway_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
                                    .'</pre>')
                                : null)
                            ->placeholder('No data from the payment gateway yet.'),
                    ]),
            ]);
    }
}
