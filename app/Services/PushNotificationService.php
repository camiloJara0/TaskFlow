<?php

namespace App\Services;

use App\Models\TokenPush;
use App\Models\User;
use Base64Url\Base64Url;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    protected WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('webpush.vapid_subject'),
                'publicKey' => config('webpush.vapid_public_key'),
                'privateKey' => config('webpush.vapid_private_key'),
            ],
        ]);
    }

    public function sendToUser(User $user, array $payload): void
    {
        if (($user->estado ?? 'activo') === 'inactivo') {
            return;
        }

        $tokens = $user->tokensPush()->where('activo', true)->get();
        foreach ($tokens as $token) {
            $this->send($token, $payload);
        }
    }

    public function send(TokenPush $token, array $payload): void
    {
        if (empty($token->p256dh) || empty($token->auth)) {
            return;
        }

        if (!$this->clavesValidas($token)) {
            Log::warning('Push: claves de suscripción inválidas', ['endpoint' => $token->token]);
            return;
        }

        try {
            $subscription = Subscription::create([
                'endpoint' => $token->token,
                'keys' => [
                    'p256dh' => $token->p256dh,
                    'auth' => $token->auth,
                ],
            ]);

            $this->webPush->queueNotification(
                $subscription,
                json_encode($payload),
                ['TTL' => config('webpush.ttl', 86400)]
            );

            foreach ($this->webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    continue;
                }

                if ($report->isSubscriptionExpired()) {
                    $token->delete();
                    continue;
                }

                Log::warning('Push no entregado', [
                    'endpoint' => $token->token,
                    'status' => $report->getResponse()?->getStatusCode(),
                    'reason' => $report->getReason(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando push', ['endpoint' => $token->token, 'exception' => $e->getMessage()]);
        }
    }

    private function clavesValidas(TokenPush $token): bool
    {
        try {
            $p256dh = Base64Url::decode($token->p256dh);
            $auth = Base64Url::decode($token->auth);

            return is_string($p256dh) && is_string($auth)
                && strlen($p256dh) === 65
                && strlen($auth) === 16;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function payload(
        string $title,
        string $body,
        ?string $url = null,
        ?string $icon = null,
        ?string $badge = null
    ): array {
        return [
            'title' => $title,
            'body' => $body,
            'url' => $url ?? '/',
            'icon' => $icon ?? config('webpush.default_icon', '/pwa-192x192.png'),
            'badge' => $badge ?? config('webpush.default_badge', '/pwa-192x192.png'),
            'actions' => [
                ['action' => 'open', 'title' => 'Ver'],
            ],
        ];
    }
}
