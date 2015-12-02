<?php

namespace App\Generators;

interface TokenGeneratorInterface
{
    public static function generate(array $data = []);
}