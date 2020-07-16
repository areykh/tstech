<?php

/* @var $this yii\web\View */

$this->title = 'TS Tech';
?>
<div class="site-index">
    <div class="body-content">
        <h2>Report by months</h2>
        <table class="table table-bordered table-striped">
            <tr>
                <th>Year-month</th>
                <th>Accrual sum</th>
                <th>Commission sum</th>
            </tr>
            <?php foreach ($report['profitByMonths'] as $rows) { ?>
                <tr>
                    <?php foreach ($rows as $cell) { ?>
                        <td><?= $cell ?></td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </table>

        <h2>Report by birthday</h2>
        <table class="table table-bordered table-striped">
            <tr>
                <th>Client age</th>
                <th>Average deposit sum</th>
            </tr>
            <?php foreach ($report['avgByBirthday'] as $rows) { ?>
                <tr>
                    <?php foreach ($rows as $cell) { ?>
                        <td><?= $cell ?></td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
