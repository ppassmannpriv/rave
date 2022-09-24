<?php

namespace App\Actions\Cart;

/**
 * @method static \Lorisleiva\Actions\Decorators\JobDecorator|\Lorisleiva\Actions\Decorators\UniqueJobDecorator makeJob(\App\Models\EventTicket $eventTicket)
 * @method static \Lorisleiva\Actions\Decorators\UniqueJobDecorator makeUniqueJob(\App\Models\EventTicket $eventTicket)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(\App\Models\EventTicket $eventTicket)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchIf(bool $boolean, \App\Models\EventTicket $eventTicket)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchUnless(bool $boolean, \App\Models\EventTicket $eventTicket)
 * @method static dispatchSync(\App\Models\EventTicket $eventTicket)
 * @method static dispatchNow(\App\Models\EventTicket $eventTicket)
 * @method static dispatchAfterResponse(\App\Models\EventTicket $eventTicket)
 * @method static void run(\App\Models\EventTicket $eventTicket)
 */
class AddTicketToCart
{
}
/**
 * @method static \Lorisleiva\Actions\Decorators\JobDecorator|\Lorisleiva\Actions\Decorators\UniqueJobDecorator makeJob(\App\Models\EventTicket $eventTicket)
 * @method static \Lorisleiva\Actions\Decorators\UniqueJobDecorator makeUniqueJob(\App\Models\EventTicket $eventTicket)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(\App\Models\EventTicket $eventTicket)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchIf(bool $boolean, \App\Models\EventTicket $eventTicket)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchUnless(bool $boolean, \App\Models\EventTicket $eventTicket)
 * @method static dispatchSync(\App\Models\EventTicket $eventTicket)
 * @method static dispatchNow(\App\Models\EventTicket $eventTicket)
 * @method static dispatchAfterResponse(\App\Models\EventTicket $eventTicket)
 * @method static void run(\App\Models\EventTicket $eventTicket)
 */
class ClearCart
{
}
/**
 * @method static \Lorisleiva\Actions\Decorators\JobDecorator|\Lorisleiva\Actions\Decorators\UniqueJobDecorator makeJob(mixed $eventTicketId)
 * @method static \Lorisleiva\Actions\Decorators\UniqueJobDecorator makeUniqueJob(mixed $eventTicketId)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(mixed $eventTicketId)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchIf(bool $boolean, mixed $eventTicketId)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchUnless(bool $boolean, mixed $eventTicketId)
 * @method static dispatchSync(mixed $eventTicketId)
 * @method static dispatchNow(mixed $eventTicketId)
 * @method static dispatchAfterResponse(mixed $eventTicketId)
 * @method static void run(mixed $eventTicketId)
 */
class RemoveTicketFromCart
{
}
/**
 * @method static \Lorisleiva\Actions\Decorators\JobDecorator|\Lorisleiva\Actions\Decorators\UniqueJobDecorator makeJob(\App\Models\EventTicket $eventTicket)
 * @method static \Lorisleiva\Actions\Decorators\UniqueJobDecorator makeUniqueJob(\App\Models\EventTicket $eventTicket)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(\App\Models\EventTicket $eventTicket)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchIf(bool $boolean, \App\Models\EventTicket $eventTicket)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchUnless(bool $boolean, \App\Models\EventTicket $eventTicket)
 * @method static dispatchSync(\App\Models\EventTicket $eventTicket)
 * @method static dispatchNow(\App\Models\EventTicket $eventTicket)
 * @method static dispatchAfterResponse(\App\Models\EventTicket $eventTicket)
 * @method static void run(\App\Models\EventTicket $eventTicket)
 */
class UpdateTicketInCart
{
}
namespace App\Actions\EventTicket;

/**
 * @method static \Lorisleiva\Actions\Decorators\JobDecorator|\Lorisleiva\Actions\Decorators\UniqueJobDecorator makeJob(\App\Models\EventTicket $eventTicket)
 * @method static \Lorisleiva\Actions\Decorators\UniqueJobDecorator makeUniqueJob(\App\Models\EventTicket $eventTicket)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(\App\Models\EventTicket $eventTicket)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchIf(bool $boolean, \App\Models\EventTicket $eventTicket)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchUnless(bool $boolean, \App\Models\EventTicket $eventTicket)
 * @method static dispatchSync(\App\Models\EventTicket $eventTicket)
 * @method static dispatchNow(\App\Models\EventTicket $eventTicket)
 * @method static dispatchAfterResponse(\App\Models\EventTicket $eventTicket)
 * @method static void run(\App\Models\EventTicket $eventTicket)
 */
class CreateCodesForNewEventTicket
{
}
namespace Lorisleiva\Actions\Concerns;

/**
 * @method void asController()
 */
trait AsController
{
}
/**
 * @method void asListener()
 */
trait AsListener
{
}
/**
 * @method void asJob()
 */
trait AsJob
{
}
/**
 * @method void asCommand(\Illuminate\Console\Command $command)
 */
trait AsCommand
{
}