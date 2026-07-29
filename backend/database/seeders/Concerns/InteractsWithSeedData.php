<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;

trait InteractsWithSeedData
{
    /**
     * @param  array<string, mixed>  $match
     * @param  array<string, mixed>  $values
     */
    protected function upsertId(string $table, array $match, array $values): int
    {
        $query = DB::table($table)->where($match);
        $id = $query->value('id');

        if ($id !== null) {
            $query->update(array_merge($values, ['updated_at' => now()]));

            return (int) $id;
        }

        return (int) DB::table($table)->insertGetId(array_merge(
            $match,
            $values,
            ['created_at' => now(), 'updated_at' => now()]
        ));
    }

    /**
     * @param  array<string, mixed>  $match
     * @param  array<string, mixed>  $values
     */
    protected function upsertPivot(string $table, array $match, array $values = []): void
    {
        DB::table($table)->updateOrInsert(
            $match,
            array_merge($values, ['updated_at' => now(), 'created_at' => now()])
        );
    }

    /**
     * Upsert a row identified by one translated JSON value.
     *
     * @param  array<string, mixed>  $values
     */
    protected function upsertJsonId(
        string $table,
        string $column,
        string $locale,
        string $translatedValue,
        array $values
    ): int {
        $query = DB::table($table)->where("{$column}->{$locale}", $translatedValue);
        $id = $query->value('id');

        if ($id !== null) {
            $query->update(array_merge($values, ['updated_at' => now()]));

            return (int) $id;
        }

        return (int) DB::table($table)->insertGetId(array_merge(
            $values,
            ['created_at' => now(), 'updated_at' => now()]
        ));
    }

    /**
     * @param  array<string, mixed>  $value
     */
    protected function json(array $value): string
    {
        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    }

    protected function translations(string $vi, string $en, string $zh): string
    {
        return $this->json(compact('vi', 'en', 'zh'));
    }
}
