<?php

use app\models\User;

class ResultsApiCest
{
    private string $token;

    public function _before(FunctionalTester $I)
    {
        $user = User::findOne(1);
        $this->token = $user->generateAccessToken(3600);
    }

    public function usersList(FunctionalTester $I)
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->token);
        $I->sendGET('/users', ['active' => 1]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            [
                'id' => 1,
                'first_name' => 'Admin',
                'last_name' => 'User',
                'username' => 'admin',
            ],
        ]);
    }

    public function createAndListResults(FunctionalTester $I)
    {
        $data = [
            'title' => 'Api test',
            'final_result' => 'Done',
            'urgent' => true,
            'due_date' => '22.03.1993 12:32',
            'description' => 'Desc',
            'responsible_id' => 1,
        ];

        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->token);
        $I->sendPOST('/results', $data);
        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson($data);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->token);
        $I->sendGET('/results', ['page' => 1]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'meta' => ['page' => 'integer', 'perPage' => 'integer', 'total' => 'integer'],
            'items' => 'array',
        ]);
    }
}

