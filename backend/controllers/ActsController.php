<?php
//контроллер обрабатывает команды касающиеся работы с актами сверки
//в данны момент отключен

namespace backend\controllers;

use Yii;
use backend\models\Acts;
use backend\models\ActsSearch;
use common\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use ZipArchive;

/**
 * ActsController implements the CRUD actions for Acts model.
 */
class ActsController extends Controller
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
     * Lists all Acts models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ActsSearch();
        $usr = User::findOne(Yii::$app->user->id);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$usr->_1c_id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Acts model.
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
     * Creates a new Acts model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Acts();

        if ($model->load(Yii::$app->request->post())) {
            
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    /**
     * распаковываем загруженный архив
     */
    public function unpackzip($fname)
    {
        $zip = new ZipArchive();
        $res = $zip->open('Acts/ActsTemp/' . $fname . '.zip');
        if ($res === TRUE) {
            $zip->extractTo('Acts/ActsStore/');
            $zip->close();
            unlink('Acts/ActsTemp/' . $fname . '.zip');
            return true;
        } else {
            return false;
        }
        
    }
    /**
     * читаем файл реестра и заполняем таблицу Acts
     */
    public function readreestr()
    {
        $filename = 'Acts/ActsStore/reestr.txt';
        $readfile = fopen($filename, 'r');
        $unfind = '';//массив для ненайденых документов
        while ($str = fgets($readfile)){
            $items = explode(';', $str);
            
            $numdoc = substr($items[0],-18);
            $extension = pathinfo($numdoc, PATHINFO_EXTENSION);
            if(!file_exists('Acts/ActsStore/' . $numdoc)){
                $unfind[] = $numdoc;//учет ненайденых документов
                unset($items);
                continue;
            }
            $begenddate = date('dmY', strtotime($items[5])) . '-' . date('dmY', strtotime($items[6]));
            $act = Acts::find()->andFilterWhere(['begenddate' => $begenddate])
                    ->andFilterWhere(['customers_customer_1c_id' => $items[3]])
                    ->andFilterWhere(['typedoc' => $items[7]])
                    ->one();
            
            if(isset($act)){
                $modelact = $this->findModel($act->id);
                $modelact->num = $numdoc;
                $link = Yii::$app->security->generateRandomString(16); //определяем имя файла
                copy('Acts/ActsStore/' . $numdoc, 'Acts/ActsDoc/' . $link . '.' . $extension);
                unlink('Acts/ActsStore/' . $numdoc);
                $modelact->link = $link . '.' . $extension;
                $modelact->save();
            } else {
                $modelact = new Acts();
                $modelact->num = $numdoc;
                $link = Yii::$app->security->generateRandomString(16); //определяем имя файла
                copy('Acts/ActsStore/' . $numdoc, 'Acts/ActsDoc/' . $link . '.' . $extension);
                unlink('Acts/ActsStore/' . $numdoc);
                $modelact->link = $link . '.' . $extension;
                $modelact->begdate = date('Y-m-d',strtotime($items[5]));
                $modelact->enddate = date('Y-m-d',strtotime($items[6]));
                $modelact->users_user_1c_id = $items[1];
                $modelact->customers_customer_1c_id = $items[3];
                $modelact->begenddate = $begenddate;
                $modelact->typedoc = $items[7];
                $modelact->save();
            } 
            unset($items);
        }
        fclose($readfile); 
        unlink($filename);
        if(isset($unfind)){
            return $unfind;           
        } else {
            return 0;
        }
    }

    /**
     * Загрузка файла реестра
     */
    public function actionUploadreestr()
    {
        $model = new Acts();
        if ($model->load(Yii::$app->request->post())){
            //$model->filereestr = UploadedFile::getInstance($model, 'filereestr');
            $model->filezip = UploadedFile::getInstance($model, 'filezip');
            if($model->filezip){
                //$filename = 'Reestr';
                $filenamezip = 'Ziptemp' . time();
                //$model->filereestr->saveAs('Acts/ActsTemp/' . $filename . '.' . $model->filereestr->extension);
                $model->filezip->saveAs('Acts/ActsTemp/' . $filenamezip . '.zip');
                $this->unpackzip($filenamezip);
                $unfind = $this->readreestr();
                if($unfind != 0){
                    $unfind = implode("<br>", $unfind);
                    Yii::$app->session->setFlash('success',$unfind);               
                }
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('fail','Ошибка загрузки файлов');
                return $this->render('create',['model' => $model]);                
            }
        } else {
            return $this->render('create',['model' => $model]);
        }
    }

    /**
     * @return \yii\web\Response
     */
    public function actionDwnftp(){
        $fileloc = 'ActdocFile/reestr.txt';
        $fileftp = 'insite/reestr.txt';
        $ftp = new FtpWork();
        if ($ftp->download($fileftp, $fileloc)){
            Yii::$app->session->setFlash('success', 'файл скачан');
        } else {
            Yii::$app->session->setFlash('error', 'файл скачан');
        }
        $this->readreestr();
        //die;
        return $this->redirect(['index']);
    }

    /**
     * Updates an existing Acts model.
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
     * Deletes an existing Acts model.
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
     * Finds the Acts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Acts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Acts::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
