<?php

namespace App\Console\Commands;

use App\Models\DocumentCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CategorizeInvestorDocuments extends Command
{
    protected $signature = 'investors:categorize-by-year {--apply : Apply assignments and create missing year categories}';

    protected $description = 'Organize documents in each investor root category by their document year';

    public function handle(): int
    {
        return DB::transaction(function (): int {
            $plans = [];
            $total = 0;
            foreach (DocumentCategory::whereNull('parent_id')->with('children')->get() as $root) {
                $documents = $root->documents()->lockForUpdate()->get();
                foreach ($documents->groupBy('year') as $year => $items) {
                    if (! preg_match('/^(19|20)\d{2}$/', (string) $year)) {
                        $this->error("Danh mục #{$root->id} có tài liệu thiếu năm hợp lệ. Chưa thay đổi dữ liệu.");

                        return self::FAILURE;
                    }
                    $slug = $root->getTranslation('slug', 'vi', false).'-nam-'.$year;
                    $matches = $root->children->filter(fn ($child) => $child->getTranslation('slug', 'vi', false) === $slug);
                    if ($matches->isEmpty()) {
                        $matches = $root->children->filter(fn ($child) => trim($child->getTranslation('name', 'vi', false)) === 'Năm '.$year);
                    }
                    if ($matches->count() > 1) {
                        $this->error("Danh mục #{$root->id}, năm {$year} có nhiều danh mục đích. Chưa thay đổi dữ liệu.");

                        return self::FAILURE;
                    }
                    $target = $matches->first();
                    // Do not silently hide public documents or reuse a deleted category's URL.
                    if (($target && ! $target->is_active && $items->contains('is_active', true))
                        || (! $target && DocumentCategory::withTrashed()->where('slug->vi', $slug)->exists())) {
                        $this->error("Cần kiểm tra danh mục đích {$slug}. Chưa thay đổi dữ liệu.");

                        return self::FAILURE;
                    }
                    $plans[] = [$root, (int) $year, $items->modelKeys(), $target, $slug];
                    $total += $items->count();
                    $this->line($root->getTranslation('name', 'vi')." / {$year}: {$items->count()} tài liệu".($target ? " → #{$target->id}" : ' → tạo danh mục năm'));
                }
            }
            if ($this->option('apply')) {
                foreach ($plans as [$root, $year, $ids, $target, $slug]) {
                    $target ??= DocumentCategory::create([
                        'parent_id' => $root->id,
                        'name' => ['vi' => "Năm {$year}", 'en' => "Year {$year}", 'zh' => "{$year} 年"],
                        'slug' => ['vi' => $slug, 'en' => $slug, 'zh' => $slug],
                        'sort_order' => $year,
                        'is_active' => $root->is_active,
                        'created_by' => $root->created_by,
                        'updated_by' => $root->updated_by,
                    ]);
                    DB::table('investor_documents')->whereIn('id', $ids)->where('document_category_id', $root->id)
                        ->update(['document_category_id' => $target->id, 'updated_at' => now()]);
                }
            }
            $this->info(($this->option('apply') ? 'Đã chuyển: ' : 'Dự kiến chuyển: ').$total.' tài liệu.');

            return self::SUCCESS;
        });
    }
}
