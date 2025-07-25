<?php

namespace App\Interfaces;

interface CaptchaInterface
{
    public function generate(): array;
    public function validate(string $key, string $value): bool;
}