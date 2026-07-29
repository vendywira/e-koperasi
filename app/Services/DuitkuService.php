<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentChannel;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DuitkuService
{
    protected string $merchantCode = '';
    protected string $apiKey = '';
    protected string $callbackUrl = '';
    protected string $returnUrl = '';
    protected int $expiryPeriod = 1440;
    protected bool $sandbox = true;

    public function __construct()
    {
        $this->merchantCode = (string) (config('services.duitku.merchant_code') ?? '');
        $this->apiKey = (string) (config('services.duitku.api_key') ?? '');
        $this->callbackUrl = (string) (config('services.duitku.callback_url') ?? '');
        $this->returnUrl = (string) (config('services.duitku.return_url') ?? '');
        $this->expiryPeriod = (int) (config('services.duitku.expiry_period') ?? 1440);
        $this->sandbox = (bool) (config('services.duitku.sandbox') ?? true);
    }

    protected function baseUrl(): string
    {
        return $this->sandbox
            ? 'https://sandbox.duitku.com'
            : 'https://passport.duitku.com';
    }

    public function createInvoice(Invoice $invoice, string $paymentMethod, string $customerName, string $customerEmail, string $customerPhone = ''): array
    {
        $channel = PaymentChannel::where('code', $paymentMethod)->first();
        $baseAmount = (int) round($invoice->total_amount);
        $feeAmount = $channel ? $channel->calculateFee($baseAmount) : 0;
        $totalAmount = $channel ? $channel->totalAmount($baseAmount) : $baseAmount;

        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'amount' => $totalAmount,
            'base_amount' => $baseAmount,
            'fee_amount' => $feeAmount,
            'channel_code' => $paymentMethod,
            'status' => 'pending',
        ]);

        // Sandbox mock — no real API call when credentials missing
        if ($this->sandbox && empty($this->merchantCode)) {
            $ref = strtoupper(bin2hex(random_bytes(8)));
            $va = match ($paymentMethod) {
                'M1' => '88888' . str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'M2' => '70000' . str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'BRIVA' => '88888' . str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'BNIVA' => '90000' . str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                default => null,
            };
            $data = [
                'vaNumber' => $va,
                'reference' => 'DEMO-' . $ref,
                'redirectUrl' => null,
                'paymentUrl' => null,
                'qrUrl' => $paymentMethod === 'QRIS'
                    ? 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=DEMO-' . $ref
                    : null,
            ];
            $transaction->update([
                'duitku_ref' => 'DEMO-' . $ref,
                'expiry' => now()->addMinutes($this->expiryPeriod),
                'raw_response' => $data,
            ]);
            return $data;
        }

        $payload = [
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => $totalAmount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $transaction->id,
            'productDetails' => "Invoice {$invoice->invoice_number} — {$invoice->name}",
            'customerVaName' => $customerName,
            'email' => $customerEmail,
            'phoneNumber' => $customerPhone,
            'callbackUrl' => $this->callbackUrl,
            'returnUrl' => str_replace('{ref}', $transaction->id, $this->returnUrl),
            'signature' => $this->generateSignature($transaction->id),
            'expiryPeriod' => $this->expiryPeriod,
        ];

        $response = Http::post("{$this->baseUrl()}/api/v1/merchant/v2/createInvoice", $payload);

        if ($response->failed()) {
            Log::error('Duitku createInvoice failed', [
                'transaction_id' => $transaction->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            $transaction->update([
                'status' => 'failed',
                'raw_response' => $response->json(),
            ]);
            $msg = $response->json()['Message'] ?? 'Gagal membuat pembayaran';
            throw new \RuntimeException($msg);
        }

        $data = $response->json();
        $transaction->update([
            'duitku_ref' => $data['reference'] ?? null,
            'expiry' => now()->addMinutes($this->expiryPeriod),
            'raw_response' => $data,
        ]);

        return $data;
    }

    public function checkStatus(string $merchantOrderId): array
    {
        $payload = [
            'merchantCode' => $this->merchantCode,
            'merchantOrderId' => $merchantOrderId,
            'signature' => md5($this->merchantCode . $merchantOrderId . $this->apiKey),
        ];

        $response = Http::get("{$this->baseUrl()}/api/v1/merchant/v2/transactionStatus", $payload);

        return $response->json() ?? [];
    }

    public function verifyCallback(array $data): bool
    {
        if (!isset($data['signature'], $data['merchantOrderId'])) return false;
        $expected = md5($this->merchantCode . $data['merchantOrderId'] . $this->apiKey);
        return hash_equals($expected, $data['signature']);
    }

    protected function generateSignature(string $merchantOrderId): string
    {
        return md5($this->merchantCode . $merchantOrderId . $this->apiKey);
    }

    public function syncPaymentChannels(): array
    {
        $merchantOrderId = Str::uuid()->toString();

        try {
            $response = Http::get("{$this->baseUrl()}/api/v1/merchant/v2/paymentMethod", [
                'merchantCode' => $this->merchantCode,
                'merchantOrderId' => $merchantOrderId,
                'signature' => md5($this->merchantCode . $merchantOrderId . $this->apiKey),
            ]);

            if ($response->failed()) {
                Log::warning('Duitku syncPaymentChannels failed: ' . $response->body());
                return ['total' => 0];
            }

            $channels = $response->json()['paymentFee'] ?? [];
            $saved = 0;

            foreach ($channels as $ch) {
                $code = $ch['paymentMethod'] ?? $ch['code'] ?? '';
                if (empty($code)) continue;

                PaymentChannel::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $ch['paymentName'] ?? $ch['name'] ?? $code,
                        'icon_url' => $ch['iconUrl'] ?? null,
                        'fee_fixed' => (int) ($ch['totalFee'] ?? 0),
                        'fee_percent' => (int) ($ch['totalFeePercent'] ?? 0),
                        'type' => $this->mapChannelType($code),
                        'is_active' => true,
                    ]
                );
                $saved++;
            }

            return ['total' => $saved];
        } catch (\Throwable $e) {
            Log::error('Duitku sync exception: ' . $e->getMessage());
            return ['total' => 0];
        }
    }

    protected function mapChannelType(string $code): string
    {
        $c = strtolower($code);
        if (str_contains($c, 'va') || str_contains($c, 'virtual')) return 'va';
        if (str_contains($c, 'qris')) return 'qris';
        if (in_array($c, ['gopay', 'ovo', 'dana', 'shopeepay', 'linkaja'])) return 'ewallet';
        return 'retail';
    }
}