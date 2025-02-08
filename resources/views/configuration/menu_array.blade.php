<?php

$menu = [
    0 => [
        'name' => 'Dashboard',
        'icon' => 'fa fa-dashboard',
        'dropdown' => false,
        'route' => 'admin.dashboard',
        'dropdown_items' => [],
    ],
    1 => [
        'name' => 'Users',
        'icon' => 'fa fa-users',
        'dropdown' => true,
        'route' => '',
        'dropdown_items' => [
            0 => [
                'name' => 'Add User',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.users.create',
            ],
            1 => [
                'name' => 'Manage Users',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.users.index',
            ],
            2 => [
                'name' => 'Manage User Roles',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.roles.index',
            ],
        ],
    ],
    3 => [
        'name' => 'Settings',
        'icon' => 'fa fa-gear',
        'dropdown' => true,
        'route' => '',
        'dropdown_items' => [
            0 => [
                'name' => 'General Settings',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.settings.index',
            ],
            1 => [
                'name' => 'Edit Profile',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.settings.edit_profile',
            ],
        ],
    ],
    // 4 => [
    //     'name' => 'Pages',
    //     'icon' => 'fa fa-file',
    //     'dropdown' => true,
    //     'route' => '',
    //     'dropdown_items' => [
    //         0 => [
    //             'name' => 'Add Page',
    //             'icon' => 'fa fa-circle-o',
    //             'route' => 'admin.pages.create',
    //         ],
    //         1 => [
    //             'name' => 'Manage Pages',
    //             'icon' => 'fa fa-circle-o',
    //             'route' => 'admin.pages.index',
    //         ],
    //     ],
    // ],
    4 => [
        'name' => 'Products',
        'icon' => 'fa fa-file',
        'dropdown' => true,
        'route' => '',
        'dropdown_items' => [
            0 => [
                'name' => 'Add Product',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.category_products.create',
            ],
            1 => [
                'name' => 'Manage Product',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.category_products.index',
            ],
        ],
    ],
    5 => [
        'name' => 'Service Category',
        'icon' => 'fa fa-file',
        'dropdown' => true,
        'route' => '',
        'dropdown_items' => [
            0 => [
                'name' => 'Add Category',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.service_categories.create',
            ],
            1 => [
                'name' => 'Manage Category',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.service_categories.index',
            ],
        ],
    ],
    6 => [
        'name' => 'City Master',
        'icon' => 'fa fa-file',
        'dropdown' => true,
        'route' => '',
        'dropdown_items' => [
            0 => [
                'name' => 'Add City',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.city_master.create',
            ],
            1 => [
                'name' => 'Manage City',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.city_master.index',
            ],
        ],
    ],
    7 => [
        'name' => 'Area Master',
        'icon' => 'fa fa-file',
        'dropdown' => true,
        'route' => '',
        'dropdown_items' => [
            0 => [
                'name' => 'Add Area',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.area_master.create',
            ],
            1 => [
                'name' => 'Manage Area',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.area_master.index',
            ],
        ],
    ],
    8 => [
        'name' => 'Branch Master',
        'icon' => 'fa fa-file',
        'dropdown' => true,
        'route' => '',
        'dropdown_items' => [
            0 => [
                'name' => 'Add Branch',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.branch_master.create',
            ],
            1 => [
                'name' => 'Manage Branch',
                'icon' => 'fa fa-circle-o',
                'route' => 'admin.branch_master.index',
            ],
        ],
    ],
];

unset($menuItem, $dropdownItem);

if (auth()->check() && auth()->user()->hasRole('Branch admin')) {
    foreach ($menu as $key => $item) {
        if ($item['name'] === 'Users' || $item['name'] === 'Users') {
            unset($menu[$key]);
        }
    }
}
