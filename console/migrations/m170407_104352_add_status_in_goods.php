<?php

use yii\db\Migration;

class m170407_104352_add_status_in_goods extends Migration
{
    public function up()
    {
        $this->addColumn('goods', 'status', $this->integer());
        $this->addCommentOnColumn('goods', 'status', 'Определяет отображать номенклатуру или нет');
    }

    public function down()
    {
        $this->dropColumn('goods', 'status');
    }
}
