<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * One-off utility: swaps the ids of two specific forms (and all of their
 * questions/responses/translations along with them), looked up by title so
 * it works regardless of what id they currently hold — dev/staging/prod
 * all seed independently and can end up with different ids for the same
 * form.
 *
 * Not wired into DatabaseSeeder's default run() — run manually once:
 *   php artisan db:seed --class=SwapFormIdsSeeder --force
 *
 * Running it twice swaps them back, same as swapping any two values twice —
 * only run it once per environment.
 */
class SwapFormIdsSeeder extends Seeder
{
    private const TITLE_A = 'ご来社アンケート(営業所向け)';

    private const TITLE_B_LIKE = '%大規模輸出産地モデル形成等支援事業%';

    public function run(): void
    {
        $formA = Form::where('title', self::TITLE_A)->first();
        $formB = Form::where('title', 'like', self::TITLE_B_LIKE)->first();

        if (! $formA || ! $formB) {
            $this->command?->error('Could not find both forms. A: '.($formA ? $formA->id : 'NOT FOUND').', B: '.($formB ? $formB->id : 'NOT FOUND'));

            return;
        }

        $idA = $formA->id;
        $idB = $formB->id;

        if ($idA === $idB) {
            $this->command?->info("Forms already share the same id ({$idA}), nothing to do.");

            return;
        }

        $this->command?->info("Swapping form id {$idA} <-> {$idB}");

        $driver = DB::getDriverName();
        $temp = 999999;
        $tables = ['questions', 'responses', 'form_translations'];

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        DB::transaction(function () use ($idA, $idB, $temp, $tables) {
            foreach ($tables as $table) {
                DB::table($table)->where('form_id', $idA)->update(['form_id' => $temp]);
            }
            DB::table('forms')->where('id', $idA)->update(['id' => $temp]);

            foreach ($tables as $table) {
                DB::table($table)->where('form_id', $idB)->update(['form_id' => $idA]);
            }
            DB::table('forms')->where('id', $idB)->update(['id' => $idA]);

            foreach ($tables as $table) {
                DB::table($table)->where('form_id', $temp)->update(['form_id' => $idB]);
            }
            DB::table('forms')->where('id', $temp)->update(['id' => $idB]);
        });

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->command?->info('Done.');
    }
}
