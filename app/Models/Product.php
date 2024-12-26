<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $price
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public const string EVENT_TICKET_PRODUCT_TYPE = 'event_ticket';
    public const string PHYSICAL_PRODUCT_TYPE = 'physical';
    public const string VIRTUAL_PRODUCT_TYPE = 'virtual';
    public const string SHIPPING_PRODUCT_TYPE = 'shipping';
    public const string TRANSACTION_FEE_TYPE = 'transaction_fee';
    /**
     * @var string[] PRODUCT_TYPES
     */
    public const array PRODUCT_TYPES = [
        self::EVENT_TICKET_PRODUCT_TYPE,
        self::PHYSICAL_PRODUCT_TYPE,
        self::VIRTUAL_PRODUCT_TYPE,
        self::SHIPPING_PRODUCT_TYPE,
        self::TRANSACTION_FEE_TYPE
    ];
    public const string DEFAULT_PRODUCT_TYPE = self::EVENT_TICKET_PRODUCT_TYPE;

    public function isAvailable(): bool
    {
        // @TODO: Stock logic is for another day
        return true;
    }

}
