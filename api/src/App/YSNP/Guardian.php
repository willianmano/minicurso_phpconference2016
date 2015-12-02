<?php

namespace App\YSNP;

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
}