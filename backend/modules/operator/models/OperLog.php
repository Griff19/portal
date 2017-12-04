<?php
/**
 * Модель таблицы "Лог работы оператора" oper_log
 */
namespace backend\modules\operator\models;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property integer $created_at
 * @property integer $user_id
 * @property string $action
 * @property string $desc
 */
class OperLog extends \yii\db\ActiveRecord
{
    const ACTION_NEXT_CUSTOMER = 'Пропуск контрагента';
	
	public $updated_at; //заглушка
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oper_log';
    }
	
	/**
	 * Заполняем дату создания и идентификатор пользователя при сохранении модели
	 * @return array
	 */
    public function behaviors() {
		$b = parent::behaviors();
		$b[] = ['class' => 'yii\behaviors\TimestampBehavior'];
		$b[] = ['class' => AttributeBehavior::className(),
				'attributes' => [ActiveRecord::EVENT_BEFORE_INSERT => 'user_id'],
				'value' => function (){ return Yii::$app->user->id; }
			];
		return $b;
	}
	
	/**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'user_id'], 'integer'],
            [['action', 'desc'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'user_id' => 'User ID',
            'action' => 'Action',
            'desc' => 'Причина',
        ];
    }
}
