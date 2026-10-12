<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $code
 * @property int $user_id
 * @property string $purchasable_type
 * @property int $purchasable_id
 * @property-read Plan|Course $purchasable
 * @property int $amount
 * @property OrderStatus $status
 * @property string|null $payment_type
 * @property string|null $snap_token
 * @property array<string, mixed>|null $gateway_payload
 * @property Carbon|null $paid_at
 * @property Carbon $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'user_id', 'purchasable_type', 'purchasable_id', 'amount', 'status', 'payment_type', 'snap_token', 'gateway_payload', 'paid_at', 'expires_at'])]
#[RouteKey('code')]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    // Contoh: ORD-20261012-K3F9QZ
    public static function generateCode(): string
    {
        return 'ORD-'.now('Asia/Jakarta')->format('Ymd').'-'.Str::upper(Str::random(6));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPayable(): bool
    {
        return $this->status === OrderStatus::Pending && $this->expires_at->isFuture();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => OrderStatus::class,
            'gateway_payload' => 'array',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
