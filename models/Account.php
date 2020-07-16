<?php

namespace app\models;

use DateTime;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "account".
 *
 * @property int $id
 * @property int $client_id
 * @property float $cash
 * @property float $percent
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Client $client
 * @property Operation[] $operations
 */
class Account extends \yii\db\ActiveRecord
{
    const COMMISSION_MIN_SUM = 50;
    const COMMISSION_MAX_SUM = 5000;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'account';
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
            [['client_id', 'cash', 'percent', /*'created_at'*/], 'required'],
            [['client_id'], 'integer'],
            [['cash', 'percent'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::class, 'targetAttribute' => ['client_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_id' => 'Client ID',
            'cash' => 'Cash',
            'percent' => 'Percent',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Client]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    /**
     * Gets query for [[Operations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOperations()
    {
        return $this->hasMany(Operation::class, ['account_id' => 'id']);
    }

    /**
     * Calculate accrual sum
     * @return float|int
     */
    private function calculateAccrual()
    {
        return $this->cash * $this->percent / 100;
    }

    /**
     * Add accrual sum and save account and operation
     * @param $date
     * @return float|int
     */
    public function makeAccrual($date)
    {
        $sum = $this->calculateAccrual();

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->cash += $sum;
            $this->save();

            Operation::create($this, $sum, Operation::DIRECTION_ACCRUAL, $date);

            $transaction->commit();

            return $sum;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            $transaction->rollBack();
        } catch (\Throwable $e) {
            Yii::error($e->getMessage());
            $transaction->rollBack();
        }
        return 0;
    }

    /**
     * Calculate commission sum
     * @param DateTime $date
     * @return float|int
     * @throws \Exception
     */
    private function calculateCommission(DateTime $date)
    {
        if ($this->cash < 1000) {
            // 5%
            $commissionSum = $this->cash * 0.05;
            if ($commissionSum < self::COMMISSION_MIN_SUM) {
                $commissionSum = self::COMMISSION_MIN_SUM;
            }
        } elseif ($this->cash < 10000) {
            // 6%
            $commissionSum = $this->cash * 0.06;
        } else {
            // 7%
            $commissionSum = $this->cash * 0.07;
            if ($commissionSum > self::COMMISSION_MAX_SUM) {
                $commissionSum = self::COMMISSION_MAX_SUM;
            }
        }

        $currentDate = clone $date;
        $currentDate->modify('-1 month');
        $createdAt = new DateTime($this->created_at);
        if ($currentDate->format('Y-m') == $createdAt->format('Y-m')) {
            $numberOfDaysInMonth = (int)$createdAt->format('t');
            $commissionDays = $numberOfDaysInMonth - (int)$createdAt->format('j') + 1;
            $commissionSum *= $commissionDays / $numberOfDaysInMonth;
        }

        return $commissionSum;
    }

    /**
     * Deduct commission sum and save account and operation
     * @param DateTime $date
     * @return float|int
     * @throws \Exception
     */
    public function makeCommission(DateTime $date)
    {
        $sum = $this->calculateCommission($date);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->cash -= $sum;
            $this->save();

            Operation::create($this, $sum, Operation::DIRECTION_COMMISSION, $date);

            $transaction->commit();

            return $sum;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            $transaction->rollBack();
        } catch (\Throwable $e) {
            Yii::error($e->getMessage());
            $transaction->rollBack();
        }
        return 0;
    }

    /**
     * Create report by age
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getReportAvgByBirthday()
    {
        return Yii::$app->db->createCommand("
            SELECT 
                '18-25' as agerange,
                AVG(" . self::tableName() . ".cash) as avgsum
            FROM 
                " . self::tableName() . " 
            LEFT JOIN 
                " . Client::tableName() . " ON " . Client::tableName() . ".id = " . Account::tableName() . ".client_id
            WHERE 
                (
                    (YEAR(CURRENT_DATE) - YEAR(" . Client::tableName() . ".birthday)) -
                    (DATE_FORMAT(CURRENT_DATE, '%m%d') < DATE_FORMAT(birthday, '%m%d'))
                ) BETWEEN 18 AND 24
                
            UNION ALL
            
            SELECT 
                '25-50' as agerange,
                AVG(" . self::tableName() . ".cash) as avgsum
            FROM 
                " . self::tableName() . " 
            LEFT JOIN 
                " . Client::tableName() . " ON " . Client::tableName() . ".id = " . Account::tableName() . ".client_id
            WHERE 
                (
                    (YEAR(CURRENT_DATE) - YEAR(" . Client::tableName() . ".birthday)) -
                    (DATE_FORMAT(CURRENT_DATE, '%m%d') < DATE_FORMAT(birthday, '%m%d'))
                ) BETWEEN 25 AND 49
                
            UNION ALL
            
            SELECT 
                '50 <' as agerange,
                AVG(" . self::tableName() . ".cash) as avgsum
            FROM 
                " . self::tableName() . " 
            LEFT JOIN 
                " . Client::tableName() . " ON " . Client::tableName() . ".id = " . Account::tableName() . ".client_id
            WHERE 
                (
                    (YEAR(CURRENT_DATE) - YEAR(" . Client::tableName() . ".birthday)) -
                    (DATE_FORMAT(CURRENT_DATE, '%m%d') < DATE_FORMAT(birthday, '%m%d'))
                ) >= 50;
            
        ")->queryAll();
    }
}
