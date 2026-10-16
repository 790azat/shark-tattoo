# Shark Tattoo

Сайт тату-студии [@_shark__tattoo](https://www.instagram.com/_shark__tattoo/) (fine line тату и пирсинг, Ереван) на Laravel 13 + Livewire 4 с админкой.

## Что есть

- Главная: первый экран, актуальные (кружки как в Instagram), работы и видео с фильтром, «О мастере», услуги, форма записи и контакты.
- Видео из Instagram играют прямо на сайте через официальный плеер Instagram.
- Админка `/admin` (пароль из `ADMIN_PASSWORD`): заявки, публикации, актуальные, профиль и контакты, уведомления о заявках в Telegram.
- Данные, собранные со страницы Instagram, лежат в `database/data/instagram.json`, картинки в `public/ig/`.

## Vercel

Деплой через runtime `vercel-php` (см. `vercel.json`, `api/index.php`).

Переменные окружения: `APP_KEY`, `ADMIN_PASSWORD`, `SETUP_TOKEN`.

Без базы сайт работает на `database/seed.sqlite` (копия в `/tmp`, правки из админки временные).
Чтобы правки сохранялись, подключите Postgres (например, Neon в Vercel Marketplace, переменная `DATABASE_URL`)
и откройте `/setup/{SETUP_TOKEN}`: создадутся таблицы и импортируются данные.

## Локально

```bash
composer setup
php artisan db:seed
composer dev
```

После изменения `instagram.json` пересоберите базу для Vercel:

```bash
rm -f database/seed.sqlite && touch database/seed.sqlite
DB_DATABASE=$PWD/database/seed.sqlite php artisan migrate --force --seed
```
