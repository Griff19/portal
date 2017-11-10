<?php

namespace backend\modules\operator\controllers;

use Yii;
use backend\models\Customers;
use backend\models\CustomersSearch;
use backend\models\GoodsSearch;
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
//					[
//						'actions' => ['index', 'view', 'catalog'],
//						'allow' => true,
//						'roles' => ['@'],
//					],
					[
						'actions' => ['index', 'order', 'call'],
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
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action) {
        $this->enableCsrfValidation = ($action->id !== 'call');
        return parent::beforeAction($action);
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
	 * @param integer $tp идентификатор типа цен
	 * @param null $text искомая строка
	 * @return string
	 * @throws NotFoundHttpException
	 */
    public function actionOrder($customer_id, $tp, $text = null)
    {
    	$customer = Customers::findOne($customer_id);
    	if (!$customer)
    		throw new NotFoundHttpException("Не существует контрагента с идентификатором $customer_id");

    	$goodSearch = new GoodsSearch();
    	$goodData = $goodSearch->search(Yii::$app->request->queryParams, $tp);

		return $this->render('order', [
	            'customer' => $customer,
			    'goodData' => $goodData,
			    'goodSearch' => $goodSearch
		    ]);
    }
}
