<?php

namespace Database\Factories;

use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationSettingFactory extends Factory
{
    protected $model = NotificationSetting::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'channel' => $this->faker->randomElement(NotificationSetting::getChannels()),
            'notification_type' => $this->faker->randomElement(NotificationSetting::getNotificationTypes()),
            'enabled' => $this->faker->boolean(80), // 80% de probabilidad de estar habilitado
        ];
    }

    public function email(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'channel' => NotificationSetting::CHANNEL_EMAIL,
            ];
        });
    }

    public function push(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'channel' => NotificationSetting::CHANNEL_PUSH,
            ];
        });
    }

    public function sms(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'channel' => NotificationSetting::CHANNEL_SMS,
            ];
        });
    }

    public function inApp(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'channel' => NotificationSetting::CHANNEL_IN_APP,
            ];
        });
    }

    public function wallet(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'notification_type' => NotificationSetting::TYPE_WALLET,
            ];
        });
    }

    public function event(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'notification_type' => NotificationSetting::TYPE_EVENT,
            ];
        });
    }

    public function message(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'notification_type' => NotificationSetting::TYPE_MESSAGE,
            ];
        });
    }

    public function general(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'notification_type' => NotificationSetting::TYPE_GENERAL,
            ];
        });
    }

    public function enabled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'enabled' => true,
            ];
        });
    }

    public function disabled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'enabled' => false,
            ];
        });
    }

    public function forUser(int $userId): static
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'user_id' => $userId,
            ];
        });
    }

    public function mobileChannels(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'channel' => $this->faker->randomElement([
                    NotificationSetting::CHANNEL_PUSH,
                    NotificationSetting::CHANNEL_SMS,
                ]),
            ];
        });
    }

    public function digitalChannels(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'channel' => $this->faker->randomElement([
                    NotificationSetting::CHANNEL_EMAIL,
                    NotificationSetting::CHANNEL_IN_APP,
                ]),
            ];
        });
    }

    public function financialTypes(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'notification_type' => NotificationSetting::TYPE_WALLET,
            ];
        });
    }

    public function communicationTypes(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'notification_type' => $this->faker->randomElement([
                    NotificationSetting::TYPE_MESSAGE,
                    NotificationSetting::TYPE_GENERAL,
                ]),
            ];
        });
    }

    public function highPriority(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'notification_type' => NotificationSetting::TYPE_WALLET,
                'enabled' => true,
            ];
        });
    }

    public function mediumPriority(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'notification_type' => $this->faker->randomElement([
                    NotificationSetting::TYPE_EVENT,
                    NotificationSetting::TYPE_MESSAGE,
                ]),
                'enabled' => $this->faker->boolean(70),
            ];
        });
    }

    public function lowPriority(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'notification_type' => NotificationSetting::TYPE_GENERAL,
                'enabled' => $this->faker->boolean(50),
            ];
        });
    }

    public function emailWallet(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'channel' => NotificationSetting::CHANNEL_EMAIL,
                'notification_type' => NotificationSetting::TYPE_WALLET,
                'enabled' => true,
            ];
        });
    }

    public function pushEvent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'channel' => NotificationSetting::CHANNEL_PUSH,
                'notification_type' => NotificationSetting::TYPE_EVENT,
                'enabled' => true,
            ];
        });
    }

    public function smsWallet(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'channel' => NotificationSetting::CHANNEL_SMS,
                'notification_type' => NotificationSetting::TYPE_WALLET,
                'enabled' => true,
            ];
        });
    }

    public function inAppGeneral(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'channel' => NotificationSetting::CHANNEL_IN_APP,
                'notification_type' => NotificationSetting::TYPE_GENERAL,
                'enabled' => true,
            ];
        });
    }

    public function defaultSettings(): static
    {
        return $this->state(function (array $attributes) {
            // Configuraciones por defecto más realistas
            $channel = $attributes['channel'];
            $type = $attributes['notification_type'];
            
            $enabled = match(true) {
                $channel === NotificationSetting::CHANNEL_IN_APP => true,
                $channel === NotificationSetting::CHANNEL_EMAIL => true,
                $channel === NotificationSetting::CHANNEL_PUSH && in_array($type, [NotificationSetting::TYPE_EVENT, NotificationSetting::TYPE_MESSAGE]) => true,
                $channel === NotificationSetting::CHANNEL_SMS && $type === NotificationSetting::TYPE_WALLET => true,
                default => false,
            };
            
            return [
                'enabled' => $enabled,
            ];
        });
    }

    public function conservative(): static
    {
        return $this->state(function (array $attributes) {
            // Usuario conservador: solo email y in-app
            if (in_array($attributes['channel'], [NotificationSetting::CHANNEL_PUSH, NotificationSetting::CHANNEL_SMS])) {
                return ['enabled' => false];
            }
            return ['enabled' => true];
        });
    }

    public function aggressive(): static
    {
        return $this->state(function (array $attributes) {
            // Usuario agresivo: todas las notificaciones habilitadas
            return ['enabled' => true];
        });
    }

    public function selective(): static
    {
        return $this->state(function (array $attributes) {
            // Usuario selectivo: solo tipos importantes habilitados
            $importantTypes = [NotificationSetting::TYPE_WALLET, NotificationSetting::TYPE_EVENT];
            return [
                'enabled' => in_array($attributes['notification_type'], $importantTypes),
            ];
        });
    }
}
