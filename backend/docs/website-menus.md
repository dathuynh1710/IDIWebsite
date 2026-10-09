# Menu website

## Triển khai

Từ thư mục `backend`:

```sh
php artisan migrate
php artisan db:seed --class=MenuSeeder
```

`MenuSeeder` chỉ tạo vị trí `main`/`footer` chưa tồn tại. Không sửa, bổ sung lại hoặc ghi đè vị trí đã có, kể cả menu rỗng hay bị tắt. Không cần chạy lại toàn bộ `DatabaseSeeder` trên dữ liệu hiện có. Seed ban đầu lấy các trang Giới thiệu và nhóm sản phẩm đang có bằng ID; mục mới tạo sau đó được thêm vào menu qua admin.

## Quản trị

- Mở **Hệ thống → Quản lý menu** (`/admin/menus`), yêu cầu quyền `settings.manage` hoặc vai trò super-admin.
- Chọn menu chính hoặc footer, bấm **Thêm mục menu**. Để mục cha trống cho cấp gốc; chọn một mục cùng menu để tạo mục con.
- Nhập nhãn VI/EN/中文. VI bắt buộc, nhãn EN/中文 trống dùng VI.
- Chọn trang CMS Giới thiệu theo ID, nhóm sản phẩm theo ID, URL nội bộ `/...`, hoặc URL ngoài HTTP(S).
- Trong **Sửa / chuyển / sắp xếp**, thay đổi mục cha và trường **Thứ tự**. Thứ tự tăng dần, trùng thứ tự thì ID tăng dần. Không cho chọn chính nó, hậu duệ hay mục của menu khác làm cha.
- Ẩn cha sẽ ẩn cả nhánh trên website. Nút ẩn toàn bộ menu trả cây rỗng, không kích hoạt menu dự phòng.
- Xóa có hộp xác nhận; mục cha còn con sẽ bị chặn. Chuyển hoặc xóa con trước. Xóa mục menu không xóa trang CMS.
- Các thay đổi được ghi vào nhật ký admin hiện có.

Vị trí khác có thể tạo bằng mã riêng; frontend hiện tích hợp `main` và `footer`. Mục gốc của footer là cột, các mục con là liên kết trong cột. CTA liên hệ, logo và thông tin liên hệ là thành phần giao diện riêng, không phải mục menu.

## API và frontend

`GET /api/menus/{location}?locale=vi|en|zh` (nhận cả `zh-CN`) trả `items` dạng cây. Menu tắt trả 200 với cây rỗng; vị trí chưa tồn tại trả 404. API eager-load trang/nhóm sản phẩm, không truy vấn theo từng nút.

URL CMS dùng `AboutPageRoutes`, cùng quy tắc với `localized_routes`. Trang bị ẩn/xóa hoặc module Giới thiệu tắt: bỏ nhánh liên quan. Trang thiếu slug/bản dịch của locale: giữ nhãn và con nhưng `href: null`, frontend hiển thị nhãn không bấm được. Không tự dựng slug từ tiêu đề. Liên kết CMS hiện giới hạn các trang Giới thiệu có route frontend; loại trang khác dùng URL nội bộ nếu đã có route.

`MenuProvider` tải header/footer theo locale; `MenuTree` render đệ quy bằng link và nút có `aria-expanded`/`aria-controls`. Tab tới nút rồi Enter/Space để mở/đóng; Escape đóng submenu và trả focus về nút. Mobile dùng cùng cây. Sidebar cổ đông lấy nhánh `/investors` từ menu chính.

API chưa triển khai (404 lần đầu): dùng dữ liệu tương thích trong `navigation.js`. Đang tải hoặc lỗi mạng lần đầu: giữ liên kết trang chủ và nút thử lại khi lỗi. Nếu đã tải menu CMS, lỗi tiếp theo giữ kết quả gần nhất của cùng locale, không phục hồi danh sách hardcode hoặc hiện lại mục đã ẩn. Không giữ cây locale cũ khi đổi ngôn ngữ.

## Kiểm thử

```sh
# backend
php artisan test --filter="Menu|About"
# frontend
npm test
npm run build
```
