<?php
/**
 * Модель таблицы `phone`
 */
namespace backend\models;

/**
 * @property integer $id
 * @property integer $customer_id
 * @property string $phone
 * @property integer $sort
 */
class Phone extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'phone';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id'], 'required'],
            [['customer_id', 'sort'], 'integer'],
            [['phone'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'phone' => 'Phone',
            'sort' => 'Sort',
        ];
    }

	/**
	 * Получаем следующий номер в сортировке
	 * @return false|int|null|string
	 */
    public function getSortNumber(){
    	$sort = Phone::find()->select('sort')
	        ->where(['customer_id' => $this->customer_id])
		    ->orderBy(['sort' => SORT_DESC])
		    ->limit(1)
		    ->scalar();

    	return $sort + 1;
    }
}
