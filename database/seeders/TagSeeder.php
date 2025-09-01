<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tag; 
use Illuminate\Support\Str; 

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'UI/UX', 'تصميم شعارات', 'رسوم توضيحية', 'بورتريه', 'طبيعة', 'منتجات',
            'Laravel', 'ReactJS', 'Vue.js', 'PHP', 'JavaScript', 'CSS', 'HTML',
            'شعر', 'قصص قصيرة', 'نسخ إعلانية', 'مقالات', 'تصوير شوارع',
            'فن تجريدي', 'فن رقمي 3D', 'أنيميشن 2D', 'أنيميشن 3D', 'فيديوهات قصيرة',
            'تصميم شخصيات', 'تجربة مستخدم', 'واجهة مستخدم', 'مواقع تفاعلية',
            'تصميم هوية', 'فنون جميلة', 'رسم رقمي', 'تصوير معماري', 'تخطيط فضاء'
        ];

        foreach ($tags as $tagName) {
            Tag::create([
                'name' => $tagName,
                'slug' => Str::slug($tagName), 
            ]);
        }
    }
}
