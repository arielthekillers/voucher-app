<?php

class WhatsAppService {
    private $apiKey;
    private $deviceId;
    private $baseUrl;
    private $enabled;
    private $db;

    public function __construct() {
        $this->db = Database::connect();
        $settings = $this->db->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $this->apiKey = $settings['whatsapp_api_token'] ?? '';
        $this->deviceId = $settings['whatsapp_device_id'] ?? '';
        $this->baseUrl = $settings['whatsapp_endpoint'] ?: 'https://ruangwa.id/api-app/waba/messages/simple';
        $this->enabled = ($settings['whatsapp_enabled'] ?? '0') === '1';
    }

    public function send($phone, $message, $mediaUrl = null) {
        if (!$this->enabled) return false;

        $data = [
            'api_key' => $this->apiKey,
            'device_key' => $this->deviceId,
            'phone' => $this->formatPhone($phone),
            'message' => $message,
            'url' => $mediaUrl
        ];

        return $this->request($data);
    }

    public function sendEarnNotification($phone, $amount, $totalPoints) {
        $currency = defined('CURRENCY_NAME') ? CURRENCY_NAME : 'Point';
        $businessName = defined('APP_NAME') ? APP_NAME : 'Kami';
        
        $message = "Halo kak, terima kasih telah berbelanja di *$businessName*.\n\n";
        $message .= "Kami telah menambahkan *$amount $currency* ke saldo akun kamu.\n";
        $message .= "Total saldo kamu sekarang: *$totalPoints $currency*.\n\n";
        $message .= "Terus kumpulkan $currency dan tukarkan dengan promo menarik!";

        return $this->send($phone, $message);
    }

    public function sendRedeemNotification($phone, $promoTitle, $pointCost) {
        $currency = defined('CURRENCY_NAME') ? CURRENCY_NAME : 'Point';
        $businessName = defined('APP_NAME') ? APP_NAME : 'Kami';

        $message = "Redemption Berhasil!\n\n";
        $message .= "Kamu baru saja menukarkan *$pointCost $currency* untuk promo: *$promoTitle* di $businessName.\n";
        $message .= "Silakan tunjukkan pesan ini ke kasir jika diperlukan.\n\n";
        $message .= "Terima kasih!";

        return $this->send($phone, $message);
    }

    private function request($data) {
        $ch = curl_init($this->baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("WhatsApp API Error: $error");
            return false;
        }

        return json_decode($response, true);
    }

    private function formatPhone($phone) {
        // Simple formatter: replace 08 with 628, remove non-digits
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 2) === '08') {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }
}
