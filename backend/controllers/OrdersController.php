<?php
/**
 * Контроллер для модели Заказы (Orders)
 */
namespace backend\controllers;

use Yii;
use backend\models\Basket;
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
                    [
                        'actions' => ['create-from-basket'],
                        'allow' => true,
                        'roles' => ['telephone'],
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
     * Генерируем список всех заказов
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
     * Открываем один заказ
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
     * Создаем новый заказ
     * @return mixed
     */
    public function actionCreate($amount = 0)
    {
        $model = new Orders();

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
	 * Создаем через ajax заказ из корзины со страницы оператора
	 * @param integer $customer_id идентификатор контрагента
	 * @return string
	 */
    public function actionCreateFromBasket($customer_id)
    {
        $amount = Basket::getTotals('summ', $customer_id);

    	$model = new Orders();
        $model->customers_customer_id = $customer_id;
        $model->order_amount = $amount * 100;
        $model->user_id = Yii::$app->user->id;
        $model->status = Orders::STATUS_CREATE;
        $model->order_date = date('Y-m-d', strtotime("now +1 day"));
        $model->save();

        Logs::add('Создан заказ: ' . $model->order_id . ' на сумму: ' . $amount);

	    $str = "Заказ №$model->order_id на сумму {$amount}р.";

        $listofgoods = new ListofgoodsController('listofgoods', $this->modules);
		$create = $listofgoods->actionInsertall($model->order_id, $customer_id);
		$processed = false;
		if ($create) {
			$str .= " успешно создан";
			$model->status = Orders::STATUS_PLACE;
			if ($model->save()) {
				Logs::add('Размещен заказ: ' . $model->order_id . ' на сумму: ' . $amount);
				$str .= " и размещён";
				$processed = true; //todo: использовать для определения цвета сообщения
			} else {
				$str .= " но не размещён...";
			}
		} else {
			$str .= " не удалось заполнить и разместить...";
		}

		return $str;
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
     * @param integer $id
     * @return mixed
     */
    public function actionPlace($id)
    {
        $model = $this->findModel($id);

        $model->status = Orders::STATUS_PLACE;
        $model->save();
        Logs::add('Размещен заказ: ' . $model->order_id . ' на сумму: ' . $model->order_amount/100);
        return $this->redirect(['index']);
    }

    /**
     * Отмена размещенного заказа.
     * @param integer $id
     * @return mixed
     */
    public function actionUnplace($id)
    {
    	$model = $this->findModel($id);
		$model->scenario = Orders::SCENARIO_SAFE;
        $model->status = Orders::STATUS_CREATE;
        if ($model->save()) {
	        Logs::add('Снят с размещения заказ: ' . $model->order_id . ' на сумму: ' . $model->order_amount / 100);
        } else {
        	Yii::$app->session->setFlash('error', serialize($model->errors));
        }
	    return $this->redirect(['index']);
    }

    /**
     * Поместить заказ в обработанные
     * @param integer $id
     * @return mixed
     */
    public function actionAgree($id)
    {
        $model = $this->findModel($id);

        $model->status = Orders::STATUS_PROCESSED;
        $model->save();
        Logs::add('Оператор обработал заказ: ' . $model->order_id . ' на сумму: ' . $model->order_amount/100);
        return $this->redirect(['index']);
    }

    /**
     * убрать из обработанных, только для админов
     * @param integer $id
     * @return mixed
     */
    public function actionDisagree($id)
    {
        $model = $this->findModel($id);

        $model->status = Orders::STATUS_PLACE;
        $model->save();
        Logs::add('Оператор отменил обработку заказа: ' . $model->order_id . ' на сумму: ' . $model->order_amount/100);
        return $this->redirect(['index']);
    }

    /**
     * Редактируем дынные заказа. После - возвращаемся в окно просмотра заказа
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
     * Удаление черновика заказа. После удаления возвращаемся к списку заказов.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->status == Orders::STATUS_CREATE) {
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
        $orders = Orders::find()->where(['status' => Orders::STATUS_PLACE])->all();

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
            $dt->status = Orders::STATUS_PROCESSED;
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
