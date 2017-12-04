<?php

namespace backend\modules\operator\controllers;

use Yii;
use backend\modules\operator\models\OperLog;
use backend\models\BasketSearch;
use backend\models\TypicalOrderSearch;
use backend\models\Customers;
use backend\models\CustomersSearch;
use backend\models\GoodsSearch;
use backend\models\Orders;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Default controller for the `operator` module
 */
class DefaultController extends Controller
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'actions' => ['index', 'order', 'next-customer', 'check-customer', 'modal'],
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
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
	{
        $customerSearch = new CustomersSearch();
        $customersData = $customerSearch->search(Yii::$app->request->queryParams);

		return $this->render('index', [
			'customerSearch' => $customerSearch,
			'customerData' => $customersData
		]);
    }
	
	/**
	 * Генерируем страницу заказа для телефонного операторв
	 * @param integer $customer_id идентификатор контрагента
	 * @return string
	 * @throws NotFoundHttpException
	 */
    public function actionOrder($customer_id)
    {
    	$customer = Customers::findOne($customer_id);
    	if (!$customer)
    		throw new NotFoundHttpException("Не существует контрагента с идентификатором $customer_id");
		
    	$goodSearch = new GoodsSearch();
    	$goodData = $goodSearch->search(Yii::$app->request->queryParams, $customer->typeprices_id);

        $typicalOrderSearch = new TypicalOrderSearch();
        $typicalOrderData = $typicalOrderSearch->search(Yii::$app->request->queryParams);

        $basketSearch = new BasketSearch();
        $basketData = $basketSearch->search(Yii::$app->request->queryParams, true);

		return $this->render('order', [
	            'customer' => $customer,
			    'goodData' => $goodData,
			    'typicalOrderData' => $typicalOrderData,
			    'basketData' => $basketData,
			    'goodSearch' => $goodSearch
		    ]);
    }
	
	/**
	 * Проверяем можно ли переходить к следующему контрегенту, или следует спросить пользователя почему
	 * заказ не сделан или не завершен, запускается через ajax со страницы оператора телефона
	 * @param integer $customer_id идентификатор контрагента
	 * @return boolean
	 */
	public function actionCheckCustomer($customer_id)
	{
		$customer = Customers::findOne($customer_id);
		if ($customer->placeOrder) {
			return true;
		} else {
			$customer->sort = time();
			if ($customer->save()) {}
			else Yii::$app->session->setFlash('error', serialize($customer->errors));
			return false;
		}
	}
	
	/**
	 * Генерация модального окна, вызывается через ajax со страницы оператора телефона
	 * @return string
	 */
	public function actionModal($customer_id)
	{
		$model = new OperLog();
		if ($model->load(Yii::$app->request->post())) {
			$customer = Customers::findOne($customer_id);
			$model->action = OperLog::ACTION_NEXT_CUSTOMER .' ID:'. $customer_id .' '. $customer->customer_name;
			$model->save();
			return $this->redirect('next-customer');
		} else
			return $this->renderAjax('modal_nc', ['model' => $model]);
	}
	
    /**
	 * Переходим к следующему контрагенту по кнопке на странице телефонного оператора
	 * @return \yii\web\Response
	 */
    public function actionNextCustomer()
	{
		/** @var Customers $customer */
		$customer = Customers::find()->where('phone IS NOT NULL')->andWhere(['customers.status' => Customers::STATUS_ACTIVE])
			->andWhere("orders.status <> :status OR orders.status IS NULL", [':status' => Orders::STATUS_PLACE])
			->orderBy('sort')
			->joinWith('phone')->joinWith('orders')->one();
		
		return $this->redirect(['order', 'customer_id' => $customer->customer_id, 'tp' => $customer->typeprices_id]);
	}
}
