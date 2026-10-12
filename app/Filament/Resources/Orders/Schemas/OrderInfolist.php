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
                            ->label('Kode')
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
                            ->helperText(fn (Order $record): string => $record->purchasable_type === 'plan' ? 'Paket' : 'Course'),
                        TextEntry::make('amount')
                            ->label('Nominal')
                            ->money('IDR', locale: 'id', decimalPlaces: 0),
                        TextEntry::make('payment_method')
                            ->label('Metode pembayaran')
                            ->state(fn (Order $record): ?string => app(Midtrans::class)->paymentMethodLabel($record))
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('expires_at')
                            ->label('Batas pembayaran')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('paid_at')
                            ->label('Dibayar')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                    ]),
                Section::make('Data dari payment gateway')
                    ->description('Isi notifikasi atau hasil cek status terakhir.')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('gateway_payload')
                            ->hiddenLabel()
                            ->state(fn (Order $record): ?HtmlString => $record->gateway_payload
                                ? new HtmlString('<pre style="white-space: pre; overflow-x: auto; font-size: 0.75rem;">'
                                    .e((string) json_encode($record->gateway_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
                                    .'</pre>')
                                : null)
                            ->placeholder('Belum ada data dari payment gateway.'),
                    ]),
            ]);
    }
}
