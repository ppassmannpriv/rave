<?php

return [
    'userManagement' => [
        'title'          => 'User management',
        'title_singular' => 'User management',
    ],
    'permission' => [
        'title'          => 'Permissions',
        'title_singular' => 'Permission',
        'fields'         => [
            'id'                => 'ID',
            'id_helper'         => ' ',
            'title'             => 'Title',
            'title_helper'      => ' ',
            'created_at'        => 'Created at',
            'created_at_helper' => ' ',
            'updated_at'        => 'Updated at',
            'updated_at_helper' => ' ',
            'deleted_at'        => 'Deleted at',
            'deleted_at_helper' => ' ',
        ],
    ],
    'role' => [
        'title'          => 'Roles',
        'title_singular' => 'Role',
        'fields'         => [
            'id'                 => 'ID',
            'id_helper'          => ' ',
            'title'              => 'Title',
            'title_helper'       => ' ',
            'permissions'        => 'Permissions',
            'permissions_helper' => ' ',
            'created_at'         => 'Created at',
            'created_at_helper'  => ' ',
            'updated_at'         => 'Updated at',
            'updated_at_helper'  => ' ',
            'deleted_at'         => 'Deleted at',
            'deleted_at_helper'  => ' ',
        ],
    ],
    'user' => [
        'title'          => 'Users',
        'title_singular' => 'User',
        'fields'         => [
            'id'                       => 'ID',
            'id_helper'                => ' ',
            'name'                     => 'Name',
            'name_helper'              => ' ',
            'email'                    => 'Email',
            'email_helper'             => ' ',
            'email_verified_at'        => 'Email verified at',
            'email_verified_at_helper' => ' ',
            'password'                 => 'Password',
            'password_helper'          => ' ',
            'roles'                    => 'Roles',
            'roles_helper'             => ' ',
            'remember_token'           => 'Remember Token',
            'remember_token_helper'    => ' ',
            'created_at'               => 'Created at',
            'created_at_helper'        => ' ',
            'updated_at'               => 'Updated at',
            'updated_at_helper'        => ' ',
            'deleted_at'               => 'Deleted at',
            'deleted_at_helper'        => ' ',
        ],
    ],
    'event' => [
        'title'          => 'Events',
        'title_singular' => 'Event',
        'fields'         => [
            'id'                 => 'ID',
            'id_helper'          => ' ',
            'name'               => 'Name',
            'name_helper'        => 'Name of your event - it is not unique!',
            'start'              => 'Start',
            'start_helper'       => 'Start date and time of your event.',
            'end'                => 'End',
            'end_helper'         => 'End date and time of your event.',
            'location'           => 'Location',
            'location_helper'    => 'Location of your event.',
            'description'        => 'Description',
            'description_helper' => 'Description of your events',
            'created_at'         => 'Created at',
            'created_at_helper'  => ' ',
            'updated_at'         => 'Updated at',
            'updated_at_helper'  => ' ',
            'deleted_at'         => 'Deleted at',
            'deleted_at_helper'  => ' ',
        ],
    ],
    'ticket' => [
        'title'          => 'Tickets',
        'title_singular' => 'Ticket',
    ],
    'eventTicket' => [
        'title'          => 'Event Tickets',
        'title_singular' => 'Event Ticket',
        'fields'         => [
            'id'                 => 'ID',
            'id_helper'          => ' ',
            'ticket_type'        => 'Ticket Type',
            'ticket_type_helper' => 'Ticket type you want to sell.',
            'price'              => 'Price',
            'price_helper'       => 'Price of your ticket',
            'stock'              => 'Stock',
            'stock_helper'       => 'Stock of your ticket',
            'event'              => 'Event',
            'event_helper'       => 'Select the event this ticket is for.',
            'from'               => 'Available from:',
            'from_helper'        => 'Set this to give this ticket a date from when it will be available.',
            'to'                 => 'Available to',
            'to_helper'          => 'Set this to give this ticket a time and date to stop being available.',
            'created_at'         => 'Created at',
            'created_at_helper'  => ' ',
            'updated_at'         => 'Updated at',
            'updated_at_helper'  => ' ',
            'deleted_at'         => 'Deleted at',
            'deleted_at_helper'  => ' ',
        ],
    ],
    'contentManagement' => [
        'title'          => 'Content management',
        'title_singular' => 'Content management',
    ],
    'contentCategory' => [
        'title'          => 'Categories',
        'title_singular' => 'Category',
        'fields'         => [
            'id'                => 'ID',
            'id_helper'         => ' ',
            'name'              => 'Name',
            'name_helper'       => ' ',
            'slug'              => 'Slug',
            'slug_helper'       => ' ',
            'created_at'        => 'Created at',
            'created_at_helper' => ' ',
            'updated_at'        => 'Updated At',
            'updated_at_helper' => ' ',
            'deleted_at'        => 'Deleted At',
            'deleted_at_helper' => ' ',
        ],
    ],
    'contentTag' => [
        'title'          => 'Tags',
        'title_singular' => 'Tag',
        'fields'         => [
            'id'                => 'ID',
            'id_helper'         => ' ',
            'name'              => 'Name',
            'name_helper'       => ' ',
            'slug'              => 'Slug',
            'slug_helper'       => ' ',
            'created_at'        => 'Created at',
            'created_at_helper' => ' ',
            'updated_at'        => 'Updated At',
            'updated_at_helper' => ' ',
            'deleted_at'        => 'Deleted At',
            'deleted_at_helper' => ' ',
        ],
    ],
    'contentPage' => [
        'title'          => 'Pages',
        'title_singular' => 'Page',
        'fields'         => [
            'id'                    => 'ID',
            'id_helper'             => ' ',
            'title'                 => 'Title',
            'title_helper'          => ' ',
            'path'                  => 'Path',
            'path_helper'           => ' ',
            'enabled'               => 'Enabled',
            'enabled_helper'        => 'Is page visible or not?',
            'index'                 => 'Is index',
            'index_helper'          => 'Is this page the index/starting page?',
            'category'              => 'Categories',
            'category_helper'       => ' ',
            'tag'                   => 'Tags',
            'tag_helper'            => ' ',
            'page_text'             => 'Full Text',
            'page_text_helper'      => ' ',
            'excerpt'               => 'Excerpt',
            'excerpt_helper'        => ' ',
            'featured_image'        => 'Featured Image',
            'featured_image_helper' => ' ',
            'created_at'            => 'Created at',
            'created_at_helper'     => ' ',
            'updated_at'            => 'Updated At',
            'updated_at_helper'     => ' ',
            'deleted_at'            => 'Deleted At',
            'deleted_at_helper'     => ' ',
        ],
    ],
    'sale' => [
        'title'          => 'Sales',
        'title_singular' => 'Sale',
    ],
    'eventTicketCode' => [
        'title'          => 'Event Ticket Codes',
        'title_singular' => 'Event Ticket Code',
        'fields'         => [
            'id'                => 'ID',
            'id_helper'         => ' ',
            'code'              => 'Code',
            'code_helper'       => 'This code will be used as a reference for every made ticket.',
            'created_at'        => 'Created at',
            'created_at_helper' => ' ',
            'updated_at'        => 'Updated at',
            'updated_at_helper' => ' ',
            'deleted_at'        => 'Deleted at',
            'deleted_at_helper' => ' ',
            'event'             => 'Event',
            'event_at_helper'   => ' ',
        ],
    ],
    'order' => [
        'title'          => 'Orders',
        'title_singular' => 'Order',
        'fields'         => [
            'id'                       => 'ID',
            'id_helper'                => ' ',
            'user'                     => 'User',
            'user_helper'              => 'User that made this order',
            'created_at'               => 'Created at',
            'created_at_helper'        => ' ',
            'updated_at'               => 'Updated at',
            'updated_at_helper'        => ' ',
            'deleted_at'               => 'Deleted at',
            'deleted_at_helper'        => ' ',
            'payment'                  => 'Payment',
            'payment_helper'           => ' ',
            'event_ticket_code'        => 'Event Ticket Code',
            'event_ticket_code_helper' => 'The event ticket code that is being used for the ticket. Not the reference! We split these to avoid people trying to mess with us.',
        ],
    ],
    'payment' => [
        'title'          => 'Payments',
        'title_singular' => 'Payment',
        'fields'         => [
            'id'                => 'ID',
            'id_helper'         => ' ',
            'provider'          => 'Provider',
            'provider_helper'   => 'Provider that was used for this payment.',
            'amount'            => 'Amount',
            'amount_helper'     => 'Amount of this payment',
            'state'             => 'State',
            'state_helper'      => 'State of payment (has been paid or not etc.)',
            'reference'         => 'Reference',
            'reference_helper'  => 'This is the payment reference that is used to identify this payment on our provider end.',
            'created_at'        => 'Created at',
            'created_at_helper' => ' ',
            'updated_at'        => 'Updated at',
            'updated_at_helper' => ' ',
            'deleted_at'        => 'Deleted at',
            'deleted_at_helper' => ' ',
        ],
    ],
    'paymentMethods' => [
        'title'          => 'Payment methods',
        'title_singular' => 'Payment method',
        'fields'         => [
            'created_at'        => 'Created at',
            'created_at_helper' => ' ',
            'updated_at'        => 'Updated at',
            'updated_at_helper' => ' ',
            'deleted_at'        => 'Deleted at',
            'deleted_at_helper' => ' ',
        ],
    ],
];
