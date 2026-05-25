<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $baseUrl;
    private string $instance;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('services.evolution.url'), '/');
        $this->instance = config('services.evolution.instance');
        $this->apiKey   = config('services.evolution.apikey');
    }

    /**
     * Send a text message via Evolution API.
     *
     * @param  string  $number  WhatsApp number in international format without '+' (e.g. 628123456789)
     * @param  string  $text    Message body (supports WhatsApp markdown: *bold*, _italic_)
     * @return bool
     */
    public function sendText(string $number, string $text): bool
    {
        $url = "{$this->baseUrl}/message/sendText/{$this->instance}";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apikey'       => $this->apiKey,
            ])->timeout(15)->post($url, [
                'number'      => $number,
                'textMessage' => ['text' => $text],
                'options'     => [
                    'delay'       => 1200,
                    'linkPreview' => false,
                ],
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('WhatsApp send failed', [
                'number' => $number,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp service exception', [
                'number'  => $number,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
