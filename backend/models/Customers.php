<?php
/**
 * Модель таблицы `customers` - Контрагенты.
 */
namespace backend\models;

/**
 * @property integer customer_1c_id
 * @property integer customer_id
 * @property integer user_id
 * @property string customer_name
 * @property string customer_email
 * @property integer typeprices_id
 * @property Orders[] $orders
 * @property string inn
 * @property string directPhone
 * @property string directResponsible
 * @property TypicalOrder typicalOrder
 */
class Customers extends \yii\db\ActiveRecord
{
    const ACCOUNT_ACTIVATE = 'activate';
    public $file;
    public $capch;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_1c_id','customer_name'], 'required'],
            [['user_id','typeprices_id'], 'integer'],
            [['customer_name'], 'string', 'max' => 200],
            ['customer_email', 'email'],
            ['customer_1c_id','string','max' => 10],
            ['inn', 'string', 'max' => 12],
            [['file'],'file'],
            [['customer_email', 'inn'], 'findAttr', 'on' => self::ACCOUNT_ACTIVATE],
            ['capch', 'captcha', 'on' => self::ACCOUNT_ACTIVATE]
        ];
    }

    public function scenarios(){
        $scenario = parent::scenarios();
        $scenario[self::ACCOUNT_ACTIVATE] = ['customer_email', 'inn', 'capch'];
        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer_1c_id' => 'Код контрагента в 1С',
            'customer_id' => 'Customer ID',
            'user_id' => 'Имя пользователя',
            'customer_name' => 'Контрагент',
            'customer_email' => 'Email',
            'file' => 'Файл с данными по контрагентам',
            'typeprices_id' => 'Тип цен',
            'inn' => 'ИНН',
            'capch' => 'Каптча:',
	        'directPhone' => 'Телефон',
	        'directResponsible' => 'Ответственный'
        ];
    }

    /**
     * Валидация введенных данных при Активации аккаунта
     * @param $attribute
     * @param $params
     */
    public function findAttr($attribute, $params) {
        $err = false;
        $model = Customers::findOne([$attribute => $this->$attribute]);
        if ($model) {
            if ($attribute == 'customer_email') {
                if ($model->inn != $this->inn)
                    $err = true;
            }
            if ($attribute == 'inn') {
                if ($model->customer_email != $this->customer_email)
                    $err = true;
            }
        } else $err = true;

        if ($err) {
            $this->addError('customer_email', '');
            $this->addError('inn', 'Email и ИНН не соответствуют');
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Orders::className(), ['customers_customer_id' => 'customer_id']);
    }
    /**
     * Получаем массив идентификаторов типов цен
     * @param integer $user_id
     * @return Array
     */
    public function getTP($user_id)
    {
        $custom = $this->find()->where(['user_id' => $user_id])->all();
        foreach ($custom as $tp){
            $arr[] = $tp->typeprices_id;
        }
        //print_r($arr);
        //die();
        return $arr;
    }
    
    public function getTypePrices(){
        return $this->hasOne(Typeprice::className(), ['type_price_id' => 'typeprices_id']);
    }

	/**
	 * Связываем с моделью пользователей
	 * @return \yii\db\ActiveQuery
	 */
    public function getUsersUser(){
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

	/**
	 * Связываем с моделью телефонов
	 * @return \yii\db\ActiveQuery
	 */
    public function getPhone(){
    	return $this->hasMany(Phone::className(), ['customer_id' => 'customer_id']);
    }

	/**
	 * Связываем с моделью ответственных лиц
	 * @return \yii\db\ActiveQuery
	 */
    public function getResponsible(){
    	return $this->hasMany(Responsible::className(), ['customer_id' => 'customer_id']);
    }

    /**
     * Связываем с моделью Типичных заказов
     */
    public function getTypicalOrder(){
        return $this->hasMany(TypicalOrder::className(), ['customer_id' => 'customer_id']);
    }

	/**
	 * Получаем основной телефонный номер для текущего контрагента
	 * @return false|null|string
	 */
    public function getDirectPhone(){
    	return Phone::find()->select('phone')->where(['customer_id' => $this->customer_id])
		    ->limit(1)->orderBy('sort')->scalar();
    }

	/**
	 * Получаем основное ответственное лицо по контрагенту (sort = 1)
	 * @return false|null|string
	 */
    public function getDirectResponsible(){
    	return Responsible::find()->select('name')->where(['customer_id' => $this->customer_id])
		    ->limit(1)->orderBy('sort')->scalar();
    }

    /**
     * Получаем идентификатор пользователя
     * @param $_1c_id
     * @return mixed|null
     */
    public function getUserid($_1c_id)
    {
        $model = $this->find()->where(['customer_1c_id' => $_1c_id])->one();
        if ($model !== NULL){
            return $model->user_id;
        } else {
            return NULL;
        }
    }
}
