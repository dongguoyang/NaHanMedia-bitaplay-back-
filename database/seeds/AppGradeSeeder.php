<?php

use Illuminate\Database\Seeder;

class AppGradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => '年满3周岁',
                'content' => '此类内容不包含暴力、惊吓、不良用语（如低俗幽默、粗口）等受限制内容及7+以上级别禁止内容，一般情况下，适合各个年龄段的用户。',
            ],
            [
                'name' => '年满7周岁',
                'content' => '此类内容基本健康，可能包含少量卡通暴力或极少的含蓄不良用语（如轻度的粗口，但不得含有性粗口），但不得含有现实暴力（如针对人类外形或动物形象或描写细致、逼真、血腥的暴力场景）或惊吓的内容（如可能惊吓到儿童的声音或场景）及12+以上级别禁止内容。',
            ],
            [
                'name' => '年满12周岁',
                'content' => '此类内容可能包含少量的轻微暴力（针对虚拟形象的暴力内容以及含蓄的针对人类外形或动物角色的暴力内容，不含逼真、细致、血腥的暴力场景）、少量的含蓄不良用语（如轻度的粗口，但不得含有性粗口）、少量可能惊吓到儿童的场景（但不含惊悚/恐怖题材内容），但不得含有粗俗幽默内容及16+以上级别禁止内容。',
            ],
            [
                'name' => '年满16周岁',
                'content' => '此类内容可能包含较少的暴力（不含逼真、细致、血腥的暴力场景）、不良用语（不得含有性粗口）、可能惊吓到儿童的场景（但不含惊悚/恐怖题材内容），但不得含有性暗示内容（例如两性笑话、裸露内容等）、惊悚/恐怖题材内容、提及烟草/饮酒的内容及18+级别禁止内容。',
            ],
            [
                'name' => '年满18周岁',
                'content' => '此类内容（如理财、直播表演、恋爱交友等）仅适用于成人用户，但不得含有色情、赌博、激烈的暴力（如血腥、残肢、严刑拷打等场景）、教唆犯罪、危害国家利益、破坏民族团结、侮辱宗教信仰、宣扬邪教/迷信/毒品等违反法律及违背社会公德的内容。',
            ],
        ];
        foreach ($data as $v) {
            \App\Models\AppGrade::create([
                'name' => $v['name'],
                'content' => $v['content']
            ]);
        }
    }
}
