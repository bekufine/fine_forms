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
            ['type' => 'section', 'title' => '会社', 'is_required' => false, 'options' => null],

            ['type' => 'text', 'title' => '第一希望', 'is_required' => true, 'options' => null],

            ['type' => 'text', 'title' => '第二希望', 'is_required' => false, 'options' => null],

            ['type' => 'text', 'title' => '第三希望', 'is_required' => false, 'options' => null],
        ];

        foreach ($questions as $order => $question) {
            $form->questions()->updateOrCreate(['order' => $order], $question);
        }

        $form->questions()->where('order', '>=', count($questions))->delete();
    }
}
