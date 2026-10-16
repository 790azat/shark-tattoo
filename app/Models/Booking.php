<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $guarded = [];

    public const STATUSES = [
        'new' => 'Новая',
        'contacted' => 'Связались',
        'done' => 'Записан',
        'cancelled' => 'Отменена',
    ];

    public const SERVICES = [
        'tattoo' => 'Тату',
        'piercing' => 'Пирсинг',
        'consult' => 'Консультация',
    ];
}
