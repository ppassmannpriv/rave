<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\ContentPage
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $page_text
 * @property string|null $excerpt
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContentCategory[] $categories
 * @property-read int|null $categories_count
 * @property-read mixed $featured_image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|Media[] $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContentTag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage newQuery()
 * @method static \Illuminate\Database\Query\Builder|ContentPage onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage wherePageText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|ContentPage withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ContentPage withoutTrashed()
 * @mixin \Eloquent
 */
class ContentPage extends Model implements HasMedia
{
    use SoftDeletes;
    use InteractsWithMedia;
    use HasFactory;

    public $table = 'content_pages';

    protected $appends = [
        'featured_image',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'title',
        'page_text',
        'excerpt',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 50, 50);
        $this->addMediaConversion('preview')->fit('crop', 120, 120);
    }

    public function categories()
    {
        return $this->belongsToMany(ContentCategory::class);
    }

    public function tags()
    {
        return $this->belongsToMany(ContentTag::class);
    }

    public function getFeaturedImageAttribute()
    {
        $file = $this->getMedia('featured_image')->last();
        if ($file) {
            $file->url       = $file->getUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}