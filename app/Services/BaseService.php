<?php

namespace App\Services;

use App\Models\SmtpSettings;
use App\Mail\CustomMailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class BaseService
{
    /**
     * Discover SMTP settings with fallback from user to company level.
     */
    protected function getSmtpSettings($userId = null, $companyId = null)
    {
        $userId = $userId ?: Auth::id();
        $companyId = $companyId ?: Auth::user()->cid;

        // 1. Try to find user-specific settings
        $settings = SmtpSettings::where('user_id', $userId)->first();

        // 2. Fallback to company-specific settings
        if (!$settings && $companyId) {
            $settings = SmtpSettings::where('cid', $companyId)->first();
        }

        return $settings;
    }

    /**
     * Shared helper to send mail using CustomMailable with SMTP discovery.
     */
    public function sendMail($to, $subject, $viewName, $viewData, $userId = null, $companyId = null)
    {
        $settings = $this->getSmtpSettings($userId, $companyId);

        $fromAddress = $settings?->from_address;
        $fromName = $settings?->from_name;

        $mailable = new CustomMailable(
            $subject,
            $viewName,
            $viewData,
            $fromAddress,
            $fromName
        );

        return Mail::to($to)->send($mailable);
    }
}
