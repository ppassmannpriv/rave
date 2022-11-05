<?php

namespace App\Models\TimeSchedule;

use App\Models\TimeSchedule;
use Carbon\Carbon;
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
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

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
        'name',
        'time_schedule_id',
        'description',
        'crew_only',
        'created_at',
        'updated_at',
        'repeat',
    ];

    public function timeSchedule(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TimeSchedule::class, 'time_schedule_id', 'id');
    }

    public function isCrewOnly(): bool
    {
        return $this->crew_only;
    }

    public function isHelper(): bool
    {
        return !$this->isCrewOnly();
    }

    /**
     * @param array $data
     * @return static[]
     */
    public static function createRepeating(array $data): array
    {
        $repeatCounter = $data['repeat'];
        unset($data['repeat']);
        $iter = 0;
        $models = [static::create($data)];
        for ($i = 1; $i < $repeatCounter; $i++) {
            $iterationData = $data;

            $iterationStart = Carbon::createFromFormat(static::DATETIME_FORMAT, $data['start']);
            $iterationEnd = Carbon::createFromFormat(static::DATETIME_FORMAT, $data['end']);

            $duration = $iterationStart->diff($iterationEnd);

            $iterationData['start'] = $iterationStart->addHours($duration->h * $i)->format(static::DATETIME_FORMAT);
            $iterationData['end'] = $iterationEnd->addHours($duration->h * $i)->format(static::DATETIME_FORMAT);

            $models[] = static::create($iterationData);
        }

        return $models;
    }
}
