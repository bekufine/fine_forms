<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartTimeStaffCareerSurveySeeder extends Seeder
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

        $title = 'アルバイトスタッフ 進路アンケート';
        $description = "今後の進路について、皆さんのお考えをお聞かせください。\n\nご回答は今後のサポートの参考にさせていただきます。\nお答えいただける項目のみで結構です。";

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

        $questions = [
            ['type' => 'text', 'title' => '1. 名前', 'is_required' => true, 'options' => null],

            ['type' => 'radio', 'title' => '2. 今後の進路について教えてください。', 'is_required' => false, 'options' => [
                '就職を希望している', '進学を希望している', '就職か進学か検討中', 'まだ決めていない', 'その他',
            ]],

            ['type' => 'section', 'title' => "3. 就職を希望している方にお聞きします。\n希望している業界や会社名があれば教えてください。\n※まだ決まっていない場合は「未定」で構いません。", 'is_required' => false, 'options' => null],

            ['type' => 'text', 'title' => '業界', 'is_required' => false, 'options' => null],

            ['type' => 'text', 'title' => '会社名', 'is_required' => false, 'options' => null],

            ['type' => 'checkbox', 'title' => '4. 興味のある業界を教えてください。（複数選択可）', 'is_required' => false, 'options' => [
                'ホテル・観光', '飲食', 'ブライダル', '人材', 'IT・Web', '広告・マーケティング', '美容・アパレル', '医療・福祉', '教育', '金融・不動産', 'メーカー', '公務員', 'その他', 'まだ分からない',
            ]],

            ['type' => 'checkbox', 'title' => '5. 興味のある仕事内容を教えてください。（複数選択可）', 'is_required' => false, 'options' => [
                '接客・サービス', '営業', '企画', 'マーケティング', '事務', '人事・採用', 'マネジメント', 'IT・エンジニア', 'デザイン・クリエイティブ', '専門職', 'その他', 'まだ分からない',
            ]],

            ['type' => 'checkbox', 'title' => '6. 将来の仕事を選ぶ際に重視したいものを3つまで選んでください。', 'is_required' => false, 'options' => [
                '給与', '休日・働きやすさ', '勤務地', '仕事内容', 'やりがい', '成長できる環境', '安定性', '会社の雰囲気・人間関係', '福利厚生', 'キャリアアップ', '有名な会社・ブランド', '好きなことを仕事にできる', '社会貢献', 'その他',
            ]],

            ['type' => 'textarea', 'title' => "7. 自由記載\n将来やってみたいこと、目指している仕事、進路について考えていることなどがあれば自由に記入してください。", 'is_required' => false, 'options' => null],
        ];

        foreach ($questions as $order => $question) {
            $form->questions()->updateOrCreate(['order' => $order], $question);
        }

        $form->questions()->where('order', '>=', count($questions))->delete();
    }
}
