<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\EventTicketCode
 *
 * @property int $id
 * @property string $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $event_ticket_id
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode newQuery()
 * @method static \Illuminate\Database\Query\Builder|EventTicketCode onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|EventTicketCode withTrashed()
 * @method static \Illuminate\Database\Query\Builder|EventTicketCode withoutTrashed()
 * @mixin \Eloquent
 */
class EventTicketCode extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'event_ticket_codes';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'code',
        'event_ticket_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function eventTicket(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EventTicket::class, 'event_ticket_id');
    }
}
