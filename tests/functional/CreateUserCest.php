<?php

use Faker\Factory;
use \Codeception\Util\HttpCode;


/**
 * Тестирование метода Сreate user
 */
class CreateUserCest
{
    /**
     * Проверка успешной регистрации пользователя
     *
     * @param \FunctionalTester $I
     */
    public function checkUserCreate(FunctionalTester $I)
    {
        $I->wantTo('Check that user creates successfully');
       
        $faker = Factory::create();
        $username = $faker->name;
        $email    = $faker->email;
        $password = $faker->password;
        
        $I->sendPOST(
            '/user/create',
            [
                'username' => $username,
                'email'    => $email,
                'password' => $password
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->canSeeResponseContainsJson([
            'success' => true,
            'details' => [
                'email'    => $email,
                'username' => $username
            ],
            "message" => "User Successully created"
        ]);

        $id = preg_replace("/[^0-9]/", '', $I->grabDataFromResponseByJsonPath('$..details.id'));

        $I->sendGET('/user/get');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id' => $id[0],
            'username' => $username,
            'email'    => $email
        ]);
    }

    /**
     * Проверка кода ошибки 400 при создании юзера без обязательного параметра - usermname
     *
     * @param \FunctionalTester $I
     */
    public function checkCreateWithoutRequiredFieldUsername(FunctionalTester $I)
    {
        $I->wantTo('Check error code 400 while create user without usermane');
        
        $faker = Factory::create();
        $email    = $faker->email;
        $password = $faker->password;
        
        $I->sendPOST(
            '/user/create',
            [
                'email'    => $email,
                'password' => $password
            ]
        );
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->canSeeResponseContainsJson([
            'success'             => false,
            'message' => [
                "A username is required"
            ],
        ]);
    }

    /**
     * Проверка кода ошибки 400 при создании юзера c уже существующим email
     *
     * @param \FunctionalTester $I
     */
    public function checkCreateWithAlreadyExistedValue(FunctionalTester $I)
    {
        $I->wantTo('Check error code 400 while create user without usermane');
        
        $faker = Factory::create();
        $email    = $faker->email;
        $password = $faker->password;
        
        $I->sendPOST(
            '/user/create',
            [

                'email'    => $this->getAlreadyExistedEmail,
                'password' => $password,
                'username' => $username
            ]
        );
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->canSeeResponseContainsJson([
            'success'             => false,
            'message' => [
                "This username is taken. Try another."
            ],
        ]);
    }
    
    /**
     *  Возвращает существующий email
     *
     * @param \FunctionalTester $I
     */
    protected function getAlreadyExistedEmail(FunctionalTester $I)
    {
        $I->sendGET('/user/get');
        $existedEmail = $I->grabDataFromResponseByJsonPath('$..username');

        return $existedEmail;
    }
}
