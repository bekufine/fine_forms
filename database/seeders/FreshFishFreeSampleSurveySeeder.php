<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Seeder;

class FreshFishFreeSampleSurveySeeder extends Seeder
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

        $scale = ['非常に良い', '良い', '普通', 'やや悪い', '悪い'];

        $form = Form::firstOrCreate(
            ['user_id' => $user->id, 'title' => '新鮮魚便 無償サンプル依頼書'],
            [
                'description' => "アンケートにお答えいただいた方へ、現地パートナー会社であるIMP社を通じて、チルドフィレの無償サンプルをお届けいたします。\n\n今後の商品開発、品質向上および商品提案の参考とするため、アンケートへのご協力をお願いいたします。",
                'is_published' => true,
            ]
        );

        $questions = [
            ['type' => 'radio', 'title' => '鮮度', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '色合い', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '身質・食感', 'is_required' => true, 'options' => $scale],

            ['type' => 'radio', 'title' => '調理のしやすさについて、どのように評価されますか？', 'is_required' => true, 'options' => [
                '非常に良い', '良い', '普通', 'やや悪い', '悪い', '調理していないため分からない',
            ]],

            ['type' => 'radio', 'title' => '今回の試食を通じて、冷凍魚に対するイメージは変わりましたか？', 'is_required' => true, 'options' => [
                '大きく良くなった', '良くなった', '変わらない', '悪くなった',
            ]],

            ['type' => 'checkbox', 'title' => '今後、使用してみたい・興味のある魚種を教えてください。（複数選択可）', 'is_required' => false, 'options' => [
                '穴子', '甘鯛', '剣先イカ', 'のどぐろ', '真鯛', 'シマアジ', 'ヒラメ', 'ウニ', 'その他',
            ]],

            ['type' => 'radio', 'title' => '希望する納品状態を教えてください。', 'is_required' => false, 'options' => [
                '冷凍状態で納品し、自店で解凍', '現地で適切に解凍し、チルド状態で納品', 'どちらでもよい',
            ]],

            ['type' => 'radio', 'title' => '今後、今回の商品を取り扱うことについて、どのようにお考えですか？', 'is_required' => true, 'options' => [
                'ぜひ取り扱いたい', '条件が合えば取り扱いたい', '今後検討したい', '現時点では取り扱う予定はない',
            ]],

            ['type' => 'checkbox', 'title' => '今後のご案内について、ご希望があればお選びください。（複数選択可）', 'is_required' => false, 'options' => [
                '見積りを希望する', '商品・価格について詳しい説明を希望する', '商談を希望する', '現時点では希望しない',
            ]],

            ['type' => 'textarea', 'title' => '商品や納品方法について、ご意見・ご要望があればお聞かせください。', 'is_required' => false, 'options' => null],
        ];

        foreach ($questions as $order => $question) {
            $form->questions()->updateOrCreate(['order' => $order], $question);
        }
    }
}
