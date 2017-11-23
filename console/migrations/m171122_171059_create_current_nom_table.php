<?php

use yii\db\Migration;

/**
 * Создаем таблицу `current_nom` для хренения актуальной номенклатуры.
 */
class m171122_171059_create_current_nom_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('current_nom', [
            'id' => $this->primaryKey(),
	        'good_1c_id' => $this->string(5)->notNull()->comment('идентификатор в системе 1с'),
	        'guid_1c' => $this->string(36)->notNull()->comment('guid идентификатор в 1с')
        ]);

        $this->addCommentOnTable('current_nom', 'Таблица актуальной номенклатуры');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('current_nom');
    }
}
