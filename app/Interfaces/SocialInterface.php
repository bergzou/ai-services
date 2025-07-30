<?php

namespace App\Interfaces;

interface SocialInterface
{
    public function getAuthUrl(string $state): string;
    public function getUserByCode(string $code): array;
    public function getProviderType(): int;
}