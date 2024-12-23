<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentMethod\PayPalFriendsFamily;
use App\Models\PaymentMethod\PayPalExpress;

/**
 * App\Models\PaymentMethod
 *
 * @property int $id
 * @property string $alias
 * @property string $name
 * @property string $FQN
 * @property string|null $description
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereFQN($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentMethod extends Model
{
    use HasFactory;

    public const ALIAS = null;
    public const CLASSMAP = [
        PayPalFriendsFamily::ALIAS => PayPalFriendsFamily::class,
        PayPalExpress::ALIAS => PayPalExpress::class,
    ];

    public $table = 'payment_methods';
    public $primaryKey = 'id';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'name',
        'alias',
        'active',
        'FQN',
        'description',
        'created_at',
        'updated_at',
    ];

    public function model()
    {
        return self::CLASSMAP[$this->alias]::find($this->id);
    }
}
