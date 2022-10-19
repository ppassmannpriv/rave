<!doctype html>
<html>
@include('mail.partials.head')
<body style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
<span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">Order #{{ $order->id }}</span>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f6f6f6; width: 100%;" width="100%" bgcolor="#f6f6f6">
    <tr>
        <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;" valign="top">&nbsp;</td>
        <td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; max-width: 580px; padding: 10px; width: 580px; margin: 0 auto;" width="580" valign="top">
            <div class="content" style="box-sizing: border-box; display: block; margin: 0 auto; max-width: 580px; padding: 10px;">

                <!-- START CENTERED WHITE CONTAINER -->
                <table role="presentation" class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background: #ffffff; border-radius: 3px; width: 100%;" width="100%">

                    <!-- START MAIN CONTENT AREA -->
                    <tr>
                        <td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;" valign="top">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" width="100%">
                                <tr>
                                    <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;" valign="top">
                                        <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; margin-bottom: 15px;">Hello {{ $order->user->name }},</p>
                                        <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; margin-bottom: 15px;">Your order is all paid! Here are your ticket codes!</p>
                                        @foreach($order->orderItems as $orderItem)
                                            <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; padding: 5px 10px; margin-bottom: 1px; background: #f5f5f5;">
                                                <strong>{{ $orderItem->eventTicket->event->name }}</strong><br />
                                                Location: {{ $orderItem->eventTicket->event->location }}<br />
                                                Start: {{ \Carbon\Carbon::parse($orderItem->eventTicket->event->start)->format('d-m-Y H:i:s') }}<br />
                                                End: {{ \Carbon\Carbon::parse($orderItem->eventTicket->event->end)->format('d-m-Y H:i:s') }}<br />
                                                Type: {{ $orderItem->eventTicket->ticket_type }}<br /><br />
                                                <span>Codes:<br />
                                                    @foreach($orderItem->eventTicketCodes as $eventTicketCode)
                                                        <strong style="font-size: 18px;">{{ $eventTicketCode->code }}</strong><br />
                                                    @endforeach
                                                </span>
                                            </p>
                                        @endforeach
                                        <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; margin-top: 15px; margin-bottom: 15px;">
                                            Thank you again! We hope you will have a lot of fun and make great memories with us soon!
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- END MAIN CONTENT AREA -->
                </table>
                <!-- END CENTERED WHITE CONTAINER -->

                @include('mail.partials.footer')

            </div>
        </td>
        <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;" valign="top">&nbsp;</td>
    </tr>
</table>
</body>
</html>
