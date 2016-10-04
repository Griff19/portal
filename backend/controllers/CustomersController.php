<?php
//Контроллер обеспечивает работу с контрагентами
namespace backend\controllers;

use Yii;
use backend\models\Customers;
use backend\models\CustomersSearch;
use backend\models\Typeprice;
use backend\models\FtpWork;
use common\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * CustomersController implements the CRUD actions for Customers model.
 */
class CustomersController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],                        
                    ],
                    [
                        'actions' => ['view','create','update','delete','uploadform', 'dwnftp'],
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
     * Lists all Customers models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CustomersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Customers model.
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
     * Creates a new Customers model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Customers();

        if ($model->load(Yii::$app->request->post())) {
            if(!(Yii::$app->user->can('admin'))){
                $model->user_id = Yii::$app->user->id;
            }
            $model->save();
            return $this->redirect(['view', 'id' => $model->customer_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'upload' => false,
            ]);
        }
    }

    /**
     *
     */
    public function read_file() {
        /* @var $customer Customers */
        /* @var $tp Typeprice */
        $filename = 'CustomersFile/Customers.txt';
        $readfile = fopen($filename, 'r');
        while ($str = fgets($readfile, 1024)){
            $items = explode(';', $str); //имя пользователя[0]; код контрагента[1]; имя контрагента[2]; тип цен[3]; ИНН[4]; email[5]
            $usr = User::findByFullname($items[0]);
            $tpname = substr($items[3],0,9);
            if (empty($tpname))
                $tp = Typeprice::findOne(['type_price_name' => '00001    ']);
            else
                $tp = Typeprice::findOne(['type_price_name' => $tpname]);
            if (!$tp) {
                $tp = new Typeprice();
                $tp->type_price_name = $tpname;
                $tp->save();
            }

            $customer = Customers::findOne(['customer_1c_id' => $items[1]]);
            if ($customer) {
                $customer->customer_name = trim($items[2]);
                $customer->customer_email = trim($items[5]);
                $customer->inn = $items[4];
                $customer->typeprices_id = $tp->type_price_id;
                $customer->save();
            } else {
                $customer = new Customers();
                $customer->customer_1c_id = $items[1];
                $customer->customer_name = trim($items[2]);
                $customer->inn = $items[4];
                $customer->customer_email = trim($items[5]);
                $customer->typeprices_id = $tp->type_price_id;
                $customer->save();
            }
        }
        fclose($readfile);
    }

    /**
     * Достаем данные из файла
     */
    public function readfile(){
        $filename = 'CustomersFile/Customers.txt';
        $readfile = fopen($filename, 'r');
        while ($str = fgets($readfile, 1024)){
            //echo 'BEGIN <br>';
            $items = explode(';', $str); //имя пользователя[0]; код контрагента[1]; имя контрагента[2]; тип цен[3]; ИНН[4]; email[5]
            //print_r($items);
            //die();
            $usr = User::findByFullname($items[0]);
            //var_dump($usr);
            if(!isset($usr)) {continue;}

            $tpname = substr($items[3],0,9);
            if (empty(trim($tpname))){continue;}
            $tp = Typeprice::findOne(['type_price_name' => $tpname]);
            if(isset($tp)){
                //print_r($tp);
                //echo $tp->type_price_id . '<br>';
            } else {
                //echo 'new TP <br>';
                $tp = new Typeprice();
                $tp->type_price_name = $tpname;
                $tp->save();
            }
            //continue;
            $customer = Customers::findOne(['customer_1c_id' => $items[1]]);
            if(isset($customer)){
                //echo $customer->customer_id;
            } else {
                //echo 'new CS <br>';
                $customer = new Customers();
                $customer->customer_1c_id = $items[1];
                $customer->user_id = $usr->id;
                $customer->customer_name = $items[2];
                $customer->typeprices_id = $tp->type_price_id;
                $customer->save();               
            }
            unset($customer);
            unset($tp);                  
        }
        fclose($readfile);
    }
    //загружаем файл с данными по контрагентам
    public function actionUploadform(){
        $model = new Customers();
        
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if($model->file){
                $fileName = 'Customers';
                $model->file->saveAs('CustomersFile/'.$fileName.'.'.$model->file->extension);                                
                $this->read_file();
                //die;
            }            
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
                'upload' => true,
                ]);
        }
    }

    /**
     * Получаем файл из FTP
     * @return \yii\web\Response
     */
    public function actionDwnftp()
    {
        $fileloc = 'CustomersFile/Customers.txt';
        $fileftp = 'insite/contract.txt';
        $ftp = new FtpWork();
        if ($ftp->download($fileftp, $fileloc)){
            Yii::$app->session->setFlash('success', 'файл скачан');
        } else {
            Yii::$app->session->setFlash('error', 'файл скачан');
        }
        $this->read_file();
        return $this->redirect(['index']);
    }

    /**
     * Updates an existing Customers model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->customer_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'upload' => false
            ]);
        }
    }

    /**
     * Deletes an existing Customers model.
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
     * Получить ид пользователя по коду контрагента в 1с
     */
    public function getUserid($_1c_id)
    {
        $model = Customers::findOne(['customer_1c_id' => $_1c_id]);
        if ($model !== NULL){
            return $model->user_id;
        } else {
            return NULL;
        }
    }
    /**
     * Finds the Customers model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Customers the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Customers::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
