<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Merchandise extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const MERCHANDISE_TYPE_RADIO = [
        'tshirt' => 'T-Shirt',
        'totebag' => 'Tote Bag',
        'keychain' => 'Keychain',
    ];

    public $table = 'merchandises';
    public $primaryKey = 'id';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'type',
        'name',
        'short_description',
        'description',
        'price',
        'active',
        'stock',
        'created_at',
        'updated_at',
    ];

    public function getType()
    {
        return self::MERCHANDISE_TYPE_RADIO[$this->type];
    }

    public function isAvailable(): bool
    {
        return $this->stock > 0 && $this->active;
    }
}
