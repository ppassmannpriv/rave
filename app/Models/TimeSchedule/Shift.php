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
     * @return array
     * @throws \Throwable
     */
    public static function createRepeating(array $data): array
    {
        $errorBag = [];
        $models = [];
        $repeatCounter = $data['repeat'];
        unset($data['repeat']);

        $iterationStart = Carbon::createFromFormat(static::DATETIME_FORMAT, $data['start']);
        $iterationEnd = Carbon::createFromFormat(static::DATETIME_FORMAT, $data['end']);
        $duration = $iterationStart->diff($iterationEnd);

        if ($duration->s < 1) {
            throw new \InvalidArgumentException('Duration is below 1 second!');
        }
        try {
            for ($i = 0; $i < $repeatCounter; $i++) {
                $iterationData = $data;
                $iterationData['start'] = $iterationStart->addHours($i < 1 ? 0 : $duration->h)->format(static::DATETIME_FORMAT);
                $iterationData['end'] = $iterationEnd->addHours($i < 1 ? 0 : $duration->h)->format(static::DATETIME_FORMAT);

                $models[] = static::create($iterationData);
            }
        } catch (\Throwable $throwable) {
            $errorBag[] = $throwable;
        }

        if ($errorBag !== []) {
            throw $errorBag[0];
        }

        return $models;
    }
}
