<?php

namespace app\commands;

use app\models\Account;
use app\models\Operation;
use DateTime;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command process the deposits
 */
class DailyHandlerController extends Controller
{
    /**
     * Process range of days since 2019-01-01 (safe restart)
     * start one time to fill the range
     * console: php yii daily-handler/generate
     * @return int
     * @throws \Exception
     */
    public function actionGenerate()
    {
        echo 'process all days since 2019-01-01...' . "\n";

        $date = new DateTime('2019-01-01');
        while ($date->format('Y-m-d') <= date('Y-m-d')) {
            $this->accrual($date);
            $this->commission($date);
            $date->modify('+1 day');
        }

        return ExitCode::OK;
    }

    /**
     * Process one current day (safe restart)
     * cron task once a day
     * console: php yii daily-handler
     * @return int
     * @throws \Exception
     */
    public function actionIndex()
    {
        echo 'process current day...' . "\n";

        $date = new DateTime();
        $this->accrual($date);
        $this->commission($date);

        return ExitCode::OK;
    }

    /**
     * Process accruals
     * @param DateTime $date
     */
    private function accrual(DateTime $date)
    {
        // create sub query to check if operation was done early (protect accrual duplicates if script was restarted)
        $operationQuery = Operation::getOperationSubQuery($date, Operation::DIRECTION_ACCRUAL);

        // create main account query
        // if current day is 28/29/30 and it is the last day of the month but other deposits can
        // have day upper then current day so it is necessary to include other days 29/30/31
        $accountQuery = Account::find()
            ->where([
                'and',
                ['<', 'created_at', $date->format('Y-m-d')],
                [$this->isLastDayOfMonth($date) ? '>=' : '=', 'DAY(created_at)', $this->getCurrentDay($date)],
                ['=', 0, $operationQuery],
            ])
            ->limit(100);

        while ($accounts = $accountQuery->all()) {
            foreach ($accounts as $account) {
                $sum = round($account->makeAccrual($date), 2);
                echo "+{$sum} {$date->format('Y-m-d')} #{$account->id}\n";
            }
        }
    }

    /**
     * Process commissions
     * @param DateTime $date
     */
    private function commission($date)
    {
        if ($this->isFirstDayOfMonth($date)) {
            // create sub query to check if operation was done early (protect commission duplicates if script was restarted)
            $operationQuery = Operation::getOperationSubQuery($date, Operation::DIRECTION_COMMISSION);

            // create main account query
            $accountQuery = Account::find()
                ->where([
                    'and',
                    ['<', 'created_at', $date->format('Y-m-d')],
                    ['=', 0, $operationQuery],
                ])
                ->limit(100);

            while ($accounts = $accountQuery->all()) {
                foreach ($accounts as $account) {
                    $sum = round($account->makeCommission($date), 2);
                    echo "-{$sum} {$date->format('Y-m-d')} #{$account->id}\n";
                }
            }
        }
    }

    /**
     * Get day from date
     * @param DateTime $date
     * @return int
     */
    private function getCurrentDay(DateTime $date)
    {
        return (int)$date->format('j');
    }

    /**
     * Check if the day is first in month
     * @param DateTime $date
     * @return bool
     */
    private function isFirstDayOfMonth(DateTime $date)
    {
        return $this->getCurrentDay($date) == 1;
    }

    /**
     * Check if the day is last in month
     * @param DateTime $date
     * @return bool
     */
    private function isLastDayOfMonth(DateTime $date)
    {
        return $this->getCurrentDay($date) == $date->format('t');
    }
}
