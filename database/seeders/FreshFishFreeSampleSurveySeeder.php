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

        $scale = ['とても良い', '良い', '普通', 'あまり良くない', '良くない'];

        $form = Form::firstOrCreate(
            ['user_id' => $user->id, 'title' => '新鮮魚便 無償サンプル依頼書'],
            [
                'description' => "アンケートにお答えいただいた方へ、現地パートナー会社であるIMP社を通じて、チルドフィレの無償サンプルをお届けいたします。\n\n今後の商品開発、品質向上および商品提案の参考とするため、アンケートへのご協力をお願いいたします。",
                'is_published' => true,
            ]
        );

        $questions = [
            ['type' => 'text', 'title' => '貴社名・店名', 'is_required' => true, 'options' => null],
            ['type' => 'text', 'title' => 'ご担当者様氏名', 'is_required' => true, 'options' => null],
            ['type' => 'text', 'title' => '役職名（任意）', 'is_required' => false, 'options' => null],
            ['type' => 'text', 'title' => '電話番号', 'is_required' => true, 'options' => null],
            ['type' => 'text', 'title' => 'メールアドレス', 'is_required' => true, 'options' => null],
            ['type' => 'checkbox', 'title' => '業種（複数選択可）', 'is_required' => true, 'options' => [
                'レストラン', 'ホテル', '輸入業', '卸売業', '小売業', '料理学校', 'その他',
            ]],

            ['type' => 'radio', 'title' => '1．産地でエラと内臓を取り除くセミドレス加工', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '2．真空パックと急速冷凍による品質保持', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '3．冷凍状態で輸送し、現地で適切に解凍する仕組み', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '4．解凍後、チルドフィレとして納品する仕組み', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '5．冷凍処理によるアニサキスなどの寄生虫対策', 'is_required' => true, 'options' => $scale],

            ['type' => 'radio', 'title' => '鮮度', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '色合い', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '香り', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '身質・食感', 'is_required' => true, 'options' => $scale],
            ['type' => 'radio', 'title' => '調理のしやすさ', 'is_required' => true, 'options' => $scale],

            ['type' => 'radio', 'title' => '冷凍魚に対するイメージは変わりましたか。', 'is_required' => true, 'options' => [
                '大きく向上した', '少し向上した', '変わらない', 'あまり良くならなかった', '良くならなかった',
            ]],

            ['type' => 'checkbox', 'title' => '希望する加工形態（複数選択可）', 'is_required' => false, 'options' => [
                'セミドレス', 'ウロコ取り', '三枚おろし', '骨抜き', 'フィレ', 'ポーションカット', 'スライスカット', 'その他',
            ]],

            ['type' => 'checkbox', 'title' => '興味のある魚種（複数選択可）', 'is_required' => false, 'options' => [
                'のどぐろ', '甘鯛', '真穴子', '真鯛', '剣先イカ', 'クエ', 'アジ', 'サバ', 'ヒラメ', '太刀魚', 'カツオ', 'ウニ', 'ヒラマサ', '鮎', 'その他',
            ]],

            ['type' => 'radio', 'title' => '今後の取扱いについて', 'is_required' => true, 'options' => [
                'ぜひ取り扱いたい', '条件が合えば取り扱いたい', '無償サンプルを確認して検討したい', '現時点では取り扱う予定はない',
            ]],

            ['type' => 'radio', 'title' => '希望する納品状態', 'is_required' => false, 'options' => [
                '冷凍のまま', '解凍後のチルドフィレ', '仕込み済みの冷蔵状態', '商品によって使い分けたい', 'その他',
            ]],

            ['type' => 'text', 'title' => '想定される使用数量（kg／週）', 'is_required' => false, 'options' => null],
            ['type' => 'text', 'title' => '想定される使用数量（尾・パック／週）', 'is_required' => false, 'options' => null],

            ['type' => 'text', 'title' => 'ご希望の魚種（無償サンプル）', 'is_required' => false, 'options' => null],
            ['type' => 'text', 'title' => 'ご希望の加工形態（無償サンプル）', 'is_required' => false, 'options' => null],
            ['type' => 'text', 'title' => 'ご希望の納品時期（無償サンプル）', 'is_required' => false, 'options' => null],

            ['type' => 'radio', 'title' => 'お見積り依頼', 'is_required' => false, 'options' => ['あり', 'なし']],
            ['type' => 'radio', 'title' => '商談の希望', 'is_required' => false, 'options' => ['あり', 'なし']],
            ['type' => 'checkbox', 'title' => '希望する連絡方法（複数選択可）', 'is_required' => false, 'options' => [
                '訪問', 'オンライン', '電話', 'メール',
            ]],
            ['type' => 'radio', 'title' => '希望する連絡時期', 'is_required' => false, 'options' => [
                'できるだけ早く', '1週間以内', '1か月以内', 'その他',
            ]],

            ['type' => 'textarea', 'title' => 'ご意見・ご要望', 'is_required' => false, 'options' => null],
        ];

        foreach ($questions as $order => $question) {
            $form->questions()->updateOrCreate(['order' => $order], $question);
        }
    }
}
