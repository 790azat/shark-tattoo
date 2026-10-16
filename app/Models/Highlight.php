<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Highlight extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['items' => 'array', 'is_visible' => 'boolean'];
    }

    public function coverUrl(): ?string
    {
        return Media::url($this->cover ?: ($this->items[0]['src'] ?? null));
    }

    /** @return list<array{type: string, src: string}> */
    public function slides(): array
    {
        return collect($this->items ?? [])
            ->map(fn ($i) => ['type' => $i['type'] ?? 'image', 'src' => Media::url($i['src'] ?? null)])
            ->filter(fn ($i) => $i['src'])
            ->values()
            ->all();
    }
}
