<?php

namespace App\Models;

use App\Models\TimeSchedule\Shift;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TimeSchedule
 *
 * @property int $id
 * @property bool $active
 * @property ?string $description
 * @property ?int $event_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $start
 * @property \Illuminate\Support\Carbon|null $end
 * @property-read ?\App\Models\Event $event
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TimeSchedule\Shift[] $shifts
 * @property-read int|null $shifts_count
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSchedule whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSchedule whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSchedule whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSchedule whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSchedule whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSchedule whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSchedule whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TimeSchedule extends Model {
    public $table = 'time_schedules';
    public $primaryKey = 'id';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'start',
        'end'
    ];

    protected $fillable = [
        'start',
        'end',
        'event_id',
        'description',
        'active',
        'created_at',
        'updated_at',
    ];

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function shifts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Shift::class, 'time_schedule_id', 'id');
    }
}
