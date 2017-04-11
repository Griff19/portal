<?php

use yii\db\Migration;

class m170408_160644_edit_good_logo_in_good extends Migration
{
    public function up()
    {
        $this->dropColumn('goods', 'good_logo');
        $this->addColumn('goods', 'good_logo', $this->integer());
        $this->addCommentOnColumn('goods', 'good_logo', 'Идентификатор картинки');
    }

    public function down()
    {
        $this->dropColumn('goods', 'good_logo');
        $this->addColumn('goods', 'good_logo', $this->string());
        $this->dropCommentFromColumn('goods', 'good_logo');
    }
}
