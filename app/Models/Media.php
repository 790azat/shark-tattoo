<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class Media extends Model
{
    protected $guarded = [];

    protected $hidden = ['data'];

    /** Сжимает загруженное фото до 1600px WebP и сохраняет в базу. */
    public static function storeUpload(UploadedFile $file): self
    {
        $src = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $src) {
            throw new RuntimeException('Не удалось прочитать изображение.');
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $scale = min(1, 1600 / max($w, $h));
        $img = imagecreatetruecolor(max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale)));
        imagealphablending($img, false);
        imagesavealpha($img, true);
        imagecopyresampled($img, $src, 0, 0, 0, 0, imagesx($img), imagesy($img), $w, $h);

        ob_start();
        imagewebp($img, null, 80);
        $bytes = (string) ob_get_clean();

        return self::create([
            'name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'data' => base64_encode($bytes),
            'size' => strlen($bytes),
        ]);
    }

    /** Путь картинки: «media:12» — загружено в админке, иначе статичный путь или внешний URL. */
    public static function url(?string $src): ?string
    {
        if (! $src) {
            return null;
        }
        if (str_starts_with($src, 'media:')) {
            return '/media/'.(int) substr($src, 6).'.webp';
        }

        return $src;
    }

    public function ref(): string
    {
        return 'media:'.$this->id;
    }

    public function path(): string
    {
        return '/media/'.$this->id.'.webp';
    }
}
