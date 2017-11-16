<?php

use yii\db\Migration;

/**
 * Создание таблицы "Типичный заказ" для хранения позиций, которые обычно заказывает контрагент
 */
class m171111_181250_create_typical_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('typical_order', [
            'id' => $this->primaryKey(),
	        'customer_id' => $this->integer()->notNull()->comment('идентификатор контрагента'),
	        'good_id' => $this->integer()->notNull()->comment('идентификатор товара'),
	        'good_hash' => $this->string(11)->notNull()->comment('хеш товара'),
	        'count' => $this->integer()->comment('количество')
        ]);

        $this->addCommentOnTable('typical_order', 'Таблица хранит данные о типичном заказе для контрагента');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('typical_order');
    }
}
