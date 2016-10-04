<?php

namespace backend\controllers;

use Yii;
use backend\models\ActsDoc;
use backend\models\ActsdocSearch;
use backend\models\Actstable;
use backend\models\ActstableSearch;
use backend\models\Customers;
use backend\models\FtpWork;
use backend\models\Images;
use backend\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\filters\AccessControl;

/**
 * Контроллер для модели шапки Актов сверки
 */
class ActsdocController extends Controller {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'control', 'download'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['create', 'update', 'delete', 'uploadform', 'dbtofile','dwnftp'],
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
     * Lists all ActsDoc models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new ActsdocSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ActsDoc model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        $model = $this->findModel($id);
        $searchModel = new ActstableSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $model->num_act);

        return $this->render('view', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new ActsDoc model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new ActsDoc();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id_doc]);
        } else {
            return $this->render('create', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ActsDoc model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id_doc]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Устанавливаем признак отправки документа control
     * @param integer $id
     * @return mixed
     */
    public function actionControl($id) {
        $model = $this->findModel($id);
        if ($model->control == 0) {
            $model->control = 1;
        } else {
            $model->control = 0;
        }
        $model->save();
        return $this->redirect(['view', 'id' => $model->id_doc]);
    }

    /**
     * Deletes an existing ActsDoc model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $table = new ActsTable();
        $table->deleteAll(['acts_id' => $id]);
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    //Новая переработанная функция
    public function readreestr() {
        $filename = 'ActdocFile/reestrTS.txt';
        $readfile = fopen($filename, 'r');

        while ($str = fgets($readfile)) {

            $items = explode(';', $str);
            $type_act = $items[0];
            //if ($type_act == 'V') {
            //Определяем суммы сальдо заранее
            $beg_sald = $items[9];
            $beg_sald = str_replace(',', '.', $beg_sald);
            $beg_sald = preg_replace('/[^x\d|*\.]/', '', $beg_sald);
            $end_sald = $items[10];
            $end_sald = str_replace(',', '.', $end_sald);
            $end_sald = preg_replace('/[^x\d|*\.]/', '', $end_sald);
            $beg_sald = (int)($beg_sald * 100);
            $end_sald = (int)($end_sald * 100);
            $num_doc = $items[6];
            $num_act = $items[1];

            $actfind = ActsDoc::find()->where(['num_act' => $num_act])->one();
            if (isset($actfind)) {

                //Если нашли то устанавливаем сальдо
                $actsdoc = $this->findModel($actfind->id_doc);

                if ($end_sald > 0) {
                    $actsdoc->end_sald = $end_sald;
                }
                if ($actsdoc->save()){//echo 'редактирование документа...<br>';
                }
                else {print_r($actsdoc->errors);}
            } else {

                //Если не находим то создаем новый элемент
                $begdate = $items[2];
                $enddate = $items[3];
                $contr_doc = $items[4]; //код контрагента в 1с

                $cus = new Customers();
                $usid = $cus->getUserid($contr_doc); //получаем id по коду 1с
                if ($usid == NULL) {
                    echo 'пользователь '. $contr_doc . ' не найден <br>';
                    continue; //если данного пользователя нет в системе то пропускаем
                }
                //если пользователь найден то создаем документы
                echo $contr_doc . ' контрагент найден создаем новый документ <br>';
                $actsdoc = new ActsDoc();
                $actsdoc->type_act = $type_act == 'V' ? 0 : 1;
                $actsdoc->num_act = $num_act;
                $actsdoc->begdate = date('Y-m-d', strtotime($begdate));
                $actsdoc->enddate = date('Y-m-d', strtotime($enddate));
                $actsdoc->contr_doc = $contr_doc;
                $actsdoc->user_id = $usid;
                if ($beg_sald > 0) {
                    $actsdoc->beg_sald = $beg_sald;
                }
                if ($end_sald > 0) {
                    $actsdoc->end_sald = $end_sald;
                }
                if($actsdoc->save()){//echo 'сохранение документа!<br>';
                }
                else {print_r($actsdoc->errors);}
            }

            if (!empty($num_doc)) {
                $date_doc = $items[5];
                $name_doc = $items[7];
                $cod_good = $items[8];
                /**@var $actstable ActsTable*/
                $actstable = Actstable::find()->where(['num_doc' => $num_doc])
                    ->andWhere(['date_doc' => date('Y-m-d', strtotime($date_doc))])->one();
                if (isset($actstable)) {
                    $actstable->beg_sald = $beg_sald;
                    $actstable->end_sald = $end_sald;
                    if ($actstable->save()){//echo 'строка отредактирована...<br>';
                    }
                    else {print_r($actstable->errors);}
                } else {
                    //$actfind = ActsDoc::find()->andFilterWhere(['num_act' => $num_act])->one();
                    //if (isset($actfind)) {
                    //echo $actfind->id_doc;
                    //echo 'формируем строку в табличной части ' . $num_doc . '<br>';
                    $actstable = new Actstable();
                    $actstable->acts_id = $actsdoc->id_doc;
                    $actstable->act_num = $num_act;
                    $actstable->date_doc = date('Y-m-d', strtotime($date_doc));//$date_doc;
                    $actstable->num_doc = $num_doc;
                    $actstable->name_doc = $name_doc;
                    $actstable->cod_good = $cod_good;
                    $actstable->beg_sald = $beg_sald;
                    $actstable->end_sald = $end_sald;
                    if($actstable->save()){//echo 'строка сохранена...<br>';
                    }
                    else {print_r($actstable->errors);}
                }
            }
        }

        fclose($readfile);
        return true;
    } //readreestr()

    /**
     * Обрабатываем форму для загоузки файла
     * @return mixed
     */
    public function actionUploadform() {
        $model = new ActsDoc();

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->file) {
                $fileName = 'reestrTS.txt';
                $model->file->saveAs('ActdocFile/' . $fileName);
                $this->readreestr();
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
     * Скачать с ФТП
     * @return \yii\web\Response
     */
    public function actionDwnftp(){

        $fileloc = 'ActdocFile/reestrTS.txt';
        $fileftp = 'insite/reestrTS.txt';
        $ftpcatalog = 'insite/';
        $localcatalog = 'Acts/ActsDoc';

        $ftp = new FtpWork();
        if ($ftp->download($fileftp, $fileloc)){
            Yii::$app->session->setFlash('success', 'файл скачан');
        } else {
            Yii::$app->session->setFlash('error', 'файл не скачан');
        }
        $ftp->downloadAll($ftpcatalog, $localcatalog);
        $this->readreestr();

        return $this->redirect(['index']);

    }

    /**
     * формируем файл с данными от пользователя
     * @return string
     */
    public function actionDbtofile()
    {
        $filename = 'ActdocFile/acts'. date("Ymd") .'.txt';
        //модель и провайдер для передачи во view
        $actSearch = new ActsdocSearch();
        $dpActs = $actSearch->search(Yii::$app->request->queryParams);
        //модель для работы с текущими заказами
        $acts = ActsDoc::find()->where(['control' => 1])->all();

        $w = fopen($filename, 'a');//открываем файл для записи в конец
        foreach ($acts as $dt) {
            //var_dump($dt);
            //echo '<br>';
            $sm = new ActstableSearch();
            $dp = $sm->search(Yii::$app->request->queryParams, $dt['num_act']);

            foreach ($dp->models as $mod) {
                //var_dump($mod);

                $strtofile = '';
                $strtofile .= User::getName($dt['user_id'], 1) . ';';
                $strtofile .= $dt['num_act'] . ';';
                $strtofile .= $dt['begdate'] . ';';
                $strtofile .= $dt['enddate'] . ';';
                $strtofile .= $dt['contr_doc'] . ';';
//                $strtofile .= $dt['customersCustomer']['customer_1c_id'] . ';';
//                $strtofile .= $dt['customersCustomer']['customer_name'] . ';';
//
                $strtofile .= $mod['num_doc'] . ';';
                $strtofile .= $mod['date_doc'] . ';';
                $strtofile .= $mod['actstable_comm'] . ';';

                $imgs = Images::getImages(Actstable::tableName() . $mod['num_doc']);
                if (!empty($imgs)) {
                    foreach ($imgs as $img) {
                        $strtofile .= $img->img_newname . ',';
                        $ftpimg = new FtpWork();
                        $ftpimg->upload($img->img_newname, 'outsite/actsdoc/' . $img->img_newname);
                    }
                } else {
                    $strtofile .= ';';
                }

                $strtofile .= "\r\n";
                fputs($w, $strtofile);//пишем строку в файл
            }
            $dt->control = 2;
            $dt->save();
        };
        fclose($w);  //закрываем файл
        $ftp = new FtpWork();
        $ftp->upload($filename, 'outsite/actsdoc/acts'. date("Ymd") .'.txt');
        return $this->redirect(['index']);

    } //actionDbtofile()

    /**
     * Предоставляем пользователю файл для скачивания
     * @param $type
     * @param $num
     */
    public function actionDownload($type, $num){
        if ($type == 0) {
            $file = 'Acts/ActsDoc/' . 'SV' . $num . '.xlsx';
            $filename = 'SV' . $num . '.xlsx';
        } else {
            $file = 'Acts/ActsDoc/' . 'ST' . $num . '.xlsx';
            $filename = 'ST' . $num . '.xlsx';
        }
        if (file_exists($file)) {
            header("Content-Type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Content-Length: " . filesize($file));
            header("Content-Disposition: attachment; filename=" . $filename);
            readfile($file);
        } else {
            new NotFoundHttpException('Файл не найден');
        }
        exit();
    }

    /**
     * Finds the ActsDoc model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ActsDoc the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = ActsDoc::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не доступна.');
        }
    }

}
