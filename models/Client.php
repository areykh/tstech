<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "client".
 *
 * @property int $id
 * @property int $id_code
 * @property string $name
 * @property string $surname
 * @property int $gender
 * @property string|null $birthday
 *
 * @property Account[] $accounts
 */
class Client extends \yii\db\ActiveRecord
{
    const GENDER_MALE = 0;
    const GENDER_FEMALE = 1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'client';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_code', 'name', 'surname', 'gender'], 'required'],
            [['id_code', 'gender'], 'integer'],
            [['birthday'], 'safe'],
            [['name', 'surname'], 'string', 'max' => 255],
            [['id_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_code' => 'Id Code',
            'name' => 'Name',
            'surname' => 'Surname',
            'gender' => 'Gender',
            'birthday' => 'Birthday',
        ];
    }

    /**
     * Gets query for [[Accounts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccounts()
    {
        return $this->hasMany(Account::className(), ['client_id' => 'id']);
    }
}
