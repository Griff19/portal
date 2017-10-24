<?php
/**
 * Создаем таблицу `phone` для хранения телефонов контрагентов.
 */
use yii\db\Migration;

class m171017_171348_create_phone_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('phone', [
            'id' => $this->primaryKey(),
	        'customer_id' => $this->integer()->notNull()->comment('идентификатор контрагента'),
	        'phone' => $this->string(10)->comment('номер телефона'),
	        'sort' => $this->smallInteger()->comment('порядок сортировки')
        ]);

        $this->addCommentOnTable('phone', 'Таблица телефонов контрагентов');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('phone');
    }
}
