<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdditionalFishFeedbackSurveySeeder extends Seeder
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

        $title = '追加アンケートのお願い';
        $description = "9月14日のイベントへのご参加と、アンケートへのご回答、誠にありがとうございました。\n\nお持ち帰りいただいた魚を、ご自身のお店で加工・調理されたご感想も、ぜひお聞かせください。\n皆さまのお声を励みに、今後の商品づくりやサービスの改善に生かしてまいります。\n\nご協力のほど、よろしくお願いいたします。";

        $form = Form::where('user_id', $user->id)
            ->whereIn('title', [$title])
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

        $questions = [
            ['type' => 'checkbox', 'title' => '① 価格が合えば、お店で使ってみたい魚を教えてください。（複数選択可）', 'is_required' => false, 'options' => [
                '【天然】甘鯛', '【天然】のどぐろ', '【天然】剣先イカ', '【天然】真穴子', '【天然】煮穴子',
                '【養殖】タイ', '【養殖】シマアジ', '【養殖】ヒラメ',
                '特になし',
            ]],

            ['type' => 'checkbox', 'title' => '② その他、興味のある魚を教えてください。（複数選択可）', 'is_required' => false, 'options' => [
                'アジ', 'サバ', 'キダイ', 'カツオ', 'ブリ', 'マグロ', 'ハマチ', 'クエ', '鮎', 'うなぎ', 'ハモ', 'その他',
            ]],

            ['type' => 'section', 'title' => '③ お持ち帰りいただいた魚で、どのような料理を作りましたか？ ご感想もお聞かせください。', 'is_required' => false, 'options' => null],
            ['type' => 'text', 'title' => '使用した魚', 'is_required' => false, 'options' => null],
            ['type' => 'text', 'title' => '作った料理', 'is_required' => false, 'options' => null],
            ['type' => 'textarea', 'title' => 'ご感想（味・食感・扱いやすさなど）', 'is_required' => false, 'options' => null],

            ['type' => 'section', 'title' => '④ サブスク定期便をご利用になる場合、ご希望を教えてください。', 'is_required' => false, 'options' => null],
            ['type' => 'radio', 'title' => '1箱に入れる魚の種類数', 'is_required' => false, 'options' => ['5種類', '6種類', '7種類', '8種類', 'その他']],
            ['type' => 'text', 'title' => '1箱あたりの希望総重量（数値と単位をご記入ください　例：3kg／5ポンド）', 'is_required' => false, 'options' => null],
            ['type' => 'radio', 'title' => '鮮魚ボックス1箱あたりの希望価格（米ドル）', 'is_required' => false, 'options' => ['100ドル', '200ドル', '300ドル', '400ドル', 'その他']],
            ['type' => 'radio', 'title' => '配達頻度', 'is_required' => false, 'options' => ['週1回', '週2回', '週3回', 'その他']],

            ['type' => 'textarea', 'title' => '⑤ その他、ご意見・ご要望がございましたら、自由にお聞かせください。', 'is_required' => false, 'options' => null],

            ['type' => 'text', 'title' => '店舗名', 'is_required' => false, 'options' => null],
            ['type' => 'text', 'title' => 'お名前', 'is_required' => false, 'options' => null],
        ];

        foreach ($questions as $order => $question) {
            $form->questions()->updateOrCreate(['order' => $order], $question);
        }

        $form->questions()->where('order', '>=', count($questions))->delete();
    }
}
