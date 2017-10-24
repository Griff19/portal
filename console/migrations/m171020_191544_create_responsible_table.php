<?php

use yii\db\Migration;

/**
 * Создание таблицы `responsible` для хранения лиц, ответсвенных за заявку для каждого контрагента.
 */
class m171020_191544_create_responsible_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('responsible', [
            'id' => $this->primaryKey(),
	        'customer_id' => $this->integer()->notNull()->comment("идентификатор контрагента"),
	        'name' => $this->string(128)->notNull()->comment("фио ответственного"),
	        'position' => $this->string(32)->comment("должность"),
	        'sort' => $this->smallInteger()->comment("порядок сортировки"),
        ]);

        $this->addCommentOnTable('responsible', "Таблица ответственных за заявку лиц по контрагентам");
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('responsible');
    }
}
