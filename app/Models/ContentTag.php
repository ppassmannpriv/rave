<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\ContentTag
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|ContentTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentTag newQuery()
 * @method static \Illuminate\Database\Query\Builder|ContentTag onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentTag whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentTag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentTag whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentTag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|ContentTag withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ContentTag withoutTrashed()
 * @mixin \Eloquent
 */
class ContentTag extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'content_tags';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'slug',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}