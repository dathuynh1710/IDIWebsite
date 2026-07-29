# IDI Seafood Admin Dashboard

## Tổng quan

Admin CMS được render phía server bằng Laravel Blade. Phần này nằm hoàn toàn
trong `backend/`, độc lập với React public website trong `frontend/`.

- Mỗi màn hình admin là một Laravel route thật, không có SPA fallback.
- Layout, sidebar, header, footer, form fields và UI dùng Blade components.
- Alpine.js xử lý sidebar, dropdown, tab, slug, ảnh preview và modal.
- TinyMCE cung cấp rich-text editor và tải ảnh qua endpoint Laravel có CSRF.
- CRUD, validation, authorization, flash message và pagination do Laravel xử lý.

## Cấu trúc

```text
app/
├── Http/Controllers/Admin/
├── Http/Controllers/Auth/
├── Http/Requests/Admin/
└── Models/
config/
├── admin.php
└── admin-menu.php
resources/
├── css/admin/
├── js/app.js
└── views/
    ├── admin/
    ├── auth/
    ├── components/admin/
    ├── components/form/
    ├── components/ui/
    └── layouts/
routes/admin.php
```

## Layout và components

`layouts/admin.blade.php` là shell dùng chung, nạp assets bằng Vite và gắn
sidebar, header, footer, flash message cùng mobile overlay.

Các component form chính:

- `x-form.input`, `x-form.select`, `x-form.textarea`, `x-form.switch`
- `x-form.section`, `x-form.language-tabs`, `x-form.media-picker`
- `x-form.rich-text-editor`, `x-form.seo-fields`
- `x-form.publication-fields`

Input components liên kết label, validation error, ARIA và dữ liệu `old()`.
Switch luôn gửi `0` hoặc `1` nhờ hidden input.

## Menu

Sidebar đọc `config/admin-menu.php`. Item hỗ trợ `label`, `icon`, `route`,
`permission`, `active` và `children`. Để thêm module, bổ sung item vào đúng
group, dùng route name và permission; không hardcode role trong Blade.

## Routes và CRUD

`bootstrap/app.php` đăng ký `routes/admin.php`. Tất cả route dùng middleware
`web`, `auth`, prefix `/admin` và name prefix `admin.`.

Các URL chính:

- `/admin`
- `/admin/products`
- `/admin/products/create`
- `/admin/products/{product}/edit`
- `/admin/products/{product}/preview`
- `/admin/product-categories`

Product CRUD có store, update, delete và duplicate. Ảnh chèn từ TinyMCE dùng
`POST /admin/media/editor-image`.

## Form đa ngôn ngữ

Create và edit dùng chung `admin/products/_form.blade.php`. Form gồm dữ liệu
chung và ba panel `vi`, `en`, `zh`, mỗi panel có nội dung, SEO, trạng thái và
ngày xuất bản.

Panel chỉ ẩn bằng Alpine `x-show`; trường form/editor không bị unmount nên dữ
liệu không mất khi đổi tab. Slug chỉ tự sinh khi người dùng chưa sửa, có nút
“Tạo lại” và được chuẩn hóa lại trong Form Request.

Trạng thái: `draft`, `translating`, `review`, `scheduled`, `published`,
`hidden`, `archived`. Khi chọn `scheduled`, UI hiện ngày xuất bản; Form Request
vẫn là nguồn validation chính xác.

## TinyMCE và media

TinyMCE hỗ trợ heading, bold, italic, underline, căn lề, link, image, table,
list, undo/redo và HTML source. Ảnh được gửi tới Laravel, giới hạn 5 MB và chỉ
chấp nhận JPG, PNG, WEBP hoặc GIF.

Chạy một lần ở môi trường triển khai:

```bash
php artisan storage:link
```

HTML rich text được làm sạch trước khi lưu. Khi mở rộng HTML cho nhiều module,
nên chuyển sanitizer bảo thủ hiện tại thành allowlist tập trung đã review.

## Permissions

View dùng `@can('products.create')`, `@can('products.update')` và
`@can('products.delete')`, không kiểm tra tên role. `AppServiceProvider` ánh xạ
các ability chi tiết sang permission `products.manage` trong seed data.
`super-admin` được phép toàn bộ qua `Gate::before`.

## Responsive

- Dưới 768px: form một cột, list dạng card, action bar cố định, tabs cuộn ngang.
- 768–1023px: sidebar off-canvas, form hai cột.
- Từ 1024px: sidebar cố định và có thể thu gọn.

Đã kiểm tra tại 375, 768, 1024 và 1440px; mobile/tablet không tràn ngang.

## Thêm module mới

1. Tạo controller và Form Request trong namespace `Admin`.
2. Khai báo route trong `routes/admin.php`.
3. Tạo view dưới `resources/views/admin`.
4. Tái sử dụng layout, page header, form/UI components.
5. Thêm menu item vào `config/admin-menu.php`.
6. Khai báo Gate/Policy và feature tests.

## Development

```bash
composer install
npm install
php artisan storage:link
php artisan serve
npm run dev
```

Build:

```bash
npm run build
php artisan optimize:clear
php artisan test
```

Không chạy `migrate:fresh` trên database có dữ liệu.
