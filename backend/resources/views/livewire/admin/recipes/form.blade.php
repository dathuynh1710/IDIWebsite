<div>
    <x-admin.page-header :title="$recipe?->exists ? 'Cập nhật Recipe' : 'Thêm Recipe mới'" description="Biên tập công thức, nguyên liệu, cách làm và SEO cho Tiếng Việt, English và 中文" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.recipes.index') }}" wire:navigate><x-ui.icon name="arrow-left" size="18" /> Quay lại</a>
            @if($recipe?->exists)<a class="button button-ghost" href="{{ route('admin.recipes.preview', $recipe) }}" target="_blank"><x-ui.icon name="eye" size="18" /> Xem trước</a>@endif
            <x-ui.button type="submit" form="recipe-form" icon="save">Lưu Recipe</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="recipe-form" wire:submit="save" data-dirty-form>
        @if($errors->any())<div class="validation-summary" role="alert"><x-ui.icon name="alert" /><div><strong>Vui lòng kiểm tra lại thông tin.</strong><p>Có {{ $errors->count() }} trường cần chỉnh sửa.</p></div></div>@endif
        <div class="product-form-grid recipe-form-grid">
            <aside class="product-form-sidebar">
                <x-form.section title="Thông tin chung" description="Áp dụng cho cả 3 ngôn ngữ" icon="info">
                    <div class="form-stack">
                        <x-form.input name="code" label="Mã quản trị" wire:model.blur="code" placeholder="RECIPE_GRILLED_FISH" />
                        <div class="form-field">
                            <label for="recipe-image">Ảnh đại diện <span>*</span></label>
                            @if($featured_image)<div class="media-preview recipe-media-preview has-image"><img src="{{ $featured_image->temporaryUrl() }}" alt=""></div>
                            @elseif($recipe?->featuredMedia && !$remove_image)<div class="media-preview recipe-media-preview has-image"><img src="{{ $recipe->featuredMedia->url }}" alt=""></div>
                            @else<div class="media-preview recipe-media-preview"><span class="media-placeholder"><x-ui.icon name="image" size="32" /> Ảnh đề nghị 1000 × 600</span></div>@endif
                            <input id="recipe-image" class="input" type="file" wire:model="featured_image" accept=".jpg,.jpeg,.png,.webp">
                            <div wire:loading wire:target="featured_image" class="field-help">Đang tải ảnh...</div>
                            @if($featured_image || ($recipe?->featuredMedia && !$remove_image))<button class="button button-ghost media-remove-button" type="button" wire:click="removeFeaturedImage">Xóa ảnh</button>@endif
                            <x-form.field-error name="featured_image" />
                        </div>
                        <div class="form-field">
                            <label for="recipe-video">Video hướng dẫn</label>
                            @if($video_file)<div class="file-selection"><x-ui.icon name="video" size="18" /> {{ $video_file->getClientOriginalName() }}</div>
                            @elseif($recipe?->videoMedia && !$remove_video)<div class="file-selection"><x-ui.icon name="video" size="18" /> {{ $recipe->videoMedia->original_name }}</div>@endif
                            <input id="recipe-video" class="input" type="file" wire:model="video_file" accept=".mp4,.webm,.mov">
                            <p class="field-help">MP4, WebM hoặc MOV; tối đa 50 MB.</p>
                            @if($video_file || ($recipe?->videoMedia && !$remove_video))<button class="button button-ghost media-remove-button" type="button" wire:click="removeVideo">Xóa video</button>@endif
                            <x-form.field-error name="video_file" />
                        </div>
                        <div class="recipe-meta-grid">
                            <x-form.input name="servings" label="Khẩu phần" wire:model="servings" placeholder="4 người" />
                            <x-form.select name="difficulty" label="Độ khó" :options="$difficulties" wire:model="difficulty" required />
                            <x-form.input name="preparation_time" label="Chuẩn bị (phút)" type="number" wire:model="preparation_time" min="0" />
                            <x-form.input name="cooking_time" label="Nấu (phút)" type="number" wire:model="cooking_time" min="0" />
                        </div>
                        <x-form.input name="sort_order" label="Thứ tự hiển thị" type="number" wire:model="sort_order" min="0" required />
                        <div class="switch-group">
                            <x-form.switch name="is_featured" label="Công thức nổi bật" wire:model="is_featured" />
                            <x-form.switch name="is_active" label="Đang hiển thị" wire:model="is_active" />
                        </div>
                    </div>
                </x-form.section>
            </aside>

            <div class="product-form-content">
                <x-form.language-tabs :locales="$locales">
                    @foreach($locales as $locale => $label)
                        <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                            <x-form.section title="Nội dung {{ $label }}" description="Bản dịch độc lập cho phiên bản {{ $label }}" icon="languages">
                                <div class="localized-fields">
                                    <x-form.input name="title[{{ $locale }}]" label="Tiêu đề" wire:model.blur="title.{{ $locale }}" :required="$locale === 'vi'" />
                                    <div class="form-field"><label for="recipe-slug-{{ $locale }}">Đường dẫn thân thiện</label><div class="slug-input"><span>/{{ $locale }}/{{ $locale === 'vi' ? 'cong-thuc' : ($locale === 'en' ? 'recipes' : 'shipu') }}/</span><input id="recipe-slug-{{ $locale }}" class="input" wire:model.blur="slug.{{ $locale }}"><button type="button" wire:click="generateSlug('{{ $locale }}')">Tạo lại</button></div><x-form.field-error name="slug.{{ $locale }}" /></div>
                                    <x-form.textarea name="summary[{{ $locale }}]" label="Mô tả ngắn" wire:model.blur="summary.{{ $locale }}" rows="4" maxlength="2000" />
                                    <x-form.ckeditor5-editor name="content[{{ $locale }}]" label="Nội dung giới thiệu" :model="'content.'.$locale" :value="$content[$locale] ?? ''" rows="10" />

                                    <section class="recipe-builder">
                                        <div class="recipe-builder-heading">
                                            <div><h3>Nguyên liệu</h3><p>Nhập tên, lượng, đơn vị và ghi chú bằng {{ $label }}.</p></div>
                                            <div class="recipe-builder-actions">
                                                <div class="switch-field recipe-builder-toggle">
                                                    <label for="show-ingredients-{{ $locale }}">
                                                        <input id="show-ingredients-{{ $locale }}" type="checkbox" wire:model.live="show_ingredients">
                                                        <span class="switch-track" aria-hidden="true"><span></span></span>
                                                        <span><strong>Hiển thị</strong></span>
                                                    </label>
                                                </div>
                                                @if($show_ingredients)<button class="button button-secondary" type="button" wire:click="addIngredient"><x-ui.icon name="plus" size="16" /> Thêm nguyên liệu</button>@endif
                                            </div>
                                        </div>
                                        @if($show_ingredients)
                                            <div class="recipe-builder-list">
                                                @foreach($ingredients as $index => $ingredient)
                                                    <article class="recipe-builder-row" wire:key="ingredient-{{ $index }}">
                                                        <span class="recipe-builder-number">{{ $index + 1 }}</span>
                                                        <div class="recipe-ingredient-fields">
                                                            <x-form.input name="ingredients[{{ $index }}][name][{{ $locale }}]" label="Tên nguyên liệu" wire:model.blur="ingredients.{{ $index }}.name.{{ $locale }}" />
                                                            <x-form.input name="ingredients[{{ $index }}][quantity]" label="Số lượng" wire:model.blur="ingredients.{{ $index }}.quantity" />
                                                            <x-form.input name="ingredients[{{ $index }}][unit][{{ $locale }}]" label="Đơn vị" wire:model.blur="ingredients.{{ $index }}.unit.{{ $locale }}" />
                                                            <x-form.input name="ingredients[{{ $index }}][note][{{ $locale }}]" label="Ghi chú" wire:model.blur="ingredients.{{ $index }}.note.{{ $locale }}" />
                                                        </div>
                                                        <button class="icon-button is-danger" type="button" wire:click="removeIngredient({{ $index }})" aria-label="Xóa nguyên liệu"><x-ui.icon name="trash" size="17" /></button>
                                                    </article>
                                                @endforeach
                                            </div>
                                        @endif
                                    </section>

                                    <section class="recipe-builder">
                                        <div class="recipe-builder-heading">
                                            <div><h3>Các bước thực hiện</h3><p>Sắp xếp theo thứ tự từ trên xuống.</p></div>
                                            <div class="recipe-builder-actions">
                                                <div class="switch-field recipe-builder-toggle">
                                                    <label for="show-steps-{{ $locale }}">
                                                        <input id="show-steps-{{ $locale }}" type="checkbox" wire:model.live="show_steps">
                                                        <span class="switch-track" aria-hidden="true"><span></span></span>
                                                        <span><strong>Hiển thị</strong></span>
                                                    </label>
                                                </div>
                                                @if($show_steps)<button class="button button-secondary" type="button" wire:click="addStep"><x-ui.icon name="plus" size="16" /> Thêm bước</button>@endif
                                            </div>
                                        </div>
                                        @if($show_steps)
                                            <div class="recipe-builder-list">
                                                @foreach($steps as $index => $step)
                                                    <article class="recipe-builder-row recipe-step-row" wire:key="step-{{ $index }}">
                                                        <span class="recipe-builder-number">{{ $index + 1 }}</span>
                                                        <x-form.textarea name="steps[{{ $index }}][instruction][{{ $locale }}]" label="Hướng dẫn bước {{ $index + 1 }}" wire:model.blur="steps.{{ $index }}.instruction.{{ $locale }}" rows="3" maxlength="5000" />
                                                        <button class="icon-button is-danger" type="button" wire:click="removeStep({{ $index }})" aria-label="Xóa bước"><x-ui.icon name="trash" size="17" /></button>
                                                    </article>
                                                @endforeach
                                            </div>
                                        @endif
                                    </section>

                                    <details class="seo-panel" open><summary>Search Engine Optimization (SEO)</summary><div class="seo-grid">
                                        <x-form.input name="seo_title[{{ $locale }}]" label="Tiêu đề SEO" wire:model.blur="seo_title.{{ $locale }}" maxlength="255" />
                                        <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta description" wire:model.blur="meta_description.{{ $locale }}" rows="3" maxlength="500" />
                                        <div class="snippet-preview"><small>Xem trước kết quả tìm kiếm</small><strong>{{ $seo_title[$locale] ?: ($title[$locale] ?: 'Tên công thức') }}</strong><span>idiseafood.com/{{ $locale }}/{{ $locale === 'vi' ? 'cong-thuc' : ($locale === 'en' ? 'recipes' : 'shipu') }}/{{ $slug[$locale] ?: 'duong-dan' }}</span><p>{{ $meta_description[$locale] ?: ($summary[$locale] ?: 'Mô tả công thức sẽ hiển thị tại đây.') }}</p></div>
                                    </div></details>
                                </div>
                            </x-form.section>
                        </section>
                    @endforeach
                </x-form.language-tabs>
            </div>
        </div>
        <div class="mobile-form-actions"><a class="button button-secondary" href="{{ route('admin.recipes.index') }}" wire:navigate>Hủy</a><x-ui.button type="submit" icon="save">Lưu Recipe</x-ui.button></div>
    </form>
</div>
