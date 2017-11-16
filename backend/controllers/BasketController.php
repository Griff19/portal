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
                        'actions' => ['index','insert','inserttobasket',
	                        'delete','deleteall','deleteone',
	                        'doinsert','addone', 'insert'],
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
	 * После ajax-запроса получаем данные из таблицы и представляем их в виде html
	 * @param $customer_id
	 * @return string
	 */
    public static function htmlBasketRows($customer_id)
    {
	    $basketData = Basket::find()->where(['user_id' => $customer_id])->orderBy('id')->all();
	    $html = '';
	    foreach ($basketData as $basket) {
		    $good_name = $basket->goods->good_name;
		    $html .= "<tr>"
			    ."<td>$basket->id</td>" //идентификатор
			    ."<td>$basket->user_id</td>" //контрегент
			    ."<td>$good_name</td>" //номенклатура
			    ."<td>$basket->count"
		            .'<a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="addBasketOne('. $basket->id .')" style="float:right;">+</a>'
		            .'<a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="delBasketOne('. $basket->id .')" style="float:right; padding: 1px 7px 1px 7px;" >-</a>'
			    ."</td>" //количество
			    .'<td>'. $basket->summ / 100 .'</td>' //сумма
			    ."</tr>";
	    }
		$json[] = $html;
	    $json[] = Basket::getTotals('summ', $customer_id);
	    return json_encode($json);
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
     * Добавление товаров в корзину с рабочей страницы оператора
     * @return string
     * @internal param int $good_id
     * @internal param int $count
     * @internal param float $price
     */
    public function actionInsert($customer_id, $good_hash = null, $good_id = null, $count)
    {        
		if ($good_hash)
    	    $good = Goods::findOne(['hash_id' => $good_hash]);
		else
			$good = Goods::findOne(['good_id' => $good_id]);

		if ($good){
			$price = $good->good_price;

			$basket = Basket::find()->where(['user_id' => $customer_id, 'good_id' => $good->hash_id])->one();
			if ($basket){
				if ($count == 0){
					$basket->delete();
				} else {
					$basket->count = $count;
					$basket->summ  = $count * $price;
					$basket->save();
				}
			} else {
				$model = new Basket();
				$model->user_id = $customer_id;
				$model->good_id = $good->hash_id;
				$model->count = $count;
				$model->summ = $count * $price;
				$model->save();
			}
		}

		return self::htmlBasketRows($customer_id);
    }

    /**
     * Процедура работает при помощи Ajax, вызывется из views/goods/index.php
     * изменяет заказ добавляя или удаляя позиции из "корзины"
     * @param $hash_id
     * @param $count
     * @return string вставляется в шапку страницы
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
    
    /**
     * Полностью очищаем предварительный заказ (корзину)
     */
    public function actionDeleteall()
    {
        Basket::deleteAll(['user_id' => Yii::$app->user->id]);
        return $this->redirect(['goods/index']);
    }

	/**
	 * Удаляем одну штуку в строке "предварительного заказа"
	 *
	 * @param integer $id идентификатор строки корзины
	 * @param null    $mod режим вызова функции ajax или нет
	 *
	 * @return mixed
	 */
    public function actionDeleteone($id, $mod = null)
    {
        /** @var Basket $model */
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

        if ($mod)
        	return self::htmlBasketRows($model->user_id);
        else
            return $this->redirect(['index']);
    }

	/**
	 * Добавляем одну штуку товара в строке "предварительного заказа"
	 *
	 * @param integer $id илентификатор строки корзины
	 * @param null    $mod режим вызова функции ajax или нет
	 *
	 * @return mixed
	 */
    public function actionAddone($id, $mod = null)
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

        if ($mod)
        	return self::htmlBasketRows($model->user_id);
        else
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
