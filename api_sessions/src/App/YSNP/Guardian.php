<?php

namespace App\YSNP;

use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

use App\Generators\JWTTokenGenerator;

class Guardian
{
    private $authorizedAppTokens = [
        '2f82ed9258510da0e0d89630c1dc797029d441a192a1fc6e0520adee52497d40'
    ];

    private $authorizedClientToken = [
        'ad6761548a14908eb22a25233f9b1e206acd2a109cf8c95adde25d8065ef89ef'
    ];

    public function validateAppToken($appToken)
    {
        return in_array($appToken, $this->authorizedAppTokens);
    }

    public function validateClientToken($clientToken)
    {
        return in_array($clientToken, $this->authorizedClientToken);
    }

    public function validateAppAndClientToken($appToken, $clientToken)
    {
        return (in_array($appToken, $this->authorizedAppTokens) && in_array($clientToken, $this->authorizedClientToken));
    }

    public function validateJwtToken($token = null)
    {
        if (is_null($token)) {
            return false;
        }

        $token = str_replace('Bearer ', '', $token);

        $token = (new Parser())->parse($token);

        $signer = new Sha256();

        // Verifica se a chave do token corresponde com a chave da aplicacao
        if (!$token->verify($signer, 'minicurso_conference.api.signature')) {
            return false;
        }

        $validation = new ValidationData();
        $validation->setIssuer('http://minicurso_conference.api');
        $validation->setAudience('http://minicurso_conference.api');

        // Verifica se o token eh valido
        $isValid = $token->validate($validation);

        if (!$isValid) {
            return false;
        }

        // Verifica se o token precisa ser recriado. O tempo verificacao eh de um dia
        $validation->setCurrentTime(time() + 86400);
        $needRegenerate = !$token->validate($validation);

        if ($needRegenerate) {
            return JWTTokenGenerator::generate();
        }

        return $token;
    }
}