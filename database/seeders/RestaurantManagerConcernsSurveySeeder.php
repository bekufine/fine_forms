<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Seeder;

class RestaurantManagerConcernsSurveySeeder extends Seeder
{
    private const YES_NO = ['はい', 'いいえ'];

    /** «その他» открывает поле для свободного ввода числа. */
    private const HEADCOUNT = ['1〜5', '6〜15', '16〜40', 'その他'];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => config('services.admin.email')],
            [
                'name' => config('services.admin.name'),
                'password' => bcrypt(config('services.admin.password')),
            ]
        );

        $title = '人手不足に悩む飲食店店長の本音50選';
        $description = "店長だからこそ抱える悩み、いくつ当てはまりますか？\n現場の本音を教えてください。";

        $form = Form::where('user_id', $user->id)
            ->where('title', $title)
            ->first();

        if ($form) {
            $form->update([
                'title' => $title,
                'description' => $description,
                'is_published' => true,
            ]);
        } else {
            $form = Form::create([
                'user_id' => $user->id,
                'title' => $title,
                'description' => $description,
                'is_published' => true,
            ]);
        }

        // Заголовок раздела, затем его вопросы. Нумерация 1–50 сквозная,
        // как в исходной листовке.
        $sections = [
            '01　募集・採用の悩み' => [
                '求人広告費が高い。',
                '求人を出しても応募が来ない。',
                '時給を上げないと人が集まらない。',
                '応募があっても勤務条件が合わない。',
                '面接に時間を取られる。',
                '面接の無断キャンセルがある。',
                '採用が決まっても初出勤前に辞退される。',
                '採用権限は本社にある。',
            ],
            '02　研修・教育・定着の悩み' => [
                '新人教育に時間がかかる。',
                '新人を教える人手さえ足りない。',
                '仕事に慣れた頃に辞めてしまう。',
                'スタッフによって能力に差がある。',
                '教える人によって教育内容が違う。',
                'できるスタッフに仕事が集中する。',
                '新人ばかりで接客品質が安定しない。',
            ],
            '03　学生アルバイト・シフト管理の悩み' => [
                '今の学生は真面目に勉強する。',
                'テスト期間に休みが集中する。',
                '就職活動や実習で長期間休む。',
                '学生のシフト提出が遅い。',
                '一度決めたシフトの変更が多い。',
                '週1回しか働けないスタッフが多い。',
                '土日・祝日に出勤できる人が少ない。',
                '旅行・帰省・長期休暇が重なる。',
                '急な欠勤の穴埋めができない。',
            ],
            '04　日雇いなど単発人材サービスへの不安' => [
                '来る人によって当たり外れがある。',
                '無断欠勤が怖い。',
                '直前キャンセルが心配。',
                '必要な日に人が集まるとは限らない。',
                '毎回、仕事を一から教えるのが大変。',
                '身だしなみが店舗の基準に合わない。',
                '接客態度が心配。',
                'Googleの悪い口コミが怖い。',
                '毎回違う人では固定メンバーが育たない。',
            ],
            '05　外国人スタッフ採用の悩み' => [
                '日本語の壁がある。',
                '日本の接客マナーを教えるのが大変。',
                '料理や食材の説明が難しい。',
                '文化・宗教・食習慣への配慮が必要。',
                '在留資格・就労時間の確認が難しい。',
                '外国人留学生の勤務時間の管理が大変。',
                '英語で接客できるスタッフが欲しい。',
                '真面目に働く外国人スタッフをもっと採用したい。',
            ],
            '06　店舗運営・ピークタイムの悩み' => [
                'ピーク時にレジ対応が追いつかない。',
                '調理人が足りず、料理が出てこない。',
                '料理を作っても運ぶ人がいない。',
                '仕込み・調理補助の人手が足りない。',
                '洗い場が追いつかない。',
                '注文や会計の対応が追いつかない。',
                '人手不足で予約や新規のお客様を断っている。',
            ],
            '07　店長・店舗経営の悩み' => [
                '人手不足で店長自身が休めない。',
                '人集めとシフト管理に追われ、本来の仕事ができない。',
            ],
        ];

        // Вступительный блок: идёт до нумерованных вопросов, поэтому
        // сквозная нумерация 1–50 его не затрагивает.
        $questions = [
            ['type' => 'text', 'title' => '店名', 'is_required' => true, 'options' => null],
            ['type' => 'text', 'title' => 'ポジション', 'is_required' => true, 'options' => null],
            ['type' => 'select', 'title' => '店員数', 'is_required' => true, 'options' => self::HEADCOUNT],
            ['type' => 'select', 'title' => '不足している人数', 'is_required' => true, 'options' => self::HEADCOUNT],
        ];

        $number = 0;

        foreach ($sections as $heading => $items) {
            $questions[] = [
                'type' => 'section',
                'title' => $heading,
                'is_required' => false,
                'options' => null,
            ];

            foreach ($items as $item) {
                $questions[] = [
                    'type' => 'radio',
                    'title' => sprintf('%d. %s', ++$number, $item),
                    'is_required' => true,
                    'options' => self::YES_NO,
                ];
            }
        }

        foreach ($questions as $order => $question) {
            $form->questions()->updateOrCreate(['order' => $order], $question);
        }

        $form->questions()->where('order', '>=', count($questions))->delete();
    }
}
