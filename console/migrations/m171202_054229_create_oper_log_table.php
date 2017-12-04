<?php

use yii\db\Migration;

/**
 * Создаем таблицу для хренения лога работы оператора `oper_log`.
 */
class m171202_054229_create_oper_log_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('oper_log', [
            'id' => $this->primaryKey(),
			'created_at' => $this->integer(11)->comment('время создания записи'),
			'user_id' => $this->integer()->comment('идентификатор пользователя'),
			'action' => $this->string(255)->comment('действие или событие инициированное оператором'),
			'desc' => $this->string(255)->comment('описание')
        ]);
        
        $this->addCommentOnTable('oper_log', 'Таблица для хранения действий оператора');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('oper_log');
    }
}
