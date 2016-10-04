<?php

namespace backend\models;

use Yii;


/**
 * This is the model class for table "acts_doc".
 *
 * @property integer $id_doc
 * @property integer $type_act
 * @property string $begdate
 * @property string $enddate
 * @property string $num_act
 * @property string $contr_doc
 * @property integer $control
 * @property integer $user_id
 * @property integer $beg_sald
 * @property integer $end_sald
 */
class ActsDoc extends \yii\db\ActiveRecord
{
    public $file;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'acts_doc';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_act', 'begdate', 'enddate', 'num_act', 'contr_doc', 'user_id'], 'required'],
            [['type_act', 'control', 'user_id', 'beg_sald', 'end_sald'], 'integer'],
            [['begdate', 'enddate'], 'safe'],
            [['num_act'], 'string', 'max' => 11],
            [['contr_doc'], 'string', 'max' => 9],
            [['good'], 'string', 'max' => 255],
            ['file','file']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_doc' => 'Id Doc',
            'type_act' => 'Тип акта',
            'num_act' => 'Номер акта',
            'begdate' => 'Дата Нач.',
            'enddate' => 'Дата Кон.',
            
            'contr_doc' => 'Контрагент',
            'control' => 'Контроль',
            'user_id' => 'Пользователь',
            'beg_sald' => 'Сальдо нач.',
            'end_sald' => 'Сальдо кон.',
            'good' => 'Номенклатура',
            'file' => 'Файл документов'
        ];
    }

    /**
     * Привязывемся к модели пользователей
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Привязываемся к таблице контрагентов
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customers::className(), ['customer_1c_id' => 'contr_doc']);
    }
}
