<?php

namespace App\Models;

use App\Models\PaymentMethod\PayPal;
use App\Models\PaymentMethod\PayPalFriendsFamily;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    public const ALIAS = null;

    public const CLASSMAP = [
        PayPalFriendsFamily::ALIAS => PayPalFriendsFamily::class,
        PayPal::ALIAS => PayPal::class,
    ];

    public $table = 'payment_methods';

    public $primaryKey = 'id';

    protected $casts = [
        'active' => 'boolean',
        'admin_only' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'name',
        'alias',
        'active',
        'admin_only',
        'FQN',
        'description',
        'created_at',
        'updated_at',
    ];

    public function scopeSelectableBy($query, ?User $user)
    {
        if ($user !== null && $user->is_admin) {
            return $query->where(fn ($q) => $q->where('active', true)->orWhere('admin_only', true));
        }

        return $query->where('active', true)->where('admin_only', false);
    }

    public function model()
    {
        return self::CLASSMAP[$this->alias]::find($this->id);
    }
}
