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

        $form = Form::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'ご来社アンケート(営業所向け)'],
            [
                'description' => "本日はご来社いただき、誠にありがとうございました。\n\n今後のサービス向上のため、簡単なアンケートにご協力をお願いいたします。\n回答時間の目安は約1分です。",
                'is_published' => true,
            ]
        );

        $questions = [
            ['type' => 'select', 'title' => '会場を選択してください', 'is_required' => true, 'options' => [
                '関東支社', '中部支社', '天神営業所', '梅田営業所', '神戸営業所', '熊本営業所', '京都営業所',
            ]],
            ['type' => 'radio', 'title' => '1．担当者の対応はいかがでしたか。', 'is_required' => true, 'options' => [
                'とても良い', '良い', '普通', 'あまり良くない', '良くない',
            ]],
            ['type' => 'radio', 'title' => '2．お仕事に関する説明は分かりやすかったですか。', 'is_required' => true, 'options' => [
                'とても分かりやすい', '分かりやすい', '普通', 'やや分かりにくい', '分かりにくい',
            ]],
            ['type' => 'radio', 'title' => '3．会場の雰囲気や清潔感はいかがでしたか。', 'is_required' => true, 'options' => [
                'とても良い', '良い', '普通', 'あまり良くない', '良くない',
            ]],
            ['type' => 'radio', 'title' => '4．今後の流れ（お仕事のご紹介から就業開始まで）は分かりやすかったですか。', 'is_required' => true, 'options' => [
                'とても分かりやすい', '分かりやすい', '普通', 'やや分かりにくい', '分かりにくい',
            ]],
            ['type' => 'radio', 'title' => '5．本日の総合的な満足度を教えてください。', 'is_required' => true, 'options' => [
                'とても満足', '満足', '普通', 'やや不満', '不満',
            ]],
            ['type' => 'textarea', 'title' => '6．お気づきの点やご意見がございましたら、ご自由にお書きください。', 'is_required' => false, 'options' => null],
        ];

        foreach ($questions as $order => $question) {
            $form->questions()->updateOrCreate(['order' => $order], $question);
        }
    }
}
