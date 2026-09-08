<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class SmsAutoSettingsService
{
    const FILE = 'sms_auto_settings.json';

    const DEFAULTS = [
        'welcome' => [
            'enabled'  => true,
            'template' => 'Dear {name}, welcome to Chibobrand! We are glad to have you as our client. For inquiries call 0753 883 382. - CHIBOBRAND',
        ],
        'task_received' => [
            'enabled'  => true,
            'template' => 'Hello!! {name}, design task {task_code} imepokelewa CHIBO BRANDS. Umelipia TZS {paid}, kiasi kilichobaki {balance}. Task yako itakamilika {deadline}. Tujulishe: {seller_contact}',
        ],
        'task_ready' => [
            'enabled'  => true,
            'template' => 'Hello {name}, kazi yako {task_code} ({task_title}) iko tayari! Pickup code: {pickup_code}. Balance: TZS {balance}. Tujulishe: {seller_contact} - CHIBOBRAND',
        ],
        'task_completed' => [
            'enabled'  => true,
            'template' => 'Hi {name}, design task {task_code} ({task_title}) imekamilika na iko tayari. Contact: {seller_contact} - CHIBOBRAND',
        ],
        'payment_received' => [
            'enabled'  => true,
            'template' => 'Hi {name}, tumeipokea malipo ya TZS {amount} kwa task {task_code}. Asante! Balance iliyobaki: TZS {balance}. - CHIBOBRAND',
        ],
        'delivery_assigned' => [
            'enabled'  => true,
            'template' => 'Hi {name}, kazi yako {task_code} ({task_title}) iko njiani kwako! Delivery team itafika hivi karibuni. Maswali: {seller_contact} - CHIBOBRAND',
        ],
        'lead_reminder' => [
            'enabled'  => false,
            'template' => 'Hi {name}, following up from Chibobrand. We have amazing design offers for you! Call us: 0753 883 382.',
        ],
        'overdue_payment' => [
            'enabled'  => false,
            'template' => 'Dear {name}, una balance ya TZS {balance} kwa task {task_code} ambayo bado haijalipiwa. Tafadhali wasiliana nasi: 0753 883 382.',
        ],
        'task_cancelled' => [
            'enabled'  => true,
            'template' => 'Hi {name}, design task {task_code} ({task_title}) imefutwa. Wasiliana nasi kwa maelezo zaidi. - CHIBOBRAND 0753 883 382',
        ],
    ];

    public function getAll(): array
    {
        if (!Storage::exists(self::FILE)) {
            return self::DEFAULTS;
        }
        $saved = json_decode(Storage::get(self::FILE), true) ?? [];
        $result = self::DEFAULTS;
        foreach ($saved as $key => $val) {
            if (isset($result[$key])) {
                $result[$key] = array_merge($result[$key], $val);
            }
        }
        return $result;
    }

    public function get(string $trigger): array
    {
        $all = $this->getAll();
        return $all[$trigger] ?? ['enabled' => false, 'template' => ''];
    }

    public function isEnabled(string $trigger): bool
    {
        return (bool) ($this->get($trigger)['enabled'] ?? false);
    }

    public function getTemplate(string $trigger): string
    {
        return $this->get($trigger)['template'] ?? '';
    }

    /**
     * Resolve a template string, replacing {variable} placeholders.
     */
    public function resolveTemplate(string $trigger, array $vars): string
    {
        $template = $this->getTemplate($trigger);
        if (!$template) return '';
        foreach ($vars as $key => $value) {
            $template = str_replace('{' . $key . '}', $value ?? '', $template);
        }
        return $template;
    }

    public function saveAll(array $settings): void
    {
        $clean = [];
        foreach ($settings as $key => $val) {
            if (isset(self::DEFAULTS[$key])) {
                $clean[$key] = [
                    'enabled'  => (bool) ($val['enabled'] ?? false),
                    'template' => substr(trim($val['template'] ?? ''), 0, 500),
                ];
            }
        }
        Storage::put(self::FILE, json_encode($clean, JSON_PRETTY_PRINT));
    }
}
