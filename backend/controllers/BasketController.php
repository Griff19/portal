<?php

namespace backend\controllers;

use Yii;
use backend\models\Basket;
use backend\models\Goods;
use backend\models\BasketSearch;
//use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * BasketController implements the CRUD actions for Basket model.
 */
class BasketController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index','insert','inserttobasket','delete','deleteall','deleteone','doinsert','addone'],
                        'allow' => true,
                        'roles' => ['@'],                        
                    ],                    
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
    /*
     * Отключаем проверку переданных данных
     */
    public function beforeAction($action) {
        $this->enableCsrfValidation = (($action->id !== 'insert') and ($action->id !== 'inserttobasket'));
        return parent::beforeAction($action);
    }

    /**
     * Lists all Basket models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BasketSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        //$dataProvider->setPagination(50);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Basket model.
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
     *Создаем заказ разом из окна каталоги по кнопке "Заказать!"
     * @return \yii\web\Response
     * @internal param int $good_id
     * @internal param int $count
     * @internal param float $price
     */
    public function actionInsert()
    {        
//        $post = Yii::$app->request->post();
//        $a = 0;
//        foreach ($post as $key => $item){
//            if (strcasecmp($key, 'GoodsSearch')==0){continue;}//пропускаем параметры поиска
//            //собираем данные
//
//            if (FALSE !== (strpos($key, 'count'))){$count = $item; $a++;}
//            if (FALSE !== (strpos($key, 'good'))){$good = $item; $a++;}
//            if (FALSE !== (strpos($key, 'price'))){$price = $item; $a++;}
//
//            if ($a == 3)
//            {
//                if ($count == 0){
//                    $count = 0; $good = 0; $price = 0; $a = 0;
//                    continue;
//                }
//                //echo '->' . $count .' '. $good .' '. $price . '<br>';
//                $model = new Basket();
//                $model->user_id = Yii::$app->user->id;
//                $model->good_id = $good;
//                //$post = Yii::$app->request->post();
//                $model->count = $count;
//                $model->summ = ($count * $price);//тут цена приходит целым числом - корректировать не надо
//                $model->save();
//
//                $count = 0; $good = 0; $price = 0;
//                $a = 0;
//            }
//        }
        //$page = ceil($str / 20);
        return $this->redirect(['orders/create', 'amount' => Basket::getTotals('summ')/100]);
    }

    /**
     * Процедура работает при помощи jQuery, вызывется из views/goods/index.php
     * изменяет заказ добавляя или удаляя позиции из "корзины"
     * @param $hash_id
     * @param $count
     * @return string
     * @throws \Exception
     * @internal param $id_good
     */
    public function actionDoinsert($hash_id, $count)
    {
        if ($count == 0) return false;
        $good = Goods::find()->where(['hash_id' => $hash_id])->one();

        if (isset($good)) {
            $price = $good->good_price;

            $basket = Basket::find()->where(['user_id' => Yii::$app->user->id, 'good_id' => $good->hash_id])->one();
            if (isset($basket)){
                if ($count == 0){
                    $basket->delete();
                } else {
                    $basket->count = $count;
                    $basket->summ = $count * $price;
                    $basket->save();
                }
            } else {
                $model = new Basket();
                $model->user_id = Yii::$app->user->id;
                $model->good_id = $good->hash_id;
                $model->count = $count;
                $model->summ = $count * $price;
                $model->save();
            }
        }
        return 'Заказ '. Basket::getTotals('summ')/100 .'р.';
    }

    /**
     * Добавляем товар в предварительный заказ
     * @param $good_id
     * @return \yii\web\Response
     */
    public function actionInserttobasket($good_id)
    {
        $post = Yii::$app->request->post();
        //$a = 0;
        $count = $post['count_'.$good_id];
        if ($count == 0){
            $count = 1;
        }
        $price = $post['price_'.$good_id];
        
        $model = new Basket();
        $model->user_id = Yii::$app->user->id;
        $model->good_id = $good_id;
        $model->count = $count;
        $model->summ = $count * $price;
        $model->save();
        
        return $this->redirect(['goods/index']);
    }

    /**
     * Creates a new Basket model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Basket();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Basket model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Basket model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    /*
     * Полностью очищаем предварительный заказ (корзину)
     */
    public function actionDeleteall()
    {
        Basket::deleteAll(['user_id' => Yii::$app->user->id]);
        return $this->redirect(['goods/index']);
    }

    /**
     * Удаляем одну штуку в строке "предварительного заказа"
     * @param $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDeleteone($id)
    {
        $model = $this->findModel($id);
        $count = $model->count;
        $summ = $model->summ;
        $price = $summ / $count;
        $count--;
        if ($count == 0) {
            $model->delete();
            //return $this->redirect(['index']);
        } else {
            $model->count = $count;
            $model->summ = $count * $price;
            $model->save();
            //return $this->redirect(['index']);
        }
        return $this->redirect(['index']);        
    }

    /**
     * Добавляем одну штуку товара в строке "предварительного заказа"
     * @param $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionAddone($id)
    {
        $model = $this->findModel($id);
        $count = $model->count;
        $summ = $model->summ;
        $price = $summ / $count;
        $count++;
        if ($count == 0) {
            $model->delete();
            //return $this->redirect(['index']);
        } else {
            $model->count = $count;
            $model->summ = $count * $price;
            $model->save();
            //return $this->redirect(['index']);
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the Basket model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Basket the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Basket::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница недоступна.');
        }
    }
}
