<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesOfficeVisitSurveySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => config('services.admin.email')],
            [
                'name' => config('services.admin.name'),
                'password' => bcrypt(config('services.admin.password')),
            ]
        );

        $description = "本日はご来社いただき、誠にありがとうございました。\n\n今後のサービス向上のため、ぜひ皆さまのお声をお聞かせください。\n\n「スタッフの対応」「会場の雰囲気」「清潔さ」「説明のわかりやすさ」など、本日感じられたことを、Googleマップの口コミにてお聞かせいただけますと幸いです。\n\n簡単なご感想だけでも構いません。\n皆さまの率直なご意見をお待ちしております。";

        $form = Form::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'ご来社アンケート(営業所向け)'],
            [
                'description' => $description,
                'is_published' => true,
            ]
        );

        $form->update(['description' => $description]);

        $questions = [
            ['type' => 'select', 'title' => '会場を選択してください', 'is_required' => true, 'options' => [
                '関東支社', '中部支社', '天神営業所', '梅田営業所', '神戸営業所', '京都営業所', '熊本営業所',
            ]],
        ];

        foreach ($questions as $order => $question) {
            $form->questions()->updateOrCreate(['order' => $order], $question);
        }

        $form->questions()->where('order', '>=', count($questions))->delete();
    }
}
