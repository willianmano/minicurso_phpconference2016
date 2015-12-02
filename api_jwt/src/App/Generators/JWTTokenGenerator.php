<?php

namespace App\Generators;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class JWTTokenGenerator implements TokenGeneratorInterface
{
    public static function generate(array $data = [])
    {
        return (new Builder())->setIssuer('http://minicurso_conference.api') // Configures the issuer (iss claim)
        ->setAudience('http://minicurso_conference.api') // Configures the audience (aud claim)
        ->setIssuedAt(time())
            ->setExpiration(time() + 604800) // 1 semana
            ->set('nome', 'Admin')
            ->set('email', 'admin@admin.com')
            ->sign(new Sha256(), 'minicurso_conference.api.signature')
            ->getToken();
    }
}
