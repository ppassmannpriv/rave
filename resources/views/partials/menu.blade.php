<div id="sidebar" class="c-sidebar c-sidebar-fixed c-sidebar-lg-show">

    <div class="c-sidebar-brand d-md-down-none">
        <a class="c-sidebar-brand-full h4" href="#">
            {{ trans('panel.site_title') }}
        </a>
    </div>

    <ul class="c-sidebar-nav">
        <li class="c-sidebar-nav-item">
            <a href="{{ route("admin.home") }}" class="c-sidebar-nav-link">
            <i class="c-sidebar-nav-icon fas fa-fw fa-tachometer-alt">

            </i>
            {{ trans('global.dashboard') }}
            </a>
        </li>
        @can('ticket_access')
        <li class="c-sidebar-nav-dropdown {{ request()->is("admin/events*") ? "c-show" : "" }} {{ request()->is("admin/event-tickets*") ? "c-show" : "" }}">
        <a class="c-sidebar-nav-dropdown-toggle" href="#">
            <i class="fa-fw fas fa-ticket-alt c-sidebar-nav-icon">

            </i>
            {{ trans('cruds.ticket.title') }}
        </a>
        <ul class="c-sidebar-nav-dropdown-items">
            @can('event_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.events.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/events") || request()->is("admin/events/*") ? "c-active" : "" }}">
                <i class="fa-fw fas fa-calendar-alt c-sidebar-nav-icon">

                </i>
                {{ trans('cruds.event.title') }}
                </a>
            </li>
            @endcan
            @can('event_ticket_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.event-tickets.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/event-tickets") || request()->is("admin/event-tickets/*") ? "c-active" : "" }}">
                <i class="fa-fw fas fa-ticket-alt c-sidebar-nav-icon">

                </i>
                {{ trans('cruds.eventTicket.title') }}
                </a>
            </li>
            @endcan
        </ul>
        </li>
        @endcan
        @can('sale_access')
        <li class="c-sidebar-nav-dropdown {{ request()->is("admin/event-ticket-codes*") ? "c-show" : "" }} {{ request()->is("admin/orders*") ? "c-show" : "" }} {{ request()->is("admin/payments*") ? "c-show" : "" }}">
        <a class="c-sidebar-nav-dropdown-toggle" href="#">
            <i class="fa-fw fas fa-money-bill c-sidebar-nav-icon">

            </i>
            {{ trans('cruds.sale.title') }}
        </a>
        <ul class="c-sidebar-nav-dropdown-items">
            @can('event_ticket_code_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.event-ticket-codes.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/event-ticket-codes") || request()->is("admin/event-ticket-codes/*") ? "c-active" : "" }}">
                <i class="fa-fw fas fa-lock c-sidebar-nav-icon">

                </i>
                {{ trans('cruds.eventTicketCode.title') }}
                </a>
            </li>
            @endcan
            @can('order_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.orders.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/orders") || request()->is("admin/orders/*") ? "c-active" : "" }}">
                <i class="fa-fw fas fa-shopping-cart c-sidebar-nav-icon">

                </i>
                {{ trans('cruds.order.title') }}
                </a>
            </li>
            @endcan
            @can('payment_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.payments.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/payments") || request()->is("admin/payments/*") ? "c-active" : "" }}">
                <i class="fa-fw far fa-money-bill-alt c-sidebar-nav-icon">

                </i>
                {{ trans('cruds.payment.title') }}
                </a>
            </li>
            @endcan
            @can('payment_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.paymentMethods.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/paymentMethods") || request()->is("admin/paymentMethods/*") ? "c-active" : "" }}">
                <i class="fa-fw far fa-money-bill-alt c-sidebar-nav-icon">

                </i>
                {{ trans('cruds.paymentMethods.title') }}
                </a>
            </li>
            @endcan
        </ul>
        </li>
        @endcan
        @can('time_schedule_access')
        <li class="c-sidebar-nav-item">
            <a class="c-sidebar-nav-link {{ request()->is('admin/time-schedules') || request()->is('admin/time-schedules/*') ? 'c-active' : '' }}" href="{{ route('admin.time-schedules.index') }}">
                <i class="fa-fw fas fa-clock c-sidebar-nav-icon">
                </i>
                {{ trans('cruds.time-schedules.title') }}
            </a>
        </li>
        @endcan
        @can('content_management_access')
        <li class="c-sidebar-nav-dropdown {{ request()->is("admin/content-categories*") ? "c-show" : "" }} {{ request()->is("admin/content-tags*") ? "c-show" : "" }} {{ request()->is("admin/content-pages*") ? "c-show" : "" }}">
        <a class="c-sidebar-nav-dropdown-toggle" href="#">
            <i class="fa-fw fas fa-book c-sidebar-nav-icon">

            </i>
            {{ trans('cruds.contentManagement.title') }}
        </a>
        <ul class="c-sidebar-nav-dropdown-items">
            @can('content_category_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.content-categories.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/content-categories") || request()->is("admin/content-categories/*") ? "c-active" : "" }}">
                <i class="fa-fw fas fa-folder c-sidebar-nav-icon">

                </i>
                {{ trans('cruds.contentCategory.title') }}
                </a>
            </li>
            @endcan
            @can('content_tag_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.content-tags.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/content-tags") || request()->is("admin/content-tags/*") ? "c-active" : "" }}">
                <i class="fa-fw fas fa-tags c-sidebar-nav-icon">

                </i>
                {{ trans('cruds.contentTag.title') }}
                </a>
            </li>
            @endcan
            @can('content_page_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.content-pages.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/content-pages") || request()->is("admin/content-pages/*") ? "c-active" : "" }}">
                <i class="fa-fw fas fa-file c-sidebar-nav-icon">

                </i>
                {{ trans('cruds.contentPage.title') }}
                </a>
            </li>
            @endcan
        </ul>
        </li>
        @endcan
        @can('user_management_access')
        <li class="c-sidebar-nav-dropdown {{ request()->is("admin/permissions*") ? "c-show" : "" }} {{ request()->is("admin/roles*") ? "c-show" : "" }} {{ request()->is("admin/users*") ? "c-show" : "" }}">
        <a class="c-sidebar-nav-dropdown-toggle" href="#">
            <i class="fa-fw fas fa-users c-sidebar-nav-icon">

            </i>
            {{ trans('cruds.userManagement.title') }}
        </a>
        <ul class="c-sidebar-nav-dropdown-items">
            @can('permission_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.permissions.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/permissions") || request()->is("admin/permissions/*") ? "c-active" : "" }}">
                <i class="fa-fw fas fa-unlock-alt c-sidebar-nav-icon">

                </i>
                {{ trans('cruds.permission.title') }}
                </a>
            </li>
            @endcan
            @can('role_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.roles.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/roles") || request()->is("admin/roles/*") ? "c-active" : "" }}">
                <i class="fa-fw fas fa-briefcase c-sidebar-nav-icon">

                </i>
                {{ trans('cruds.role.title') }}
                </a>
            </li>
            @endcan
            @can('user_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.users.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/users") || request()->is("admin/users/*") ? "c-active" : "" }}">
                <i class="fa-fw fas fa-user c-sidebar-nav-icon">

                </i>
                {{ trans('cruds.user.title') }}
                </a>
            </li>
            @endcan
        </ul>
        </li>
        @endcan
        @if(file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php')))
        @can('profile_password_edit')
        <li class="c-sidebar-nav-item">
            <a class="c-sidebar-nav-link {{ request()->is('profile/password') || request()->is('profile/password/*') ? 'c-active' : '' }}" href="{{ route('profile.password.edit') }}">
                <i class="fa-fw fas fa-key c-sidebar-nav-icon">
                </i>
                {{ trans('global.change_password') }}
            </a>
        </li>
        @endcan
        @endif
        @can('profile_password_edit')
        <li class="c-sidebar-nav-item">
            <a class="c-sidebar-nav-link {{ request()->is('tasks/schedule') || request()->is('tasks/schedule/*') ? 'c-active' : '' }}" href="{{ route('admin.tasks.schedule.index') }}">
                <i class="fa-fw fas fa-clock c-sidebar-nav-icon">
                </i>
                Scheduled Tasks
            </a>
        </li>
        @endcan
        @can('metrics')
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link {{ request()->is('metrics/index') || request()->is('metrics/*') ? 'c-active' : '' }}" href="{{ route('admin.metrics.index') }}">
                    <i class="fa-fw fas fa-clock c-sidebar-nav-icon">
                    </i>
                   Metrics
                </a>
            </li>
        @endcan
        @can('payment_access')
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link {{ request()->is('bookkeeping/index') || request()->is('bookkeeping/*') ? 'c-active' : '' }}" href="{{ route('admin.bookkeeping.index') }}">
                    <i class="fa-fw fas fa-money-bill c-sidebar-nav-icon">
                    </i>
                    Bookkeeping
                </a>
            </li>
        @endcan
        <li class="c-sidebar-nav-item">
            <a href="#" class="c-sidebar-nav-link" onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                <i class="c-sidebar-nav-icon fas fa-fw fa-sign-out-alt">

                </i>
                {{ trans('global.logout') }}
            </a>
        </li>
    </ul>

</div>
