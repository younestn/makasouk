<?php

namespace App\Contracts;

use App\Models\User;
use Carbon\CarbonInterface;

interface PhoneVerificationSender
{
    public function send(User $user, string $phone, string $code, CarbonInterface $expiresAt): void;
}

