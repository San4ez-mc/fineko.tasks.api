<?php

use app\models\User;

class BusinessProcessApiCest
{
    private string $token;

    public function _before(FunctionalTester $I)
    {
        $user = User::findOne(1);
        $this->token = $user->generateAccessToken(3600);
    }

    public function createBusinessProcess(FunctionalTester $I)
    {
        $data = [
            'name' => 'Новий бізнес‑процес',
            'schema' => [
                'lanes' => [],
                'nodes' => [],
                'edges' => [],
            ],
        ];

        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->token);
        $I->sendPOST('/business-processes', $data);
        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson($data);
    }
}
