<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Seeder;

class VisitSatisfactionSurveySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );

        $scale = ['とても良い', '良い', '普通', 'あまり良くない', '良くない'];

        $form = Form::create([
            'user_id' => $user->id,
            'title' => 'ご来社アンケート',
            'description' => 'この度はご来社いただき誠にありがとうございました。今後のサービス向上のため、以下のアンケートにご協力をお願いいたします。',
            'is_published' => true,
        ]);

        $questions = [
            ['type' => 'radio', 'title' => '1．受付やお声がけの対応はいかがでしたか？', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '2．担当者の応対はいかがでしたか', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '3．事務所の場所や入口は分かりやすかったですか？', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '4．事務所内の清潔感はいかがでしたか？', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '5．事務所内の雰囲気や過ごしやすさはいかがでしたか？', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '6．今回のご来社について、全体的にどの程度ご満足いただけましたか？', 'is_required' => true, 'options' => $scale],
            ['type' => 'textarea', 'title' => '7．お気づきの点やご意見がございましたら、ご自由にご記入ください。', 'is_required' => false, 'options' => null],
        ];

        foreach ($questions as $order => $question) {
            $form->questions()->create([...$question, 'order' => $order]);
        }
    }
}
