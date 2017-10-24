<?php
/**
 * Модель для таблицы Ответственный по контрагенту
 */
namespace backend\models;

/**
 * @property integer $id
 * @property integer $customer_id
 * @property string $name
 * @property string $position
 * @property integer $sort
 * @property integer $sortNumber следующий номер в порядке сортировки
 * @property Customers $customer
 */
class Responsible extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'responsible';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'name'], 'required'],
            [['customer_id', 'sort'], 'integer'],
            [['name'], 'string', 'max' => 128],
            [['position'], 'string', 'max' => 32],
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
            'name' => 'Name',
            'position' => 'Position',
            'sort' => 'Sort',
        ];
    }

	/**
	 * Связываем с моделью Контрагенты
	 * @return \yii\db\ActiveQuery
	 */
    public function getCustomer(){
    	return $this->hasOne(Customers::className(), ['customer_id' => 'customer_id']);
    }

	/**
	 * Получаем следующий номер в сортировке
	 * @return false|int|null|string
	 */
	public function getSortNumber(){
		$sort = Responsible::find()->select('sort')
			->where(['customer_id' => $this->customer_id])
			->orderBy(['sort' => SORT_DESC])
			->limit(1)
			->scalar();

		return $sort + 1;
	}
}
