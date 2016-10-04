<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "acts".
 *
 * @property integer $id
 * @property string $num
 * @property string $link
 * @property string $begdate
 * @property string $enddate
 * @property integer $users_user_1c_id
 * @property string $customers_customer_1c_id
 * @property string $begenddate
 * @property string $typedoc
 */
class Acts extends \yii\db\ActiveRecord
{
    
    public $filereestr;
    public $filezip;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'acts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['num', 'begdate', 'enddate', 'users_user_1c_id', 'customers_customer_1c_id', 'begenddate','link','typedoc'], 'required'],
            //[['begdate', 'enddate'], 'safe'],
            ['customers_customer_1c_id', 'string', 'max' => 9],
            ['num', 'string', 'max' => 18],
            ['begenddate', 'string', 'max' => 17],
            ['users_user_1c_id','string','max' => 36],
            [['link','typedoc'],'string','max' => 255],
            ['filezip','file','extensions' => ['zip']],
            ['filereestr','file','extensions' => ['txt']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'num' => 'Имя файла (№ документа)',
            'link' => 'Ссылка',
            'begdate' => 'Начало периода',
            'enddate' => 'Конец периода',
            'users_user_1c_id' => 'Пользователь',
            'customers_customer_1c_id' => 'Код контрагента в 1С',
            'begenddate' => 'Begenddate',
            'typedoc' => 'Тип документа',
        ];
    }
    
    public function getUsersUser(){
        return $this->hasOne(User::className(), ['_1c_id' => 'users_user_1c_id']);
    }
}
