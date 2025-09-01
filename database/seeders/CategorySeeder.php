<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category; 
use Illuminate\Support\Str; 

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'تصميم جرافيكي', 'description' => 'أعمال التصميم المرئي مثل الشعارات، الهويات البصرية، والملصقات.'],
            ['name' => 'تصوير فوتوغرافي', 'description' => 'صور فنية ووثائقية، بما في ذلك التصوير الشخصي، الطبيعة، والمنتجات.'],
            ['name' => 'تطوير ويب', 'description' => 'مشاريع تطوير الواجهة الأمامية والخلفية للمواقع والتطبيقات.'],
            ['name' => 'كتابة إبداعية', 'description' => 'قصص قصيرة، شعر، نصوص إعلانية، ومحتوى إبداعي.'],
            ['name' => 'فن رقمي', 'description' => 'رسومات ولوحات فنية تم إنشاؤها باستخدام الأدوات الرقمية.'],
            ['name' => 'تحريك ورسوم متحركة', 'description' => 'فيديوهات رسوم متحركة ثنائية وثلاثية الأبعاد.'],
            ['name' => 'تطوير ألعاب', 'description' => 'تصميم وتطوير ألعاب الفيديو.'],
            ['name' => 'تصميم داخلي', 'description' => 'تصميم المساحات الداخلية والديكور.'],
        ];

        foreach ($categories as $categoryData) {
            Category::create([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']), 
                'description' => $categoryData['description'],
            ]);
        }
    }
}
