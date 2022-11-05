<?php

namespace App\Models\TimeSchedule;

use App\Models\TimeSchedule;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TimeSchedule\Shift
 *
 * @property int $id
 * @property bool $crew_only
 * @property string $name
 * @property ?string $description
 * @property int $time_schedule_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $start
 * @property \Illuminate\Support\Carbon|null $end
 * @property-read \App\Models\TimeSchedule $timeSchedule
 * @mixin \Eloquent
 */
class Shift extends Model {
    public $table = 'time_schedule_shifts';
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
        'time_schedule_id',
        'description',
        'crew_only',
        'created_at',
        'updated_at',
    ];

    public function timeSchedule(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TimeSchedule::class, 'event_id', 'id');
    }

    public function isCrewOnly(): bool
    {
        return $this->crew_only;
    }

    public function isHelper(): bool
    {
        return !$this->isCrewOnly();
    }
}
