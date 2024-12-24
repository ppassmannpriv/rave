<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

}
