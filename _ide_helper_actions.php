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
 * @method static \Lorisleiva\Actions\Decorators\JobDecorator|\Lorisleiva\Actions\Decorators\UniqueJobDecorator makeJob(array $cartData)
 * @method static \Lorisleiva\Actions\Decorators\UniqueJobDecorator makeUniqueJob(array $cartData)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(array $cartData)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchIf(bool $boolean, array $cartData)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchUnless(bool $boolean, array $cartData)
 * @method static dispatchSync(array $cartData)
 * @method static dispatchNow(array $cartData)
 * @method static dispatchAfterResponse(array $cartData)
 * @method static \App\Models\Order run(array $cartData)
 */
class CreateOrderFromCart
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
namespace App\Actions\Order;

/**
 * @method static \Lorisleiva\Actions\Decorators\JobDecorator|\Lorisleiva\Actions\Decorators\UniqueJobDecorator makeJob(\App\Models\Order $order)
 * @method static \Lorisleiva\Actions\Decorators\UniqueJobDecorator makeUniqueJob(\App\Models\Order $order)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(\App\Models\Order $order)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchIf(bool $boolean, \App\Models\Order $order)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchUnless(bool $boolean, \App\Models\Order $order)
 * @method static dispatchSync(\App\Models\Order $order)
 * @method static dispatchNow(\App\Models\Order $order)
 * @method static dispatchAfterResponse(\App\Models\Order $order)
 * @method static void run(\App\Models\Order $order)
 */
class AttachEventTicketCodeToOrderItemAction
{
}
namespace App\Actions\Payment\PayPalFriendsFamily;

/**
 * @method static \Lorisleiva\Actions\Decorators\JobDecorator|\Lorisleiva\Actions\Decorators\UniqueJobDecorator makeJob(\App\Models\Transaction $transaction)
 * @method static \Lorisleiva\Actions\Decorators\UniqueJobDecorator makeUniqueJob(\App\Models\Transaction $transaction)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(\App\Models\Transaction $transaction)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchIf(bool $boolean, \App\Models\Transaction $transaction)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchUnless(bool $boolean, \App\Models\Transaction $transaction)
 * @method static dispatchSync(\App\Models\Transaction $transaction)
 * @method static dispatchNow(\App\Models\Transaction $transaction)
 * @method static dispatchAfterResponse(\App\Models\Transaction $transaction)
 * @method static void run(\App\Models\Transaction $transaction)
 */
class PayTransaction
{
}
namespace App\Actions\Payment;

/**
 * @method static \Lorisleiva\Actions\Decorators\JobDecorator|\Lorisleiva\Actions\Decorators\UniqueJobDecorator makeJob(\App\Models\Transaction $transaction)
 * @method static \Lorisleiva\Actions\Decorators\UniqueJobDecorator makeUniqueJob(\App\Models\Transaction $transaction)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(\App\Models\Transaction $transaction)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchIf(bool $boolean, \App\Models\Transaction $transaction)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchUnless(bool $boolean, \App\Models\Transaction $transaction)
 * @method static dispatchSync(\App\Models\Transaction $transaction)
 * @method static dispatchNow(\App\Models\Transaction $transaction)
 * @method static dispatchAfterResponse(\App\Models\Transaction $transaction)
 * @method static void run(\App\Models\Transaction $transaction)
 */
class PayTransaction
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