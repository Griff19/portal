<?php

use yii\db\Migration;

/**
 * Добавляем новые поля в таблицу "Контрагенты" `customers`.
 */
class m171129_121627_add_status_column_to_customers_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
		/** @var Migration $this */
		$this->addColumn('customers', 'status', $this->integer()->defaultValue(1)->comment('статус контрагента'));
        $this->addColumn('customers', 'min_amount', $this->integer()->defaultValue(0)->comment('сумма минимального заказа для контрагента'));
        $this->addColumn('customers', 'sort', $this->integer()->defaultValue(0)->comment('сортировка контрагентов в зависимости от времени'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('customers', 'status');
        $this->dropColumn('customers', 'min_amount');
        $this->dropColumn('customers', 'sort');
    }
}
