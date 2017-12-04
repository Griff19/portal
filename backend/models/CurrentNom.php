<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "current_nom".
 *
 * @property integer $id
 * @property string $good_1c_id
 * @property string $guid_1c
 */
class CurrentNom extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'current_nom';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['good_1c_id', 'guid_1c'], 'required'],
            [['good_1c_id'], 'string', 'max' => 11],
            [['guid_1c'], 'string', 'max' => 36],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'good_1c_id' => 'Good 1c ID',
            'guid_1c' => 'Guid 1c',
        ];
    }
}
