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
            [
                'label' => 'Quản lý liên lạc',
                'icon' => 'mail',
                'permission' => 'contacts.manage',
                'active' => 'admin.contacts.*',
                'children' => [
                    ['label' => 'Cấu hình liên lạc', 'route' => 'admin.contacts.settings'],
                    ['label' => 'Quản lý thư liên hệ', 'route' => 'admin.contacts.index'],
                ],
            ],
            [
                'label' => 'Quản lý Recipes',
                'icon' => 'book-open',
                'permission' => 'recipes.view',
                'active' => 'admin.recipes.*',
                'children' => [
                    ['label' => 'Cấu hình chung', 'route' => 'admin.recipes.settings'],
                    ['label' => 'Thêm Recipe mới', 'route' => 'admin.recipes.create'],
                    ['label' => 'Quản lý danh sách', 'route' => 'admin.recipes.index'],
                ],
            ],
            [
                'label' => 'Quan hệ cổ đông',
                'icon' => 'chart',
                'permission' => 'investor-documents.view',
                'active' => 'admin.investors.*',
                'children' => [
                    ['label' => 'Cấu hình QHCĐ', 'route' => 'admin.investors.settings'],
                    ['label' => 'Quản lý danh mục', 'route' => 'admin.investors.categories.index'],
                    ['label' => 'Thêm QHCĐ mới', 'route' => 'admin.investors.documents.create'],
                    ['label' => 'Quản lý QHCĐ', 'route' => 'admin.investors.documents.index'],
                ],
            ],
            [
                'label' => 'Quản lý tin tức',
                'icon' => 'newspaper',
                'permission' => 'posts.view',
                'active' => 'admin.news.*',
                'children' => [
                    ['label' => 'Cấu hình tin tức', 'route' => 'admin.news.settings'],
                    ['label' => 'Quản lý danh mục', 'route' => 'admin.news.categories.index'],
                    ['label' => 'Thêm tin mới', 'route' => 'admin.news.posts.create'],
                    ['label' => 'Quản lý tin tức', 'route' => 'admin.news.posts.index'],
                    ['label' => 'Quản lý tin tiêu điểm', 'route' => 'admin.news.featured'],
                ],
            ],
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
            [
                'label' => 'Quản lý giới thiệu',
                'icon' => 'info',
                'permission' => 'pages.view',
                'active' => 'admin.about-pages.*',
                'children' => [
                    ['label' => 'Cấu hình giới thiệu', 'route' => 'admin.about-pages.settings'],
                    ['label' => 'Thêm giới thiệu mới', 'route' => 'admin.about-pages.create'],
                    ['label' => 'Quản lý giới thiệu', 'route' => 'admin.about-pages.index'],
                ],
            ],
            ['label' => 'Quản lý dự án', 'url' => '#', 'icon' => 'briefcase', 'permission' => 'pages.manage', 'hidden' => true],
            [
                'label' => 'Quản lý tuyển dụng',
                'icon' => 'users',
                'permission' => 'recruitment.view',
                'active' => 'admin.recruitment.*',
                'children' => [
                    ['label' => 'Cấu hình chung', 'route' => 'admin.recruitment.settings'],
                    ['label' => 'Quản lý tuyển dụng', 'route' => 'admin.recruitment.positions.index'],
                    ['label' => 'Thêm tuyển dụng mới', 'route' => 'admin.recruitment.positions.create'],
                    ['label' => 'Quản lý đăng ký', 'route' => 'admin.recruitment.applications.index'],
                ],
            ],
            ['label' => 'Quản lý Banner - Logo', 'url' => '#', 'icon' => 'image', 'permission' => 'media.manage', 'hidden' => true],
            ['label' => 'Cấu hình site', 'url' => '#', 'icon' => 'settings', 'permission' => 'settings.manage', 'hidden' => true],
            ['label' => 'SEO - Mạng xã hội', 'url' => '#', 'icon' => 'search', 'permission' => 'settings.manage', 'hidden' => true],
            ['label' => 'Thông tin hệ thống', 'url' => '#', 'icon' => 'info', 'permission' => 'settings.manage', 'hidden' => true],
        ],
    ],
    [
        'section' => 'Hệ thống',
        'items' => [
            ['label' => 'Quản trị viên', 'route' => 'admin.access.users', 'icon' => 'user', 'permission' => 'users.view'],
            ['label' => 'Vai trò & Quyền', 'route' => 'admin.access.roles', 'icon' => 'shield', 'permission' => 'roles.view', 'active' => 'admin.access.roles'],
            ['label' => 'Danh mục quyền hạn', 'route' => 'admin.access.permissions', 'icon' => 'check', 'permission' => 'permissions.view'],
            ['label' => 'Nhật ký hoạt động', 'route' => 'admin.activity-logs.index', 'icon' => 'history', 'permission' => 'activity.view'],
        ],
    ],
];
