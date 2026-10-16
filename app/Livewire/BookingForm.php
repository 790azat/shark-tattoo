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

    public function submit(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:80',
            'contact' => 'required|string|max:120',
            'service' => 'required|in:'.implode(',', array_keys(Booking::SERVICES)),
            'placement' => 'nullable|string|max:80',
            'size' => 'nullable|string|max:40',
            'idea' => 'nullable|string|max:2000',
        ], [], [
            'name' => 'имя',
            'contact' => 'телефон, Telegram или Instagram',
        ]);

        $key = 'booking:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('contact', 'Слишком много заявок подряд. Напишите нам в Telegram или Instagram.');

            return;
        }
        RateLimiter::hit($key, 3600);

        $booking = Booking::create($data);

        Telegram::notify(implode("\n", array_filter([
            '🦈 Новая заявка #'.$booking->id,
            Booking::SERVICES[$booking->service] ?? $booking->service,
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
        return view('livewire.booking-form');
    }
}
