<?php
/**
 * Добавляем новые права и меняем старые зависимости
 */
use yii\db\Migration;

class m171014_104435_change_operator extends Migration
{
    public function up()
    {
	    $this->insert('auth_item', [
	    	'name' => 'telephone',
		    'type' => 1,
		    'description' => 'Telephone operator'
	    ]);

	    $this->insert('auth_item_child', ['parent' => 'admin', 'child' => 'telephone']);
    }

    public function down()
    {
    	// меняем пользователям права
    	$this->update('auth_assignment', ['item_name' => 'user'], ['item_name' => 'telephone']);
    	// удаляем нововведения
	    $this->delete('auth_item_child', ['parent' => 'admin', 'child' => 'telephone']);
    	$this->delete('auth_item', ['name' => 'telephone']);
    }
}
