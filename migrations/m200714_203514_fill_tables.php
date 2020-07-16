<?php

use app\models\Account;
use app\models\Client;
use app\models\Operation;
use yii\db\Migration;

/**
 * Class m200714_203514_fill_tables
 */
class m200714_203514_fill_tables extends Migration
{
    public function up()
    {
        $client = new Client([
            'id_code' => '4345785689',
            'name' => 'Алексей',
            'surname' => 'Миронов',
            'gender' => Client::GENDER_MALE,
            'birthday' => '1969-03-05',
        ]);
        $client->save();

        $account = new Account([
            'client_id' => $client->id,
            'cash' => 1000,
            'percent' => 8,
            'created_at' => '2020-01-31 00:00:00',
        ]);
        $account->save();

        $account = new Account([
            'client_id' => $client->id,
            'cash' => 15000,
            'percent' => 10,
            'created_at' => '2020-02-1 00:00:00',
        ]);
        $account->save();

        //----------------------------

        $client = new Client([
            'id_code' => '9345785789',
            'name' => 'Анатолий',
            'surname' => 'Прохоров',
            'gender' => Client::GENDER_MALE,
            'birthday' => '1965-10-15',
        ]);
        $client->save();

        $account = new Account([
            'client_id' => $client->id,
            'cash' => 10000,
            'percent' => 13,
            'created_at' => '2019-12-05 00:00:00',
        ]);
        $account->save();

        //----------------------------

        $client = new Client([
            'id_code' => '3445785589',
            'name' => 'Сергей',
            'surname' => 'Терников',
            'gender' => Client::GENDER_MALE,
            'birthday' => '1999-05-01',
        ]);
        $client->save();

        $account = new Account([
            'client_id' => $client->id,
            'cash' => 4000,
            'percent' => 8,
            'created_at' => '2019-02-15 00:00:00',
        ]);
        $account->save();

        $account = new Account([
            'client_id' => $client->id,
            'cash' => 8000,
            'percent' => 14,
            'created_at' => '2019-04-22 00:00:00',
        ]);
        $account->save();

        //----------------------------

        $client = new Client([
            'id_code' => '5345485683',
            'name' => 'Олег',
            'surname' => 'Власов',
            'gender' => Client::GENDER_MALE,
            'birthday' => '1996-04-01',
        ]);
        $client->save();

        $account = new Account([
            'client_id' => $client->id,
            'cash' => 16000,
            'percent' => 10,
            'created_at' => '2019-01-15 00:00:00',
        ]);
        $account->save();

        //----------------------------

        $client = new Client([
            'id_code' => '3445995649',
            'name' => 'Оксана',
            'surname' => 'Тодорова',
            'gender' => Client::GENDER_FEMALE,
            'birthday' => '1980-12-10',
        ]);
        $client->save();

        $account = new Account([
            'client_id' => $client->id,
            'cash' => 30000,
            'percent' => 12,
            'created_at' => '2020-01-03 00:00:00',
        ]);
        $account->save();
    }

    public function down()
    {
        Operation::deleteAll([]);
        Account::deleteAll([]);
        Client::deleteAll([]);
    }
}
