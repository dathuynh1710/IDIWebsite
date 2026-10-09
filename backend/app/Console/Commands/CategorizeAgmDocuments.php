<?php

namespace App\Console\Commands;

use App\Models\DocumentCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CategorizeAgmDocuments extends Command
{
    protected $signature = 'investors:categorize-agm {--apply : Apply the verified category assignments}';

    protected $description = 'Move root AGM documents into existing year categories using the document year';

    public function handle(): int
    {
        return DB::transaction(function (): int {
            $root = DocumentCategory::whereNull('parent_id')->where('slug->vi', 'dai-hoi-co-dong')->first();
            if (! $root) {
                $this->error('Không tìm thấy danh mục gốc Đại hội cổ đông.');

                return self::FAILURE;
            }
            $documents = $root->documents()->lockForUpdate()->get();
            $children = $root->children()->get();
            $assignments = [];
            foreach ($documents->groupBy('year') as $year => $items) {
                $slug = 'dai-hoi-co-dong-nam-'.$year;
                $matches = $children->filter(fn ($child) => $child->getTranslation('slug', 'vi', false) === $slug);
                if (! preg_match('/^(19|20)\d{2}$/', (string) $year) || $matches->count() !== 1) {
                    $this->error("Năm '{$year}' chưa có danh mục đích duy nhất. Chưa chuyển tài liệu nào.");

                    return self::FAILURE;
                }
                $target = $matches->first();
                $assignments[] = [$items->modelKeys(), $target->id];
                $this->line("{$year}: {$items->count()} tài liệu → #{$target->id} {$slug}");
            }
            if ($this->option('apply')) {
                foreach ($assignments as [$ids, $categoryId]) {
                    DB::table('investor_documents')->whereIn('id', $ids)->where('document_category_id', $root->id)
                        ->update(['document_category_id' => $categoryId, 'updated_at' => now()]);
                }
            }
            $this->info(($this->option('apply') ? 'Đã chuyển: ' : 'Dự kiến chuyển: ').$documents->count().' tài liệu.');

            return self::SUCCESS;
        });
    }
}
