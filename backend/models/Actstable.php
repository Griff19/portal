<?php

namespace backend\models;

use Yii;

/**
 * модель для таблицы "acts_table".
 *
 * @property integer $id
 * @property integer $acts_id
 * @property string $act_num
 * @property string $date_doc
 * @property string $num_doc
 * @property string $name_doc
 * @property string $cod_good
 * @property integer $beg_sald
 * @property integer $end_sald
 * @property string $actstable_comm
 * @property string $actstable_img
 */
class ActsTable extends \yii\db\ActiveRecord
{
    public $file;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'acts_table';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['acts_id', 'act_num', 'date_doc', 'num_doc', 'name_doc', 'beg_sald', 'end_sald'], 'required'],
            [['acts_id', 'beg_sald', 'end_sald'], 'integer'],
            [['date_doc'], 'safe'],
            [['actstable_comm'], 'string'],
            [['act_num'], 'string', 'max' => 18],
            [['num_doc', 'cod_good'], 'string', 'max' => 11],
            [['name_doc'], 'string', 'max' => 30],
            [['actstable_img'], 'string', 'max' => 255],
            [['file'],'file', 'extensions' => ['jpeg','jpg'], 'checkExtensionByMimeType'=>false, 'skipOnEmpty'=>true]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'acts_id' => 'Acts ID',
            'act_num' => '№ Акта',
            'date_doc' => 'Дата док.',
            'num_doc' => '№ Док.',
            'name_doc' => 'Имя док.',
            'cod_good' => 'Номенклатура',
            'beg_sald' => 'Нач. сальдо',
            'end_sald' => 'Кон. сальдо',
            'actstable_comm' => 'Комментарий',
            'file' => 'Файл:'
        ];
    }
}
