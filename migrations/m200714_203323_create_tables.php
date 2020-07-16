<?php

use yii\db\Migration;

/**
 * Class m200714_203323_create_tables
 */
class m200714_203323_create_tables extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('client', [
            'id' => $this->primaryKey(),
            'id_code' => $this->bigInteger(12)->notNull()->unique(),
            'name' => $this->string()->notNull(),
            'surname' => $this->string()->notNull(),
            'gender' => $this->tinyInteger(1)->notNull(),
            'birthday' => $this->date(),
        ], $tableOptions);

        $this->createTable('account', [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer()->notNull(),
            'cash' => $this->decimal(10, 2)->notNull(),
            'percent' => $this->decimal(5, 2)->notNull(),
            'created_at' => $this->datetime()->notNull(),
            'updated_at' => $this->datetime(),
        ], $tableOptions);

        $this->createTable('operation', [
            'id' => $this->primaryKey(),
            'account_id' => $this->integer()->notNull(),
            'cash' => $this->decimal(10, 2)->notNull(),
            'direction' => $this->tinyInteger(1)->notNull(),
            'created_at' => $this->datetime()->notNull(),
            'updated_at' => $this->datetime(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-account-client_id',
            'account',
            'client_id',
            'client',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-operation-account_id',
            'operation',
            'account_id',
            'account',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk-operation-account_id', 'operation');
        $this->dropForeignKey('fk-account-client_id', 'account');

        $this->dropTable('operation');
        $this->dropTable('account');
        $this->dropTable('client');
    }
}
