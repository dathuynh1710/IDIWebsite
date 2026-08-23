<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvestorDocumentCategorySeeder extends Seeder
{
    /**
     * The legacy IDI taxonomy shown in the investor-relations CMS.
     *
     * @var array<int, array<string, mixed>>
     */
    private const CATEGORIES = [
        ['slug' => 'trai-phieu', 'vi' => 'Trái phiếu', 'en' => 'Bonds', 'zh' => '债券', 'sort' => 80],
        ['slug' => 'ban-cao-bach', 'vi' => 'Bản cáo bạch', 'en' => 'Prospectus', 'zh' => '招股说明书', 'sort' => 70],
        [
            'slug' => 'dai-hoi-co-dong', 'vi' => 'Đại hội cổ đông', 'en' => 'Shareholders Meeting', 'zh' => '股东大会', 'sort' => 60,
            'years' => [
                ['year' => 2026, 'suffix' => 'moi'],
                2026, 2025, 2024, 2023, 2022, 2021, 2020, 2019, 2018, 2017, 2016, 2015, 2014, 2013, 2012, 2011,
            ],
        ],
        [
            'slug' => 'quy-che-noi-bo-quan-tri-cong-ty', 'vi' => 'Quy chế nội bộ về quản trị công ty', 'en' => 'Internal governance regulations', 'zh' => '公司内部治理规章', 'sort' => 50,
            'years' => [2024],
        ],
        [
            'slug' => 'dieu-le-cong-ty', 'vi' => 'Điều lệ công ty', 'en' => 'Company charter', 'zh' => '公司章程', 'sort' => 40,
            'years' => [2024, 2022, 2021, 2020, 2016, 2015, 2014, 2013],
        ],
        ['slug' => 'thong-bao', 'vi' => 'Thông báo', 'en' => 'Notification', 'zh' => '公告', 'sort' => 30],
        [
            'slug' => 'bao-cao-thuong-nien', 'vi' => 'Báo cáo thường niên', 'en' => 'Annual Report', 'zh' => '年度报告', 'sort' => 20,
            'years' => [
                ['year' => 2025, 'suffix' => 'moi'],
                2025, 2024, 2023, 2022, 2021, 2020, 2019, 2018, 2017, 2016, 2015, 2014, 2013, 2012, 2011,
            ],
        ],
        [
            'slug' => 'bao-cao-tai-chinh', 'vi' => 'Báo cáo tài chính', 'en' => 'Financial Report', 'zh' => '财务报告', 'sort' => 10,
            'years' => [2026, 2025, 2024, 2023, 2022, 2021, 2020, 2019, 2018, 2017, 2016, 2015, 2014, 2013, 2012, 2011, 2010, 2009],
        ],
    ];

    public function run(): void
    {
        $adminId = DB::table('users')->orderBy('id')->value('id');

        foreach (self::CATEGORIES as $definition) {
            $parent = $this->upsertCategory(
                slug: $definition['slug'],
                parentId: null,
                name: ['vi' => $definition['vi'], 'en' => $definition['en'], 'zh' => $definition['zh']],
                sortOrder: $definition['sort'],
                adminId: $adminId,
            );

            foreach ($definition['years'] ?? [] as $position => $yearDefinition) {
                $year = is_array($yearDefinition) ? $yearDefinition['year'] : $yearDefinition;
                $suffix = is_array($yearDefinition) ? '-'.$yearDefinition['suffix'] : '';

                $this->upsertCategory(
                    slug: $definition['slug'].'-nam-'.$year.$suffix,
                    parentId: $parent->id,
                    name: ['vi' => "Năm {$year}", 'en' => "Year {$year}", 'zh' => "{$year} 年"],
                    sortOrder: 100 - $position,
                    adminId: $adminId,
                );
            }
        }
    }

    /** @param array<string, string> $name */
    private function upsertCategory(string $slug, ?int $parentId, array $name, int $sortOrder, ?int $adminId): DocumentCategory
    {
        $category = DocumentCategory::withTrashed()->where('slug->vi', $slug)->first() ?? new DocumentCategory;

        $category->fill([
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => ['vi' => $slug, 'en' => $slug, 'zh' => $slug],
            'sort_order' => $sortOrder,
            'is_active' => true,
            'created_by' => $category->created_by ?? $adminId,
            'updated_by' => $adminId,
        ]);
        $category->deleted_at = null;
        $category->save();

        return $category;
    }
}
