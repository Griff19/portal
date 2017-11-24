<?php

namespace backend\controllers;

use Yii;
use backend\models\User;
use backend\models\Goods;
use backend\models\GoodsSearch;
use backend\models\Typeprice;
use backend\models\Customers;
use backend\models\FtpWork;
use backend\models\Images;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\helpers\Html;

/**
 * GoodsController implements the CRUD actions for Goods model.
 */
class GoodsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'catalog'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['create', 'update', 'delete', 'uploadform', 'set-img', 'download', 'upload',
                            'change-status'],
                        'allow' => true,
                        'roles' => ['operator'],
                    ],
	                [
	                	'actions' => ['search-good'],
		                'allow' => true,
		                'roles' => ['telephone']
	                ],

                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'search-good' => ['post']
                ],
            ],
        ];
    }

    /**
     * Обычный список товаров
     * @return mixed
     */
    public function actionIndex($order_id = 0)
    {
        $searchModel = new GoodsSearch();
        if (Yii::$app->user->can('operator')) {
            $tp = 0;
        } else {

            if ((Yii::$app->user->can('subuser')) && !(Yii::$app->user->can('user'))) {
                $user = new User();
                $tp = $user->getParent(Yii::$app->user->id);

            } else {
                $customers = new Customers();
                $tp = $customers->getTP(Yii::$app->user->id);
            }
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $tp);
        if ($tp == 0)
            return $this->render('index_adm', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'order_id' => $order_id,
            ]);
        else
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'order_id' => $order_id,
            ]);
    }

    /**
     * Новое представление списка номенклатуры в виде каталога
     * @return string
     */
    public function actionCatalog()
    {
        $searchModel = new GoodsSearch();
        if (Yii::$app->user->can('operator')) {
            $tp = 0;
        } else {
            if ((Yii::$app->user->can('subuser')) && !(Yii::$app->user->can('user'))) {
                $user = new User();
                $tp = $user->getParent(Yii::$app->user->id);

            } else {
                $customers = new Customers();
                $tp = $customers->getTP(Yii::$app->user->id);
            }
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $tp);

        if ($tp == 0)
            return $this->render('catalog_adm', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        else
            return $this->render('catalog', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
    }

    /**
     * Отображение товара
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Добавление нового товара вручную
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Goods();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->discount) $model->status = Goods::DISCOUNT;
            $model->good_price = $model->good_price_real * 100;
            $model->save();
            return $this->redirect(['view', 'id' => $model->good_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'upload' => false,
            ]);
        }
    }

    /**
     * Разбираем файл и заносим данные в таблицу `goods`
     */
    public function readfile()
    {
        ini_set('max_execution_time', 420);
        ini_set('memory_limit', '250M');
        $filename = 'GoodsFile/GoodsPrice.txt';
        $readfile = fopen($filename, 'r');
        Goods::updateAll(['status' => 0]);
        while ($str = fgets($readfile)) {
            $items = explode(';', $str);
	        if (strpos($items[0], '~') !== false) continue;
            //убираем все непечатные символы из строки (могут встречаться вначале файла)
            //$items[0] = preg_replace('/[^a-zA-Zа-яА-ЯЁё0-9&\/ ]/u', '', $items[0]);

            $tp = Typeprice::find()->where(['type_price_name' => $items[4]])->one();

            if (!isset($tp)) { continue; }

            $price = $items[5]; //проводим махинации чтобы цена была в том виде в котором надо
            $price = str_replace(',', '.', $price);
            $price = preg_replace('/[^x\d|*\.]/', '', $price);

            $discount = stripos($items[3], 'акция') === false ? false : true;
            $hash = md5($items[0] . $items[2] . $items[4]);
            $hash = substr($hash, 0, 11);
            $good = Goods::find()->where(['hash_id' => $hash])->one();
            if (isset($good)) {
                //echo 'существует объект' . $goodfnd->good_name . '<br>';
                //$good = $this->findModel($goodfnd->good_id);
                $good->good_name = $items[1];
                //$good->good_1c_id = $items[0];
                //$good->good_detail_guid = $items[2];
                $good->good_description = $items[3];
                $good->good_price = $price * 100; //в базе цены хранятся в целом типе
                $good->typeprices_id = $tp->type_price_id;
                $good->status = $discount ? Goods::DISCOUNT : Goods::ENABLE;
                $good->save();
            } else {
                //echo 'создаем новый объект ' . $items[1] . '<br>';
                $good = new Goods();
                //echo 'cod: ';
                //var_dump($items[0]);
                $good->hash_id = $hash;
                $good->good_1c_id = $items[0];
                $good->good_name = $items[1];
                $good->good_detail_guid = $items[2];
                $good->good_description = $items[3];
                $good->good_price = $price * 100;
                $good->typeprices_id = $tp->type_price_id;
                $good->status = $discount ? Goods::DISCOUNT : Goods::ENABLE;
                $good->save();
            }
        }
        fclose($readfile);
    }

    /**
     * Загрузка файла с данными о товарах
     * @return string|\yii\web\Response
     */
    public function actionUploadform()
    {
        $model = new Goods();

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->file) {
                $fileName = 'GoodsPrice';
                $model->file->saveAs('GoodsFile/' . $fileName . '.' . $model->file->extension);
            }
            $this->readfile();
            //die();
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
                'upload' => true,
            ]);
        }
    }

    /**
     * Редактируем данные о товаре
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->discount = $model->status == Goods::DISCOUNT ? true : false;
        $model->good_price_real = $model->good_price / 100;
        if ($model->load(Yii::$app->request->post())) {
            $model->good_price = $model->good_price_real * 100;
            $model->status = $model->discount ? Goods::DISCOUNT : Goods::ENABLE;

            if (!$model->save())
                Yii::$app->session->setFlash('error', serialize($model->getErrors()));
            return $this->redirect(['view', 'id' => $model->good_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'upload' => false,
            ]);
        }
    }

    /**
     * Функция добавления изображения для товара (не используется).
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * public function actionSetimg($id)
     * {
     * $model = $this->findModel($id);
     * $img = $model->good_logo;
     * if ($model->load(Yii::$app->request->post())){
     * $model->file = UploadedFile::getInstance($model, 'file');
     * if ($model->file){
     * if (Images::createImg($model->file, Goods::tableName() . $model->hash_id)){
     * Yii::$app->session->setFlash('success', 'Изображение сохранено');
     * } else {
     * Yii::$app->session->setFlash('error', 'Изображение не загружено');
     * }
     * }
     * } else {
     * return $this->render('update', [
     * 'model' => $model,
     * 'upload' => true,
     * ]);
     * }
     * return $this->redirect(['index']);
     * }
     */

    /**
     * Привязка изображения к товару
     * @param $id
     * @param int $id_img
     * @return \yii\web\Response
     */
    public function actionSetImg($id, $id_img = null)
    {
        $model = $this->findModel($id);
        if ($id_img)
            $model->good_logo = $id_img;
        else
            $model->good_logo = 0;

        if (!$model->save())
            Yii::$app->session->setFlash('error', serialize($model->getErrors()));

        return $this->redirect(Url::previous());
    }

    /**
     * Удаление товара
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Установка статуса, отображение и сокрытие товара в каталоге
     * @param $id
     * @return \yii\web\Response
     */
    public function actionChangeStatus($id)
    {
        $model = $this->findModel($id);
        if ($model->status == Goods::DISABLE)
            $model->status = Goods::ENABLE;
        else
            $model->status = Goods::DISABLE;

        $model->save();

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Загрузка файла с ФТП
     * @return \yii\web\Response
     */
    public function actionDownload()
    {
        $fileloc = 'GoodsFile/GoodsPrice.txt';
        $fileftp = 'insite/GoodCost.txt';
        $ftp = new FtpWork();
        if ($ftp->download($fileftp, $fileloc)) {
            Yii::$app->session->setFlash('success', 'файл скачан');
        } else {
            Yii::$app->session->setFlash('error', 'файл скачан');
        }
        $this->readfile();
        return $this->redirect(['index']);
    }

    /**
     * Выгрузка файла на ФТП
     * @return \yii\web\Response
     */
    public function actionUpload()
    {
        $fileloc = 'goodsfile\infile.txt';
        $fileftp = 'outsite\ttttt.txt';
        $ftp = new FtpWork();
        if ($ftp->upload($fileloc, $fileftp)) {
            Yii::$app->session->setFlash('success', 'файл загружен на сервер');
        } else {
            Yii::$app->session->setFlash('error', 'файл не загружен');
        }
        return $this->redirect(['index']);
    }

	/**
	 * Генерируем код для результата фильтрации таблицы продуктов
	 * @param $text - искомая строка в базе
	 * @param $tp - тип цены контрегента
	 * @return string - возвращаем строку с html кодом
	 */
    public function actionSearchGood($text, $tp)
    {
        $goods = Goods::find()->select(['id' => 'good_id', 'name' => 'good_name', 'price' => 'good_price', 'desc' => 'good_description'])
		    ->where(['ilike', 'good_name', "$text%", false])->andWhere(['typeprices_id' => $tp])
            ->orderBy('good_name')
		    ->asArray()
		    ->all();

    	$thtml = '';
    	if ($goods)
	        foreach ($goods as $good){
	            $thtml .= '<tr>'
		                    .'<td>'. Html::a($good['name'], '#', ['class' => 'chain']) .'</td>'
							.'<td>'. $good['desc'] .'</td>'
		                    .'<td>'. $good['price'] / 100 .'</td>'
		                    .'<td>'. Html::input('number', 'count' . $good['id'], 0, ['class' => 'form-control count input-sm', 'id' => $good['id']]) .'</td>'
		                .'</tr>' . "\r\n";
		    }
        return $thtml;
    }

    /**
     * @param integer $id
     * @return Goods the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Goods::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не найдена');
        }
    }
}
