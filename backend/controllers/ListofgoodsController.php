<?php

namespace backend\controllers;

use Yii;
use backend\models\Listofgoods;
use backend\models\ListofgoodsSearch;
use backend\models\Orders;
use backend\models\Customers;
use backend\models\Basket;
//use backend\controllers\OrdersController;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * ListofgoodsController implements the CRUD actions for Listofgoods model.
 */
class ListofgoodsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index','view','update'],
                        'allow' => false,
                        //'roles' => ['@'],                        
                    ],
                    [
                        'actions' => ['create','delete','insertall','insert'],
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
    
    public function beforeAction($action) {
        $this->enableCsrfValidation = ($action->id !== 'insert');
        return parent::beforeAction($action);
    }
    /**
     * Lists all Listofgoods models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ListofgoodsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Listofgoods model.
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
     * Creates a new Listofgoods model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($order_id = 0, $amount = 0)
    {
        $model = new Listofgoods();
        $customers = new Customers();
        $typeprice = $customers->getTP(Yii::$app->user->id); //получаем тип цены для данного пользователя
        
        if ($model->load(Yii::$app->request->post())) {
            if($order_id != 0){
                $model->orders_order_id = $order_id;
                $price = $model->goodsGoodId->good_price /100;
                $count = $model->good_count;
                $amount = $amount + ($price * $count);
                //увеличим сумму заказа
                $order = Orders::findOne($order_id);
                $order->order_amount = $amount * 100;
                $order->save();                
            }            
            $model->save();
            return $this->redirect(['orders/view', 'id' => $model->orders_order_id]);            
        } else {
            return $this->render('create', [
                'model' => $model,
                'orders_order_id'=> $order_id,
                'tp' => $typeprice,
            ]);
        }
    }
    //Добавляем товар из каталога прямо в заказ
    public function actionInsert($order_id)
    {
        $order = Orders::findOne($order_id);
        $amount = $order->order_amount / 100;
        $post = Yii::$app->request->post();
        $a = 0;
        foreach ($post as $key => $item){
            if (strcasecmp($key, 'GoodsSearch')==0){continue;}//пропускаем параметры поиска
            //собираем данные
            
            if (FALSE !== (strpos($key, 'count'))){$count = $item; $a++;}
            if (FALSE !== (strpos($key, 'good'))){$good = $item; $a++;}
            if (FALSE !== (strpos($key, 'price'))){$price = $item; $a++;}
            
//var_dump(strpos($key, 'price'));
            if ($a == 3)
            {
                if ($count == 0){
                    $count = 0; $good = 0; $price = 0; $a = 0;
                    continue;
                }
                //echo '->' . $count .' '. $good .' '. $price . '<br>';
                $model = new Listofgoods();
                $model->orders_order_id = $order_id;
                $model->goods_good_1c_id = $good;
                //$post = Yii::$app->request->post();
                $model->good_count = $count;
                //$model-> = ($count * $price);//тут цена приходит целым числом - корректировать не надо
                if ($model->save())
                {
                    $order->order_amount = ($amount + ($count * $price/100)) * 100;
                    $order->save();
                }
                               
                $count = 0; $good = 0; $price = 0;
                $a = 0;
            }
        }
               
        return $this->redirect(['orders/view', 'id' => $order_id]);
    }

    /**
     * Создаем заказ на основе данных в корзине
     * @param integer $order_id идентификатор заказа
     * @param integer $customer_id идентификатор контрагента, если задано то функция вызвана со страницы оператора
     * @return \yii\web\Response|boolean
     */
    public function actionInsertall($order_id, $customer_id = null)
    {
        if ($order_id == 0) $this->redirect(['basket/index']);
        $order = Orders::findOne($order_id);
        $countGoods = Listofgoods::find()->where(['orders_order_id' => $order_id])->count();
        if($countGoods > 0) {
            $amount = $order->order_amount;
        } else {
            $amount = 0;
        }
        if ($customer_id)
            $basket = Basket::findAll(['user_id' => $customer_id]);
        else
            $basket = Basket::findAll(['user_id' => Yii::$app->user->id]);
        //for($i=0; $i < Basket::getCount(); $i++){
        
        foreach ($basket as $modelb){
            //$modelb = $basket[$i];
            //echo $modelb->good_id .' '. $modelb->count .' '. $modelb->summ .' '. $modelb->user_id . '<br>';
            $model = new Listofgoods();
            $model->orders_order_id = (int)$order_id;
            $model->goods_good_1c_id = (string)$modelb->good_id;
            $model->good_count = (int)$modelb->count;
            $model->save();

            $amount += $modelb->summ;
        }
        $order->order_amount = $amount;
        $order->save();

        if ($customer_id) {
            Basket::deleteAll(['user_id' => $customer_id]);
            return true;
        } else {
            Basket::deleteAll(['user_id' => Yii::$app->user->id]);
            return $this->redirect(['orders/view', 'id' => $order_id]);
        }
    }

    /**
     * Updates an existing Listofgoods model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->list_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Listofgoods model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $order_id = $model->orders_order_id;
        
        $order = Orders::findOne($order_id);
        $amount = $order->order_amount / 100;
        
        $price = $model->goodsGoodId->good_price /100;
        $count = $model->good_count;
        
        $amount2 = $amount - ($price * $count);
        $order->order_amount = $amount2 * 100;
        $order->save();
                
        $model->delete();

        return $this->redirect(['orders/view','id' => $order_id]);
    }

    /**
     * Finds the Listofgoods model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Listofgoods the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Listofgoods::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не найдена.');
        }
    }
}
