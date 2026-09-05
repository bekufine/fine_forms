<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Seeder;

class FreshFishPostUseSurveySeeder extends Seeder
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

        $title = 'JAPAN QUALITY FROZEN SEAFOOD Feedback Survey';
        $description = "この度は、商品をお試しいただき、誠にありがとうございます。\n今後の商品展開の参考にするため、ぜひ率直なご意見をお聞かせください。\n\n所要時間：約3分";

        $form = Form::where('user_id', $user->id)
            ->whereIn('title', ['新鮮魚便 ご利用後アンケート', $title])
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
            ['type' => 'section', 'title' => '【1】基本情報', 'is_required' => false, 'options' => null],
            ['type' => 'text', 'title' => 'レストラン名 / 企業名', 'is_required' => true, 'options' => null],
            ['type' => 'text', 'title' => 'ご担当者様 氏名', 'is_required' => true, 'options' => null],
            ['type' => 'text', 'title' => 'メールアドレス', 'is_required' => true, 'options' => null],

            ['type' => 'section', 'title' => '【2】商品について', 'is_required' => false, 'options' => null],
            ['type' => 'checkbox', 'title' => '今回お試しいただいた中で、良かった商品を教えてください。（複数選択可）', 'is_required' => false, 'options' => [
                '赤甘鯛', 'のどぐろ', '真穴子', '剣先イカ', '煮穴子', '真鯛', 'シマアジ', 'ヒラメ', 'ウニ', '冷凍寿司',
            ]],

            ['type' => 'section', 'title' => '商品全体について、5段階で評価してください（5：非常に良い／4：良い／3：普通／2：やや不満／1：不満）', 'is_required' => false, 'options' => null],
            ['type' => 'radio', 'title' => '味・品質', 'is_required' => true, 'options' => ['5', '4', '3', '2', '1']],
            ['type' => 'radio', 'title' => '商品の状態', 'is_required' => true, 'options' => ['5', '4', '3', '2', '1']],
            ['type' => 'radio', 'title' => '使いやすさ', 'is_required' => true, 'options' => ['5', '4', '3', '2', '1']],
            ['type' => 'radio', 'title' => '価格・価値', 'is_required' => true, 'options' => ['5', '4', '3', '2', '1', '判断できない']],

            ['type' => 'checkbox', 'title' => 'サブスク小口チルド定期便について', 'is_required' => false, 'options' => [
                'ぜひ利用したい', '価格が合えば利用したい', '魚種を選べれば利用したい',
                '希望する加工であれば利用したい', '旬の魚についてもっと知りたい', '現時点では利用を考えていない',
            ]],

            ['type' => 'section', 'title' => '【4】ご意見・ご要望', 'is_required' => false, 'options' => null],
            ['type' => 'textarea', 'title' => "良かった点、気になった点、今後のご要望などがあればお聞かせください。\n\n希望する魚種、サイズ、加工状態、価格帯、配送方法など、どのような内容でも構いません。", 'is_required' => false, 'options' => null],
        ];

        foreach ($questions as $order => $question) {
            $form->questions()->updateOrCreate(['order' => $order], $question);
        }

        $form->questions()->where('order', '>=', count($questions))->delete();
    }
}
