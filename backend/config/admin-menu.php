<?php

return [
    [
        'section' => null,
        'items' => [
            ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard', 'icon' => 'home'],
        ],
    ],
    [
        'section' => 'Quản lý nội dung',
        'items' => [
            ['label' => 'Quản lý liên lạc', 'url' => '#', 'icon' => 'mail', 'permission' => 'contacts.manage'],
            ['label' => 'Quản lý Recipes', 'url' => '#', 'icon' => 'book-open', 'permission' => 'recipes.manage'],
            ['label' => 'Quan hệ cổ đông', 'url' => '#', 'icon' => 'chart', 'permission' => 'investor-documents.manage'],
            ['label' => 'Quản lý tin tức', 'url' => '#', 'icon' => 'newspaper', 'permission' => 'posts.manage'],
            [
                'label' => 'Quản lý sản phẩm',
                'icon' => 'package',
                'permission' => 'products.view',
                'active' => 'admin.products.*',
                'children' => [
                    ['label' => 'Danh sách sản phẩm', 'route' => 'admin.products.index'],
                    ['label' => 'Thêm sản phẩm mới', 'route' => 'admin.products.create'],
                    ['label' => 'Danh mục sản phẩm', 'route' => 'admin.product-categories.index'],
                ],
            ],
            ['label' => 'Quản lý công thức', 'url' => '#', 'icon' => 'chef-hat', 'permission' => 'recipes.manage'],
            ['label' => 'Quản lý dự án', 'url' => '#', 'icon' => 'briefcase', 'permission' => 'pages.manage'],
            ['label' => 'Quản lý tuyển dụng', 'url' => '#', 'icon' => 'users', 'permission' => 'recruitment.manage'],
            ['label' => 'Quản lý Banner - Logo', 'url' => '#', 'icon' => 'image', 'permission' => 'media.manage'],
            ['label' => 'Cấu hình site', 'url' => '#', 'icon' => 'settings', 'permission' => 'settings.manage'],
            ['label' => 'SEO - Mạng xã hội', 'url' => '#', 'icon' => 'search', 'permission' => 'settings.manage'],
            ['label' => 'Thông tin hệ thống', 'url' => '#', 'icon' => 'info', 'permission' => 'settings.manage'],
        ],
    ],
    [
        'section' => 'Hệ thống',
        'items' => [
            ['label' => 'Người dùng', 'url' => '#', 'icon' => 'user', 'permission' => 'users.manage'],
            ['label' => 'Vai trò & Quyền', 'url' => '#', 'icon' => 'shield', 'permission' => 'roles.manage'],
            ['label' => 'Nhật ký hoạt động', 'url' => '#', 'icon' => 'history', 'permission' => 'activity.manage'],
        ],
    ],
];
