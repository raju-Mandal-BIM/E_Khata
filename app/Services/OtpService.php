<?php

namespace App\Services;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OtpService
{
    protected $otpExpiryMinutes = 1;
    protected $resendCooldownSeconds = 1;

    public function sendOtp($phone, $purpose = 'login')
    {
    
        // Check resend cooldown
        $cooldownKey = "otp_resend_cooldown_{$phone}";
        if ($this->hasRecentOtp($cooldownKey)) {
            $remainingTime = $this->getResendCooldown($cooldownKey);
            throw new \Exception("Please wait {$remainingTime} seconds before requesting a new OTP.");
        }

        // Generate OTP
        $otp = $this->generateOtpCode();

        // Store OTP in cache
        $this->storeOtpInCache($phone, $otp, $purpose);

        // Send OTP (simulated)
        $this->sendOtpToUser($phone, $otp, $purpose);

        // Set resend cooldown
        $this->setResendCooldown($cooldownKey);

        Log::info("OTP sent to {$phone} for {$purpose}");

        return [
            'success' => true,
            'otp' => $otp,
            'message' => $this->getSuccessMessage($otp)
        ];
    }

    /**
     * Verify OTP
     */
    public function verifyOtp($phone, $otp, $purpose = 'login')
    {
        // Get stored OTP from cache
        $storedOtp = $this->getStoredOtp($phone, $purpose);

        if (!$storedOtp) {
            throw new \Exception("OTP expired or not found. Please request a new OTP.");
        }

        // Check if OTP matches
        if ($storedOtp['otp'] !== $otp) {
            throw new \Exception("Invalid OTP.");
        }

        // OTP verified successfully
        $this->clearStoredOtp($phone, $purpose);

        Log::info("OTP verified for {$phone} for {$purpose}");

        return [
            'success' => true,
            'otp' => $otp
        ];
    }

    /**
     * Generate OTP code based on environment
     */
    private function generateOtpCode(): string
    {
        // if (app()->environment('local', 'testing', 'development')) {
        //     // Fixed OTP for local/testing
        // }
         return '123456';
        // return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP to user (simulated for development)
     */
    private function sendOtpToUser($phone, $otp, $purpose): void
    {
        $message = $this->getSmsMessage($otp, $purpose);

        if (app()->environment('production')) {
            // Implement your SMS gateway here
            Log::info("PRODUCTION SMS would be sent to {$phone}: {$message}");
        } else {
            Log::info("OTP for {$phone}: {$otp} (Purpose: {$purpose}) - Message: {$message}");
        }
    }

    /**
     * Get SMS message
     */
    private function getSmsMessage($otp, $purpose): string
    {
        $appName = config('app.name', 'Ekhata');

        $messages = [
            'login' => "Your login OTP for {$appName} is: {$otp}. Valid for 10 minutes.",
        ];

        return $messages[$purpose] ?? "Your OTP for {$appName} is: {$otp}. Valid for 10 minutes.";
    }

    /**
     * Get success message based on environment
     */
    private function getSuccessMessage($otp): string
    {
        if (app()->environment('local', 'testing', 'development')) {
            return "OTP sent! For testing, use: {$otp}";
        }

        return "OTP has been sent to your phone number.";
    }

    /**
     * Store OTP in cache
     */
    private function storeOtpInCache($phone, $otp, $purpose): void
    {
        $key = "otp_{$phone}_{$purpose}";
        $data = [
            'otp' => $otp,
            'expires_at' => now()->addMinutes($this->otpExpiryMinutes)->timestamp,
        ];

        Cache::put($key, $data, $this->otpExpiryMinutes * 60);
    }

    /**
     * Get stored OTP from cache
     */
    private function getStoredOtp($phone, $purpose): ?array
    {
        $key = "otp_{$phone}_{$purpose}";
        $data = Cache::get($key);

        if (!$data || $data['expires_at'] < time()) {
            return null;
        }

        return $data;
    }

    /**
     * Clear stored OTP
     */
    private function clearStoredOtp($phone, $purpose): void
    {
        $key = "otp_{$phone}_{$purpose}";
        Cache::forget($key);
    }

    /**
     * Check if there's a recent OTP request
     */
    private function hasRecentOtp($key): bool
    {
        return Cache::has($key);
    }

    /**
     * Get remaining cooldown time
     */
    private function getResendCooldown($key): int
    {
        $expiresAt = Cache::get($key, 0);
        return max(0, $expiresAt - time());
    }

    /**
     * Set resend cooldown
     */
    private function setResendCooldown($key): void
    {
        Cache::put($key, time() + $this->resendCooldownSeconds, $this->resendCooldownSeconds);
    }
}
