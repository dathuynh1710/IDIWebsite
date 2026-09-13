<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MEDIA_GROUPS = [
        [
            'from' => 'about',
            'to' => 'media/about',
            'files' => ['avatar.jpg'],
        ],
        [
            'from' => 'products/reference',
            'to' => 'media/products',
            'files' => [
                'dm2.jpg',
                'VDS_con_da_min_1.jpg',
                'Bo_da_de_EU_min.jpg',
                'VDS_con_thit_do_min.jpg',
                'Xong_CO_min.jpg',
                'Con_da_con_de_min.jpg',
                'dm3.jpg',
                'Cat_khuc_min.jpg',
                'Cat_mieng_vuong_min.jpg',
                'dm4.jpg',
                'Nguyen_con_xe_buom_min.jpg',
                'Hoa_hong_min.jpg',
                'dm5.jpg',
                'dm6.jpg',
            ],
        ],
        [
            'from' => 'recipes/images',
            'to' => 'media/recipes',
            'files' => [
                'mon_an.png',
                'CATALOGUE_2020.png',
                'z5820210877220_2b747df29a0a8e335636b84c68da6333.jpg',
                'z5820210889242_94f642a81fede0ba23b8007c1d111eb8.jpg',
                'z5820210882109_c463c04d65301d28e7518e5e51761803.jpg',
                'z5820211493047_a352e685c6713e8f10815070b8bdfabe.jpg',
                'z5820210887364_5091a4dd979800ffa9bf36571b71889b.jpg',
                'CATALOGUE_2020_1.png',
            ],
        ],
        [
            'from' => 'news/idi-source',
            'to' => 'media/news',
            'files' => [
                'SDAW25_LOGO_WIN_GBOTY_CAPAC.jpg',
                'Thumb_dang_bai_idi.jpg',
                'z6122228610437_a23bd95445e225017c694fcf4e5d247f.jpg',
                'aDSC04488.jpg',
            ],
        ],
        [
            'from' => 'documents',
            'to' => 'documents',
            'files' => ['annual-report-2025.pdf'],
        ],
    ];

    private const RICH_TEXT_ASSETS = [
        'gt1.jpg' => '/assets/media/about/values/gt1.jpg',
        'congnhan.png' => '/assets/media/about/values/congnhan.png',
        'gt3.jpg' => '/assets/media/about/values/gt3.jpg',
        'gt4.jpg' => '/assets/media/about/values/gt4.jpg',
        '4z6079751883555_a57bc70602549e4b733727ea95391927.jpg' => '/assets/media/news/4z6079751883555_a57bc70602549e4b733727ea95391927.jpg',
        '7z6079816528177_fc866584d6c64b838bff000c86fea749.jpg' => '/assets/media/news/7z6079816528177_fc866584d6c64b838bff000c86fea749.jpg',
    ];

    public function up(): void
    {
        foreach (self::MEDIA_GROUPS as $group) {
            DB::table('media')
                ->whereIn('file_name', $group['files'])
                ->whereIn('directory', array_unique([$group['from'], $group['to']]))
                ->update([
                    'disk' => 'public_assets',
                    'directory' => $group['to'],
                    'external_url' => null,
                ]);
        }

        $this->localizeRichText('pages', 'content');
        $this->localizeRichText('posts', 'content');
    }

    public function down(): void
    {
        // The committed local assets remain valid after a rollback. Restoring
        // remote dependencies would make the application less reliable.
    }

    private function localizeRichText(string $table, string $column): void
    {
        DB::table($table)
            ->select(['id', $column])
            ->whereNotNull($column)
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($table, $column): void {
                foreach ($rows as $row) {
                    $localized = $this->localizeValue($row->{$column});
                    if ($localized !== $row->{$column}) {
                        DB::table($table)->where('id', $row->id)->update([$column => $localized]);
                    }
                }
            });
    }

    private function localizeValue(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return json_encode(
                $this->localizeArray($decoded),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        return $this->localizeString($value);
    }

    private function localizeArray(array $values): array
    {
        foreach ($values as $key => $value) {
            $values[$key] = is_array($value)
                ? $this->localizeArray($value)
                : $this->localizeString($value);
        }

        return $values;
    }

    private function localizeString(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        foreach (self::RICH_TEXT_ASSETS as $fileName => $localPath) {
            $value = preg_replace(
                '~https?://[^"\'\s<>]+/'.preg_quote($fileName, '~').'~i',
                $localPath,
                $value
            ) ?? $value;
        }

        return $value;
    }
};
