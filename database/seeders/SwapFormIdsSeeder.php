<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * One-off utility: makes sure two specific forms end up with specific ids,
 * regardless of what id they currently hold in a given environment
 * (dev/staging/prod all seed independently and can diverge).
 *
 * Desired state:
 *   - 'ご来社アンケート(営業所向け)'                 -> id 9
 *   - title containing '大規模輸出産地モデル形成等支援事業' -> id 6
 *
 * Not wired into DatabaseSeeder's default run() — run manually once:
 *   php artisan db:seed --class=SwapFormIdsSeeder --force
 *
 * Idempotent: if both forms already have their target id, it does nothing.
 * Refuses to act if the current ids aren't a clean two-way swap (e.g. one
 * of the target ids is already used by some unrelated third form).
 */
class SwapFormIdsSeeder extends Seeder
{
    private const TARGETS = [
        ['match' => 'ご来社アンケート(営業所向け)', 'like' => false, 'id' => 9],
        ['match' => '%大規模輸出産地モデル形成等支援事業%', 'like' => true, 'id' => 6],
    ];

    public function run(): void
    {
        $entries = [];

        foreach (self::TARGETS as $target) {
            $form = $target['like']
                ? Form::where('title', 'like', $target['match'])->first()
                : Form::where('title', $target['match'])->first();

            if (! $form) {
                $this->command?->error("Form not found for pattern: {$target['match']}");

                return;
            }

            $entries[] = ['form' => $form, 'target' => $target['id']];
        }

        [$a, $b] = $entries;

        if ($a['form']->id === $a['target'] && $b['form']->id === $b['target']) {
            $this->command?->info('Both forms already have their target id, nothing to do.');

            return;
        }

        if (! ($a['form']->id === $b['target'] && $b['form']->id === $a['target'])) {
            $this->command?->error(sprintf(
                'Unexpected id layout — form "%s" is id %d (target %d), form "%s" is id %d (target %d). Refusing to guess; fix manually.',
                str_replace("\n", ' ', $a['form']->title), $a['form']->id, $a['target'],
                str_replace("\n", ' ', $b['form']->title), $b['form']->id, $b['target'],
            ));

            return;
        }

        $idA = $a['form']->id;
        $idB = $b['form']->id;

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
