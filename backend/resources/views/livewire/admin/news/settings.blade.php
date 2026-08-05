<div>
    <x-admin.page-header title="Cấu hình Tin tức" description="Quản lý nội dung, hiển thị và vận hành module Tin tức tại một nơi." :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button type="submit" form="news-settings" icon="save">Lưu cấu hình</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="news-settings" wire:submit="save" data-dirty-form class="news-settings-form"
        x-data="{ activeTab: 'general' }">
        @if($errors->any())
            <div class="validation-summary" role="alert">
                <x-ui.icon name="alert" />
                <div><strong>Vui lòng kiểm tra lại cấu hình.</strong><p>Có {{ $errors->count() }} trường cần chỉnh sửa.</p></div>
            </div>
        @endif

        <nav class="settings-tab-list" role="tablist" aria-label="Nhóm cấu hình Tin tức">
            @foreach([
                'general' => ['Chung', 'settings'],
                'seo' => ['SEO', 'search'],
                'images' => ['Hình ảnh', 'image'],
                'display' => ['Hiển thị', 'eye'],
                'upload' => ['Upload', 'upload'],
                'comments' => ['Bình luận', 'users'],
                'performance' => ['Hiệu năng', 'chart'],
            ] as $tab => [$label, $icon])
                <button type="button" role="tab" id="settings-tab-{{ $tab }}"
                    :class="{ 'is-active': activeTab === '{{ $tab }}' }"
                    :aria-selected="activeTab === '{{ $tab }}'"
                    aria-controls="settings-panel-{{ $tab }}"
                    x-on:click="activeTab = '{{ $tab }}'">
                    <x-ui.icon :name="$icon" size="17" /> {{ $label }}
                </button>
            @endforeach
        </nav>

        <div class="settings-tab-panels">
            <section id="settings-panel-general" role="tabpanel" aria-labelledby="settings-tab-general"
                x-show="activeTab === 'general'" x-cloak>
                <div class="settings-panel-grid">
                    <section class="card news-settings-status">
                        <div class="news-settings-status-copy">
                            <span class="news-settings-eyebrow">Trạng thái module</span>
                            <h2>Trang Tin tức {{ $module_enabled ? 'đang hoạt động' : 'đang tạm dừng' }}</h2>
                            <p>Tạm dừng chỉ ẩn dữ liệu khỏi website; toàn bộ bài viết và cấu hình trong trang quản trị vẫn được giữ nguyên.</p>
                        </div>
                        <div class="switch-group news-settings-status-switch news-settings-switch-list">
                            <x-form.switch name="module_enabled" label="Bật trang Tin tức" helper="Cho phép website cung cấp nội dung Tin tức" wire:model.live="module_enabled" />
                        </div>
                    </section>

                    <x-form.section title="Nội dung trang" description="Tên và phần giới thiệu được hiển thị theo từng ngôn ngữ." icon="languages">
                        <x-form.language-tabs :locales="$locales" id-prefix="general-">
                            @foreach($locales as $locale => $label)
                                <section id="general-panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                                    <div class="settings-field-list">
                                        <x-form.input name="page_title[{{ $locale }}]" label="Tiêu đề trang" wire:model.blur="page_title.{{ $locale }}" :required="$locale === 'vi'" helper="Tiêu đề chính trên trang danh sách Tin tức." />
                                        <x-form.textarea name="description[{{ $locale }}]" label="Mô tả giới thiệu" wire:model.blur="description.{{ $locale }}" rows="3" maxlength="2000" helper="Đoạn giới thiệu ngắn ở đầu trang Tin tức." />
                                    </div>
                                </section>
                            @endforeach
                        </x-form.language-tabs>
                    </x-form.section>

                    <x-form.section title="Nâng cao" description="Các chức năng ít dùng; chỉ bật khi website đã hỗ trợ luồng tương ứng." icon="settings">
                        <details class="settings-advanced">
                            <summary>Hiển thị cấu hình nâng cao</summary>
                            <div class="switch-group news-settings-switch-list">
                                <x-form.switch name="allow_print" label="Tạo trang in" helper="Cung cấp phiên bản bài viết tối ưu cho máy in." wire:model="allow_print" />
                                <x-form.switch name="fetch_remote_images" label="Lấy hình từ web nguồn" helper="Chỉ dùng với nguồn nội dung đáng tin cậy và có quyền sử dụng ảnh." wire:model="fetch_remote_images" />
                            </div>
                        </details>
                    </x-form.section>
                </div>
            </section>

            <section id="settings-panel-seo" role="tabpanel" aria-labelledby="settings-tab-seo"
                x-show="activeTab === 'seo'" x-cloak>
                <x-form.section title="Tối ưu công cụ tìm kiếm" description="Thiết lập tiêu đề và mô tả riêng cho từng ngôn ngữ. Meta Keyword đã được loại khỏi giao diện nhưng dữ liệu cũ vẫn được giữ nguyên." icon="search">
                    <x-form.language-tabs :locales="$locales" id-prefix="seo-">
                        @foreach($locales as $locale => $label)
                            <section id="seo-panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                                <div class="settings-field-list">
                                    <x-form.input name="seo_title[{{ $locale }}]" label="Friendly Title" wire:model.blur="seo_title.{{ $locale }}" maxlength="255" helper="Tiêu đề thân thiện hiển thị trên kết quả tìm kiếm; nên dài 50–60 ký tự." />
                                    <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta Description" wire:model.blur="meta_description.{{ $locale }}" rows="3" maxlength="500" helper="Tóm tắt nội dung trang; nên dài 120–160 ký tự." />
                                </div>

                                <div class="snippet-preview news-settings-snippet">
                                    <small>Xem trước kết quả tìm kiếm</small>
                                    <strong>{{ $seo_title[$locale] ?: ($page_title[$locale] ?: 'Tin tức IDI Seafood') }}</strong>
                                    <span>idiseafood.com/{{ $locale }}/{{ $locale === 'vi' ? 'tin-tuc' : ($locale === 'en' ? 'news' : 'xinwen') }}</span>
                                    <p>{{ $meta_description[$locale] ?: ($description[$locale] ?: 'Thông tin mới nhất từ IDI Seafood sẽ hiển thị tại đây.') }}</p>
                                </div>

                                <details class="settings-advanced news-social-panel">
                                    <summary>Chia sẻ mạng xã hội</summary>
                                    <div class="settings-field-list">
                                        <x-form.input name="og_title[{{ $locale }}]" label="Tiêu đề chia sẻ" wire:model.blur="og_title.{{ $locale }}" maxlength="255" helper="Để trống để dùng Friendly Title." />
                                        <x-form.textarea name="og_description[{{ $locale }}]" label="Mô tả chia sẻ" wire:model.blur="og_description.{{ $locale }}" rows="3" maxlength="500" helper="Để trống để dùng Meta Description." />
                                    </div>
                                </details>
                            </section>
                        @endforeach
                    </x-form.language-tabs>

                    <div class="switch-group news-settings-switch-list settings-footer-group">
                        <x-form.switch name="rebuild_seo_links" label="Rebuild SEO Link" helper="Đánh dấu để hệ thống làm mới liên kết SEO trong lần xử lý tiếp theo." wire:model="rebuild_seo_links" />
                    </div>
                </x-form.section>
            </section>

            <section id="settings-panel-images" role="tabpanel" aria-labelledby="settings-tab-images"
                x-show="activeTab === 'images'" x-cloak>
                <x-form.section title="Hình ảnh Tin tức" description="Chỉ giữ các thiết lập hình ảnh thường xuyên cần điều chỉnh." icon="image">
                    <div class="settings-field-list">
                        <div class="settings-inline-fields">
                            <span><strong>Kích thước Thumbnail</strong><small>Ảnh đại diện dùng ở danh sách và các khối tin.</small></span>
                            <div>
                                <x-form.input name="thumbnail_size" label="Chiều rộng (px)" type="number" wire:model="thumbnail_size" min="100" max="2000" />
                                <x-form.input name="thumbnail_height" label="Chiều cao (px)" type="number" wire:model="thumbnail_height" min="100" max="2000" />
                            </div>
                        </div>
                        <div class="settings-inline-fields">
                            <span><strong>Kích thước ảnh chi tiết</strong><small>Kích thước khuyến nghị cho ảnh đầu bài viết.</small></span>
                            <div>
                                <x-form.input name="detail_image_width" label="Chiều rộng (px)" type="number" wire:model="detail_image_width" min="320" max="5000" />
                                <x-form.input name="detail_image_height" label="Chiều cao (px)" type="number" wire:model="detail_image_height" min="180" max="5000" />
                            </div>
                        </div>
                        <x-form.input name="image_quality" label="Chất lượng ảnh (%)" type="number" wire:model="image_quality" min="40" max="100" helper="Khuyến nghị 80–90% để cân bằng chất lượng và tốc độ tải." />
                    </div>
                    <div class="switch-group news-settings-switch-list settings-footer-group">
                        <x-form.switch name="show_placeholder_image" label="Ảnh mặc định" helper="Dùng ảnh thay thế khi bài viết chưa có ảnh đại diện." wire:model="show_placeholder_image" />
                        <x-form.switch name="crop_images" label="Crop ảnh" helper="Tự cắt ảnh theo đúng tỷ lệ thay vì làm méo ảnh." wire:model="crop_images" />
                        <x-form.switch name="watermark_enabled" label="Watermark" helper="Gắn dấu nhận diện lên ảnh sau khi xử lý." wire:model="watermark_enabled" />
                    </div>
                </x-form.section>
            </section>

            <section id="settings-panel-display" role="tabpanel" aria-labelledby="settings-tab-display"
                x-show="activeTab === 'display'" x-cloak>
                <div class="settings-panel-grid settings-panel-two-columns">
                    <x-form.section title="Trang chủ" description="Điều chỉnh các nhóm tin xuất hiện trên trang chủ." icon="home">
                        <div class="settings-field-list">
                            <x-form.input name="featured_limit" label="Tin nổi bật" type="number" wire:model="featured_limit" min="1" max="20" helper="Số bài trong khu vực tin nổi bật (Featured News)." />
                            <x-form.input name="items_per_page" label="Tin mới" type="number" wire:model="items_per_page" min="1" max="100" helper="Số bài mới nhất (Latest News) trên mỗi trang." />
                        </div>
                        <div class="switch-group news-settings-switch-list settings-footer-group">
                            <x-form.switch name="show_featured_section" label="Hiển thị Tin nổi bật" wire:model="show_featured_section" />
                        </div>
                    </x-form.section>

                    <x-form.section title="Danh mục" description="Thiết lập danh sách bài viết trong từng danh mục." icon="folder">
                        <div class="settings-field-list">
                            <x-form.input name="category_items_per_page" label="Số bài mỗi trang" type="number" wire:model="category_items_per_page" min="1" max="100" />
                            <x-form.input name="archive_items_per_page" label="Số bài trang lưu trữ" type="number" wire:model="archive_items_per_page" min="1" max="100" helper="Áp dụng cho trang lưu trữ theo thời gian." />
                        </div>
                        <div class="switch-group news-settings-switch-list settings-footer-group">
                            <x-form.switch name="show_category_navigation" label="Hiển thị điều hướng danh mục" helper="Giúp người đọc chuyển nhanh giữa Tin nổi bật và Tin mới." wire:model="show_category_navigation" />
                        </div>
                    </x-form.section>

                    <x-form.section title="Trang chi tiết" description="Chọn thông tin và tiện ích đi kèm mỗi bài viết." icon="newspaper" class="settings-wide-card">
                        <div class="switch-group news-settings-switch-list settings-switch-columns">
                            <x-form.switch name="show_author" label="Hiển thị tác giả" wire:model="show_author" />
                            <x-form.switch name="show_published_date" label="Hiển thị ngày đăng" wire:model="show_published_date" />
                            <x-form.switch name="show_view_count" label="Hiển thị lượt xem" wire:model="show_view_count" />
                            <x-form.switch name="show_reading_time" label="Hiển thị thời gian đọc" wire:model="show_reading_time" />
                            <x-form.switch name="show_tags" label="Hiển thị Tags" wire:model="show_tags" />
                            <x-form.switch name="show_article_source" label="Hiển thị Nguồn bài viết" wire:model="show_article_source" />
                            <x-form.switch name="show_breadcrumb" label="Hiển thị Breadcrumb" helper="Hiển thị đường dẫn phân cấp phía trên bài viết." wire:model="show_breadcrumb" />
                            <x-form.switch name="show_social_share" label="Hiển thị chia sẻ mạng xã hội" wire:model="show_social_share" />
                            <x-form.switch name="show_previous_next" label="Hiển thị bài trước/sau" wire:model="show_previous_next" />
                            <x-form.switch name="show_related_articles" label="Hiển thị bài liên quan" wire:model="show_related_articles" />
                        </div>
                        <div class="settings-field-list settings-footer-group">
                            <x-form.input name="related_limit" label="Số bài liên quan" type="number" wire:model="related_limit" min="0" max="30" helper="Nhập 0 để không giới hạn bằng cấu hình." />
                        </div>
                    </x-form.section>
                </div>
            </section>

            <section id="settings-panel-upload" role="tabpanel" aria-labelledby="settings-tab-upload"
                x-show="activeTab === 'upload'" x-cloak>
                <x-form.section title="Tải tệp lên" description="Giới hạn tệp được dùng trong bài viết để bảo đảm an toàn và dung lượng." icon="upload">
                    <div class="settings-field-list">
                        <x-form.input name="max_upload_size" label="Dung lượng upload tối đa (MB)" type="number" wire:model="max_upload_size" min="1" max="100" helper="Giới hạn cho mỗi tệp tải lên." />
                        <x-form.input name="allowed_file_types" label="Định dạng file cho phép" wire:model.blur="allowed_file_types" maxlength="255" helper="Nhập phần mở rộng, phân tách bằng dấu phẩy. Ví dụ: jpg, jpeg, png, webp." />
                    </div>
                    <div class="switch-group news-settings-switch-list settings-footer-group">
                        <x-form.switch name="auto_rename_files" label="Tự đổi tên file" helper="Chuẩn hóa tên tệp để tránh trùng lặp và ký tự không hợp lệ." wire:model="auto_rename_files" />
                        <x-form.switch name="allow_webp" label="Cho phép WebP" helper="Định dạng ảnh nhẹ, phù hợp cho website hiện đại." wire:model="allow_webp" />
                        <x-form.switch name="allow_svg" label="Cho phép SVG" helper="Chỉ bật khi hệ thống có bước kiểm tra nội dung SVG an toàn." wire:model="allow_svg" />
                    </div>
                </x-form.section>
            </section>

            <section id="settings-panel-comments" role="tabpanel" aria-labelledby="settings-tab-comments"
                x-show="activeTab === 'comments'" x-cloak>
                <x-form.section title="Bình luận" description="Kiểm soát thảo luận bài viết và hạn chế nội dung không mong muốn." icon="users">
                    <div class="switch-group news-settings-switch-list">
                        <x-form.switch name="allow_comments" label="Bật bình luận" helper="Cho phép người đọc tham gia thảo luận bài viết." wire:model="allow_comments" />
                        <x-form.switch name="moderate_comments" label="Kiểm duyệt bình luận" helper="Bình luận cần được duyệt trước khi hiển thị công khai." wire:model="moderate_comments" />
                        <x-form.switch name="comment_spam_protection" label="Chống Spam" helper="Áp dụng các biện pháp hạn chế bình luận tự động và lặp lại." wire:model="comment_spam_protection" />
                    </div>
                </x-form.section>
            </section>

            <section id="settings-panel-performance" role="tabpanel" aria-labelledby="settings-tab-performance"
                x-show="activeTab === 'performance'" x-cloak>
                <x-form.section title="Hiệu năng" description="Tối ưu tốc độ tải trang và khả năng được công cụ tìm kiếm thu thập." icon="chart">
                    <div class="switch-group news-settings-switch-list settings-switch-columns">
                        <x-form.switch name="cache_homepage" label="Cache Homepage" helper="Lưu tạm dữ liệu trang chủ Tin tức." wire:model="cache_homepage" />
                        <x-form.switch name="cache_category" label="Cache Category" helper="Lưu tạm dữ liệu trang danh mục." wire:model="cache_category" />
                        <x-form.switch name="cache_detail" label="Cache Detail" helper="Lưu tạm nội dung trang chi tiết bài viết." wire:model="cache_detail" />
                        <x-form.switch name="lazy_load_images" label="Lazy Load ảnh" helper="Chỉ tải ảnh khi gần xuất hiện trong vùng nhìn." wire:model="lazy_load_images" />
                        <x-form.switch name="performance_webp" label="WebP" helper="Ưu tiên phiên bản WebP khi trình duyệt hỗ trợ." wire:model="performance_webp" />
                        <x-form.switch name="sitemap_enabled" label="Sitemap" helper="Đưa bài viết công khai vào sitemap của website." wire:model="sitemap_enabled" />
                    </div>
                </x-form.section>
            </section>
        </div>

        <div class="mobile-form-actions">
            <x-ui.button type="submit" icon="save">Lưu cấu hình</x-ui.button>
        </div>
    </form>
</div>
