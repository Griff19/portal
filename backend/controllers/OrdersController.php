<?php

namespace backend\controllers;

use Yii;
use backend\models\Orders;
use backend\models\OrdersSearch;
use backend\models\ListofgoodsSearch;
use backend\models\Listofgoods;
use backend\models\FtpWork;
use backend\helpers\Logs;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;


/**
 * OrdersController implements the CRUD actions for Orders model.
 */
class OrdersController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'copy', 'update', 'delete', 'place', 'unplace'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['agree', 'disagree'],
                        'allow' => true,
                        'roles' => ['operator'],
                    ],
                    [
                        'actions' => ['download', 'dbtofile'],
                        'allow' => true,
                        'roles' => ['operatorSQL'],
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

    /**
     * Lists all Orders models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrdersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if (Yii::$app->user->can('operator')) {
            return $this->render('index_adm', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'getFile' => FALSE,
            ]);
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Displays a single Orders model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $searchModel = new ListofgoodsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $id);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Orders model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($amount = 0)
    {
        $model = new Orders();
        //$date = 0;

        if ($model->load(Yii::$app->request->post())) {

            $model->user_id = Yii::$app->user->id;

            if ($amount > 0) {
                $model->order_amount = $amount * 100;
            } else {
                $model->order_amount = $model->order_amount * 100; //В базе числа хранятся в целом типе   
            }
            $model->save();
            Logs::add('Создан заказ: ' . $model->order_id . ' на сумму: ' . $model->order_amount/100);
            if ($amount > 0) {
                return $this->redirect(['listofgoods/insertall', 'order_id' => $model->order_id]);
            } else {
                return $this->redirect(['view', 'id' => $model->order_id]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
                'amount' => $amount,
            ]);
        }
    }

    /**
     * копируем заказ, если пользователю захочется повторить старый заказ
     * нажатием кнопки "Повторить" на странице orders/view
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionCopy($id)
    {
        $model = $this->findModel($id);
        $newModel = new Orders();
        //копируем параметры заказа
        $newModel->customers_customer_id = $model->customers_customer_id;
        $newModel->user_id = $model->user_id;
        $newModel->order_amount = $model->order_amount;

        $order_date = $model->order_date;
        if ( (date('Y-m-d', strtotime($order_date)) <= date('Y-m-d')) ){
            $order_date = date('Y-m-d', strtotime("now +1 day"));
        }
        $newModel->order_date = $order_date;

        $newModel->save();
        //тут копируем список заказаных товаров
        //которые пользователь сможет править т.к. копия создается со статусом "Черновик"
        $list = Listofgoods::find()->where(['orders_order_id' => $model->order_id])->all();
        foreach ($list as $item) {
            $newList = new Listofgoods();
            $newList->orders_order_id = $newModel->order_id;
            $newList->goods_good_1c_id = $item->goods_good_1c_id;
            $newList->good_count = $item->good_count;
            $newList->save();
        }
        Logs::add('Повтор заказа: ' . $model->order_id . ' на сумму: ' . $model->order_amount/100 . ' Новый заказ:' . $newModel->order_id);
        return $this->redirect(['view', 'id' => $newModel->order_id]);
    }

    /**
     * Размещение заказа на исполнение.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionPlace($id)
    {
        $model = $this->findModel($id);

        $model->status = 'Размещен';
        $model->save();
        Logs::add('Размещен заказ: ' . $model->order_id . ' на сумму: ' . $model->order_amount/100);
        return $this->redirect(['index']);
    }

    /**
     * Отмена размещенного заказа.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionUnplace($id)
    {
        $model = $this->findModel($id);

        $model->status = 'Черновик';
        $model->save();
        Logs::add('Снят с размещения заказ: ' . $model->order_id . ' на сумму: ' . $model->order_amount/100);
        return $this->redirect(['index']);
    }

    /**
     * поместить заказ в обработанные
     *
     * @param integer $id
     * @return mixed
     */
    public function actionAgree($id)
    {
        $model = $this->findModel($id);

        $model->status = 'Обработан';
        $model->save();
        Logs::add('Оператор обработал заказ: ' . $model->order_id . ' на сумму: ' . $model->order_amount/100);
        return $this->redirect(['index']);
    }

    /**
     * убрать из обработанных, только для админов
     *
     * @param integer $id
     * @return mixed
     */
    public function actionDisagree($id)
    {
        $model = $this->findModel($id);

        $model->status = 'Размещен';
        $model->save();
        Logs::add('Оператор отменил обработку заказа: ' . $model->order_id . ' на сумму: ' . $model->order_amount/100);
        return $this->redirect(['index']);
    }

    /**
     * Updates an existing Orders model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            //$model->order_amount = $model->order_amount * 100;//В базе числа хранятся в целом типе
            $model->save();
            Logs::add('Обновлен заказ: ' . $model->order_id . ' на сумму: ' . $model->order_amount/100);
            return $this->redirect(['view', 'id' => $model->order_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Orders model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->status == 'Черновик') {
            $list = new Listofgoods;
            $list->deleteAll(['orders_order_id' => $id]);
            $model->delete();
            Logs::add('Удален заказ: ' . $model->order_id . ' на сумму: ' . $model->order_amount/100);
        } else {
            throw new ForbiddenHttpException('Удалить можно только черновик');
        }
        return $this->redirect(['index']);
    }

    //отдаем файл пользователю
    public function actionDownload()
    {
        $file = 'OrdersFile/test.txt';
        if (file_exists($file)) {

            header("Content-Type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Content-Length: " . filesize($file));
            header("Content-Disposition: attachment; filename=" . $file);
            readfile($file);
        } else {
            new NotFoundHttpException('Файл не найден');
        }
        exit();
    }

    //формируем файл заказов для выгрузки из базы
    public function actionDbtofile()
    {
        $filename = 'OrdersFile/orderstmp'. date("Ymd") .'.txt';
        //модель и провайдер для передачи во view
        $orderSearch = new OrdersSearch();
        $dpOrder = $orderSearch->search(Yii::$app->request->queryParams);
        //модель для работы с текущими заказами
        $orders = Orders::find()->where(['status' => 'Размещен'])->all();

        $w = fopen($filename, 'a');//открываем файл для записи
        foreach ($orders as $dt) {
            //echo $dt['order_id'];

            $sm = new ListofgoodsSearch();
            $dp = $sm->search(Yii::$app->request->queryParams, $dt['order_id']);

            foreach ($dp->models as $mod) {
                $strtofile = '';
                $strtofile .= $dt['order_id'] . ';';
                $strtofile .= $dt['order_date'] . ';';
                $strtofile .= $dt['customersCustomer']['customer_1c_id'] . ';';
                $strtofile .= $dt['customersCustomer']['customer_name'] . ';';

                $strtofile .= $mod['goodsGoodId']['good_1c_id'] . ';';
                $strtofile .= $mod['goodsGoodId']['good_detail_guid'] . ';';
                $strtofile .= $mod['goodsGoodId']['good_name'] . ';';
                $good_price = $mod['goodsGoodId']['good_price'] / 100;
                $strtofile .= $good_price . ';';
                $strtofile .= $mod['good_count'] . ';';
                $strtofile .= "\r\n";
                fputs($w, $strtofile);//пишем строку в файл
            }
            $dt->status = 'Обработан';
            $dt->save();
        };
        fclose($w);  //закрываем файл
        $ftp = new FtpWork();
        $ftp->upload($filename, 'outsite/orders/orders'. date("Ymd") .'.txt');
        //unlink($filename);
        //$this->fileDownload();
        return $this->render('index_adm', [
            'searchModel' => $orderSearch,
            'dataProvider' => $dpOrder,
            'getFile' => TRUE,
        ]);
    }

    /**
     * Finds the Orders model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Orders the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Orders::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
