<?php

namespace App\Models;

use \DateTimeInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\EventTicket
 *
 * @property int $id
 * @property string $ticket_type
 * @property string $price
 * @property \Illuminate\Support\Carbon|null $from
 * @property \Illuminate\Support\Carbon|null $to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $event_id
 * @property-read \App\Models\Event|null $event
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket newQuery()
 * @method static \Illuminate\Database\Query\Builder|EventTicket onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket whereTicketType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|EventTicket withTrashed()
 * @method static \Illuminate\Database\Query\Builder|EventTicket withoutTrashed()
 * @mixin \Eloquent
 */
class EventTicket extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const TICKET_TYPE_RADIO = [
        'early_bird' => 'Early Bird',
        'regular'    => 'Regular',
        'guest_list' => 'Guest List',
        'vip'        => 'VIP',
    ];

    public $table = 'event_tickets';

    protected $dates = [
        'from',
        'to',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'ticket_type',
        'price',
        'event_id',
        'from',
        'to',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function getFromAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;
    }

    public function setFromAttribute($value)
    {
        $this->attributes['from'] = $value ? Carbon::createFromFormat(config('panel.date_format') . ' ' . config('panel.time_format'), $value)->format('Y-m-d H:i:s') : null;
    }

    public function getToAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;
    }

    public function setToAttribute($value)
    {
        $this->attributes['to'] = $value ? Carbon::createFromFormat(config('panel.date_format') . ' ' . config('panel.time_format'), $value)->format('Y-m-d H:i:s') : null;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}