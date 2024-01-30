<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            [
                'id'    => 1,
                'title' => 'user_management_access',
            ],
            [
                'id'    => 2,
                'title' => 'permission_create',
            ],
            [
                'id'    => 3,
                'title' => 'permission_edit',
            ],
            [
                'id'    => 4,
                'title' => 'permission_show',
            ],
            [
                'id'    => 5,
                'title' => 'permission_delete',
            ],
            [
                'id'    => 6,
                'title' => 'permission_access',
            ],
            [
                'id'    => 7,
                'title' => 'role_create',
            ],
            [
                'id'    => 8,
                'title' => 'role_edit',
            ],
            [
                'id'    => 9,
                'title' => 'role_show',
            ],
            [
                'id'    => 10,
                'title' => 'role_delete',
            ],
            [
                'id'    => 11,
                'title' => 'role_access',
            ],
            [
                'id'    => 12,
                'title' => 'user_create',
            ],
            [
                'id'    => 13,
                'title' => 'user_edit',
            ],
            [
                'id'    => 14,
                'title' => 'user_show',
            ],
            [
                'id'    => 15,
                'title' => 'user_delete',
            ],
            [
                'id'    => 16,
                'title' => 'user_access',
            ],
            [
                'id'    => 17,
                'title' => 'event_create',
            ],
            [
                'id'    => 18,
                'title' => 'event_edit',
            ],
            [
                'id'    => 19,
                'title' => 'event_show',
            ],
            [
                'id'    => 20,
                'title' => 'event_delete',
            ],
            [
                'id'    => 21,
                'title' => 'event_access',
            ],
            [
                'id'    => 22,
                'title' => 'ticket_access',
            ],
            [
                'id'    => 23,
                'title' => 'event_ticket_create',
            ],
            [
                'id'    => 24,
                'title' => 'event_ticket_edit',
            ],
            [
                'id'    => 25,
                'title' => 'event_ticket_show',
            ],
            [
                'id'    => 26,
                'title' => 'event_ticket_delete',
            ],
            [
                'id'    => 27,
                'title' => 'event_ticket_access',
            ],
            [
                'id'    => 28,
                'title' => 'content_management_access',
            ],
            [
                'id'    => 29,
                'title' => 'content_category_create',
            ],
            [
                'id'    => 30,
                'title' => 'content_category_edit',
            ],
            [
                'id'    => 31,
                'title' => 'content_category_show',
            ],
            [
                'id'    => 32,
                'title' => 'content_category_delete',
            ],
            [
                'id'    => 33,
                'title' => 'content_category_access',
            ],
            [
                'id'    => 34,
                'title' => 'content_tag_create',
            ],
            [
                'id'    => 35,
                'title' => 'content_tag_edit',
            ],
            [
                'id'    => 36,
                'title' => 'content_tag_show',
            ],
            [
                'id'    => 37,
                'title' => 'content_tag_delete',
            ],
            [
                'id'    => 38,
                'title' => 'content_tag_access',
            ],
            [
                'id'    => 39,
                'title' => 'content_page_create',
            ],
            [
                'id'    => 40,
                'title' => 'content_page_edit',
            ],
            [
                'id'    => 41,
                'title' => 'content_page_show',
            ],
            [
                'id'    => 42,
                'title' => 'content_page_delete',
            ],
            [
                'id'    => 43,
                'title' => 'content_page_access',
            ],
            [
                'id'    => 44,
                'title' => 'sale_access',
            ],
            [
                'id'    => 45,
                'title' => 'event_ticket_code_create',
            ],
            [
                'id'    => 46,
                'title' => 'event_ticket_code_edit',
            ],
            [
                'id'    => 47,
                'title' => 'event_ticket_code_show',
            ],
            [
                'id'    => 48,
                'title' => 'event_ticket_code_delete',
            ],
            [
                'id'    => 49,
                'title' => 'event_ticket_code_access',
            ],
            [
                'id'    => 50,
                'title' => 'order_create',
            ],
            [
                'id'    => 51,
                'title' => 'order_edit',
            ],
            [
                'id'    => 52,
                'title' => 'order_show',
            ],
            [
                'id'    => 53,
                'title' => 'order_delete',
            ],
            [
                'id'    => 54,
                'title' => 'order_access',
            ],
            [
                'id'    => 55,
                'title' => 'payment_create',
            ],
            [
                'id'    => 56,
                'title' => 'payment_edit',
            ],
            [
                'id'    => 57,
                'title' => 'payment_show',
            ],
            [
                'id'    => 58,
                'title' => 'payment_delete',
            ],
            [
                'id'    => 59,
                'title' => 'payment_access',
            ],
            [
                'id'    => 60,
                'title' => 'profile_password_edit',
            ],
            [
                'id'    => 61,
                'title' => 'order_cancel'
            ],
            [
                'id'    => 1000,
                'title' => 'customer_site'
            ],
            [
                'id'    => 1001,
                'title' => 'customer_registered'
            ],
            [
                'id'    => 2001,
                'time_schedule_shift_access'
            ],
            [
                'id'    => 2002,
                'time_schedule_shift_show'
            ],
            [
                'id'    => 2003,
                'time_schedule_shift_create'
            ],
            [
                'id'    => 2004,
                'time_schedule_shift_edit'
            ],
            [
                'id'    => 2005,
                'time_schedule_shift_delete'
            ],
            [
                'id'    => 2101,
                'time_schedule_access'
            ],
            [
                'id'    => 2102,
                'time_schedule_show'
            ],
            [
                'id'    => 2103,
                'time_schedule_create'
            ],
            [
                'id'    => 2104,
                'time_schedule_edit'
            ],
            [
                'id'    => 2105,
                'time_schedule_delete'
            ],
            [
                'id'    => 2200,
                'metrics'
            ],
        ];

        Permission::insert($permissions);
    }
}
