<?php

namespace Database\Seeders;

use App\Models\Highlight;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/** Импорт данных, собранных со страницы Instagram (database/data/instagram.json). */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode((string) file_get_contents(database_path('data/instagram.json')), true);
        $p = $data['profile'];

        $defaults = [
            'name' => 'Shark Tattoo',
            'title_am' => 'Տատու Երևան',
            'tagline' => 'Fine line тату в Ереване',
            'bio' => $p['bio'],
            'about' => "Тонкие линии, чистая графика и аккуратная работа. Каждый эскиз рисуется под тебя: размер, место и настроение обсуждаем заранее.\n\nВ студии также работает профессиональный пирсер. Стерильность, одноразовые расходники и спокойная атмосфера.",
            'avatar' => $p['avatar'],
            'hero' => '/ig/reels/10.jpg',
            'instagram' => 'https://www.instagram.com/'.$p['username'].'/',
            'handle' => $p['username'],
            'telegram' => $p['external_url'],
            'brand_instagram' => 'https://www.instagram.com/sharp_by_shark/',
            'brand_name' => '@sharp_by_shark',
            'city' => 'Ереван, Армения',
            'address' => '',
            'phone' => '',
            'hours' => 'По записи',
            'followers' => (string) $p['followers'],
            'posts_count' => (string) $p['posts'],
            'tagline_hy' => 'Fine line դաջվածքներ Երևանում',
            'tagline_en' => 'Fine line tattoos in Yerevan',
            'about_hy' => "Նուրբ գծեր, մաքուր գրաֆիկա և խնամքով արված աշխատանք։ Յուրաքանչյուր էսքիզ նկարվում է հենց քեզ համար՝ չափը, տեղը և տրամադրությունը քննարկում ենք նախապես։\n\nՍտուդիայում աշխատում է նաև պրոֆեսիոնալ փիրսեր։ Ստերիլություն, միանգամյա պարագաներ և հանգիստ մթնոլորտ։",
            'about_en' => "Fine lines, clean graphics and careful work. Every sketch is drawn just for you: size, placement and mood are discussed in advance.\n\nA professional piercer also works in the studio. Sterile setup, single-use supplies and a calm atmosphere.",
            'city_hy' => 'Երևան, Հայաստան',
            'city_en' => 'Yerevan, Armenia',
            'address_hy' => '',
            'address_en' => '',
            'hours_hy' => 'Նախնական գրանցմամբ',
            'hours_en' => 'By appointment',
            'telegram_bot_token' => '',
            'telegram_chat_id' => '',
        ];
        foreach ($defaults as $key => $value) {
            if (Setting::whereKey($key)->doesntExist()) {
                Setting::put($key, $value);
            }
        }

        foreach ($data['posts'] as $i => $post) {
            Post::updateOrCreate(['shortcode' => $post['shortcode']], [
                'kind' => $post['kind'],
                'category' => $post['category'] ?? 'tattoo',
                'caption' => $post['caption'] ?? null,
                'cover' => $post['cover'] ?? null,
                'images' => $post['images'] ?? null,
                'video_url' => $post['video'] ?? null,
                'views' => $post['views'] ?? 0,
                'likes' => $post['likes'] ?? 0,
                'comments' => $post['comments'] ?? 0,
                'duration' => $post['duration'] ?? null,
                'taken_at' => $post['taken_at'] ?? null,
                'is_featured' => $post['featured'] ?? false,
                'is_visible' => ! ($post['hidden'] ?? false),
                'sort' => $i,
            ]);
        }

        foreach ($data['highlights'] ?? [] as $i => $h) {
            Highlight::updateOrCreate(['title' => $h['title']], [
                'cover' => $h['cover'] ?? null,
                'items' => $h['items'] ?? [],
                'sort' => $i,
            ]);
        }
    }
}
