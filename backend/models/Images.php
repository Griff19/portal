<?php

namespace backend\models;

use Yii;
use backend\helpers\Logs;

/**
 * This is the model class for table "images".
 *
 * @property integer $id
 * @property string $img_oldname
 * @property string $img_newname
 * @property string $img_owner
 * @property string $img_title
 */
class Images extends \yii\db\ActiveRecord
{
    public $file;
    public static $IMG_DIR = 'imgs/'; //каталог картинок
    public static $TMP_DIR = 'imgs/tmp/'; //каталог временных файлов
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'images';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['img_oldname', 'img_newname', 'img_owner'], 'required'],
            [['img_oldname', 'img_newname', 'img_owner'], 'string', 'max' => 255],
            ['img_title', 'string', 'max' => 1024]
        ];
    }

    public function imageresize($outfile,$infile,$percents,$quality) {
        try {
            $im = imagecreatefromjpeg($infile);
            //imagecreatefrompng($infile);
        } catch (\ErrorException $e){
            Logs::add('Ошибка обработки изображения: ' . $infile);
            return false;
        }
        $w = imagesx($im) * $percents / 100;
        $h = imagesy($im) * $percents / 100;
        $im1 = imagecreatetruecolor($w, $h);
        imagecopyresampled($im1, $im, 0, 0, 0, 0, $w, $h, imagesx($im), imagesy($im));

        imagejpeg($im1, $outfile, $quality);
        imagedestroy($im);
        imagedestroy($im1);
        return true;
    }

    /**
     * Сохраняеи изображение заменяя старое
     * @param file $file
     * @param string $owner
     * @return int
     */
    public function createImg($file, $owner)
    {
        $oldname = $file->name;
        $filename = md5($file->baseName) .'.'. $file->extension;
        $tmpname = self::$TMP_DIR . $filename;
        $newname = self::$IMG_DIR . $filename;
        $file->saveAs($tmpname);
        if (!self::imageresize($newname, $tmpname, 100, 100)){
            return false;
        }
        unlink($tmpname);
        //в данный момент заменяем старую картинку на новую загруженную
        //в будущем можно будет загружать много картинок для одного объекта
        $img = Images::find()->where(['img_owner' => $owner])->one();
        if ($img){
            $model = Images::findOne($img->id);
            $model->img_newname = $newname;
            $model->img_oldname = $oldname;
            $model->img_owner = $owner;
            $model->save();
        } else {
            $model = new Images();
            $model->img_newname = $newname;
            $model->img_oldname = $oldname;
            $model->img_owner = $owner;
            $model->save();
        }
        Logs::add('Сохранено изображение: ' . $model->img_oldname . ' - ' . $model->img_newname);
        return true;
    }

    /**
     * Создаем и сохраняем изображение добавляя к уже существующим
     * @param $file
     * @param $owner
     * @return bool
     */
    public function createImgs($file, $owner)
    {
        $oldname = $file->name;
        $filename = md5($file->baseName) .'.'. $file->extension;
        $tmpname = self::$TMP_DIR . $filename;
        $newname = self::$IMG_DIR . $filename;
        $file->saveAs($tmpname);
        if (!self::imageresize($newname, $tmpname, 100, 100)){
            return false;
        }
        unlink($tmpname);

        $model = new Images();
        $model->img_newname = $newname;
        $model->img_oldname = $oldname;
        $model->img_owner = $owner;
        $model->save();

        Logs::add('Сохранено изображение: ' . $model->img_oldname . ' - ' . $model->img_newname);
        return true;
    }

    /**
     * Возвращает одну ссылку на изображение
     * @param $owner
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getImage($owner)
    {
        $imgs = Images::find()->where(['img_owner' => $owner])->all();
        if (!empty($imgs)){
            return '/'.$imgs[0]->img_newname;
        } else {
            return '';
        }
    }

    /**
     * Возвращает массив со ссылками на изображения
     * @param $owner
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getImages($owner){
        $imgs = Images::find()->where(['img_owner' => $owner])->all();
        //var_dump($imgs);
        //die;
        return $imgs;
    }

    /**
     * @param $owner
     * @return mixed|string
     */
    public static function getTitle($owner){
        $image = Images::find()->where(['img_owner' => $owner])->all();
        if (!empty($image))
            return $image[0]->img_title;
        else
            return '';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'img_oldname' => 'Старое имя файла',
            'img_newname' => 'Новое имя файла',
            'img_owner' => 'Владелец',
            'img_title' => 'Описание',
        ];
    }
}
