<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentMethod\PayPalFriendsFamily;

class PaymentMethod extends Model
{
    use HasFactory;

    public const ALIAS = null;
    public const CLASSMAP = [
        PayPalFriendsFamily::ALIAS => PayPalFriendsFamily::class,
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
