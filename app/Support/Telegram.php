<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Throwable;

/** Уведомления о новых заявках в Telegram (токен бота и chat id задаются в админке). */
class Telegram
{
    public static function notify(string $text): bool
    {
        $token = (string) Setting::get('telegram_bot_token', '');
        $chat = (string) Setting::get('telegram_chat_id', '');
        if ($token === '' || $chat === '') {
            return false;
        }

        try {
            return (bool) Http::timeout(8)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chat,
                    'text' => $text,
                    'disable_web_page_preview' => true,
                ])->json('ok');
        } catch (Throwable) {
            return false;
        }
    }
}
