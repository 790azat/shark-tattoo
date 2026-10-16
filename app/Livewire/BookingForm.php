<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Support\Telegram;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class BookingForm extends Component
{
    public string $name = '';

    public string $contact = '';

    public string $service = 'tattoo';

    public string $placement = '';

    public string $size = '';

    public string $idea = '';

    public bool $sent = false;

    public string $lang = 'hy';

    public function boot(): void
    {
        app()->setLocale($this->lang);
    }

    public function submit(): void
    {
        app()->setLocale($this->lang);
        $data = $this->validate([
            'name' => 'required|string|max:80',
            'contact' => 'required|string|max:120',
            'service' => 'required|in:'.implode(',', array_keys(Booking::SERVICES)),
            'placement' => 'nullable|string|max:80',
            'size' => 'nullable|string|max:40',
            'idea' => 'nullable|string|max:2000',
        ], [], [
            'name' => __('Имя'),
            'contact' => __('Телефон / Telegram / Instagram'),
        ]);

        $key = 'booking:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('contact', __('Слишком много заявок подряд. Напишите нам в Telegram или Instagram.'));

            return;
        }
        RateLimiter::hit($key, 3600);

        $booking = Booking::create($data);

        Telegram::notify(implode("\n", array_filter([
            '🦈 Новая заявка #'.$booking->id,
            Booking::SERVICES[$booking->service] ?? $booking->service,
            'Язык: '.$this->lang,
            'Имя: '.$booking->name,
            'Контакт: '.$booking->contact,
            $booking->placement ? 'Место: '.$booking->placement : null,
            $booking->size ? 'Размер: '.$booking->size : null,
            $booking->idea ? 'Идея: '.$booking->idea : null,
        ])));

        $this->reset('name', 'contact', 'placement', 'size', 'idea');
        $this->sent = true;
    }

    public function render()
    {
        app()->setLocale($this->lang);

        return view('livewire.booking-form');
    }
}
