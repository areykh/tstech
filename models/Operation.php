<?php

namespace app\models;

use DateTime;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "operation".
 *
 * @property int $id
 * @property int $account_id
 * @property int $direction
 * @property float $cash
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Account $account
 */
class Operation extends \yii\db\ActiveRecord
{
    const DIRECTION_ACCRUAL = 0;
    const DIRECTION_COMMISSION = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'operation';
    }

    public function behaviors()
    {
        return [
            /*[
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ]*/
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['account_id', 'direction', 'cash', /*'created_at'*/], 'required'],
            [['account_id', 'direction'], 'integer'],
            [['cash'], 'number'],
            //[['created_at', 'updated_at'], 'safe'],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['account_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_id' => 'Account ID',
            'cash' => 'Cash',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Account]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'account_id']);
    }

    /**
     * Save operation
     * @param Account $account
     * @param float $sum
     * @param int $direction
     * @param DateTime $date
     */
    public static function create(Account $account, $sum, $direction, DateTime $date)
    {
        $operation = new Operation([
            'account_id' => $account->id,
            'direction' => $direction,
            'cash' => $sum,
            'created_at' => $date->format('Y-m-d H:i:s'),
        ]);
        $operation->save();
    }

    /**
     * Create sub query to check if operation was done early (protect operation duplicate if script was restarted)
     * @param DateTime $date
     * @param int $direction
     * @return \yii\db\ActiveQuery
     */
    public static function getOperationSubQuery(DateTime $date, $direction)
    {
        return Operation::find()
            ->select('COUNT(*)')
            ->where([
                'and',
                [Operation::tableName() . '.account_id' => new Expression(Account::tableName() . '.id')],
                ['=', Operation::tableName() . '.direction', $direction],
                ['>=', Operation::tableName() . '.created_at', $date->format('Y-m-d')],
            ]);
    }

    /**
     * Create report by months
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getReportSumProfitByMonths()
    {
        return Yii::$app->db->createCommand("
            SELECT 
                LEFT(created_at, 7) as yearmonth,
                SUM(CASE WHEN direction = " . self::DIRECTION_ACCRUAL . " THEN cash END) as accrualsum,
                SUM(CASE WHEN direction = " . self::DIRECTION_COMMISSION . " THEN cash END) as commissionsum
            FROM 
                " . self::tableName() . " 
            GROUP BY LEFT(created_at, 7)
        ")->queryAll();
    }
}
