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
        $description = "9月14日のイベントへのご参加と、アンケートへのご回答、誠にありがとうございました。\n\n今後のサービスづくりの参考に、魚の定期便（サブスク）について、皆さまのご意見をお聞かせください。\n\nお答えいただける項目のみで結構です。\nご協力のほど、よろしくお願いいたします。";

        $form = Form::where('user_id', $user->id)
            ->whereIn('title', ['追加アンケートのお願い', 'お持ち帰りサンプルに関するアンケート', $title])
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
            ['type' => 'section', 'title' => '① ロサンゼルス（LA）で、解凍した魚を冷蔵でお届けする定期便（サブスク）が始まるとしたら、魚の種類数・量・価格・配達頻度について、ご意見をお聞かせください。', 'is_required' => false, 'options' => null],

            ['type' => 'radio', 'title' => '【1箱あたりの魚は何種類がよいと思いますか？】', 'is_required' => false, 'options' => [
                '5種類', '6種類', '7種類', '8種類', 'その他',
            ]],

            ['type' => 'text', 'title' => "【1箱あたりの総重量はどのくらいがよいと思いますか？】\n数値と単位をご記入ください。\n例：3kg／5ポンド", 'is_required' => false, 'options' => null],

            ['type' => 'radio', 'title' => '【1箱あたりの価格はいくらがよいと思いますか？】', 'is_required' => false, 'options' => [
                '100ドル', '150ドル', '200ドル', '250ドル', '300ドル', '350ドル', '400ドル', 'その他',
            ]],

            ['type' => 'radio', 'title' => '【配達頻度はどのくらいがよいと思いますか？】', 'is_required' => false, 'options' => [
                '週1回', '週2回', '週3回', 'その他',
            ]],

            ['type' => 'checkbox', 'title' => '② セットに入っているとよいと思う魚を教えてください。（複数選択可）', 'is_required' => false, 'options' => [
                '赤甘鯛', 'のどぐろ', '剣先イカ', 'マグロ', 'ブリ', 'ハマチ', 'カツオ', '真穴子', 'うなぎ', 'ハモ', 'アジ', 'サバ', 'タイ', 'シマアジ', 'ヒラメ', 'クエ', 'タコ', 'その他',
            ]],

            ['type' => 'checkbox', 'title' => '③ 配達時に、希望する加工処理方法があれば教えてください。（複数選択可）', 'is_required' => false, 'options' => [
                'セミドレスのまま', '三枚おろし・フィレ加工', 'ウロコ処理', '骨抜き', 'スライス加工', 'ポーション加工', 'その他',
            ]],

            ['type' => 'textarea', 'title' => '④ その他、ご意見・ご要望がございましたら、自由にお聞かせください。', 'is_required' => false, 'options' => null],

            ['type' => 'text', 'title' => '店舗名', 'is_required' => false, 'options' => null],

            ['type' => 'text', 'title' => 'お名前', 'is_required' => false, 'options' => null],
        ];

        foreach ($questions as $order => $question) {
            $form->questions()->updateOrCreate(['order' => $order], $question);
        }

        $form->questions()->where('order', '>=', count($questions))->delete();
    }
}
