<?php

namespace App\Generators;

class Sha2TokenGenerator implements TokenGeneratorInterface
{
    public static function generate(array $data = [])
    {
        $value = "sha256-sha2-token-generator" . ( new \Datetime )->format("Y-m-d H:i:s");

        return hash("sha256", $value);
    }
}