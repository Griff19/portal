<?php

namespace backend\controllers;

use Yii;
use backend\models\Scans;
use backend\models\ScansSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;



/**
 * ScansController implements the CRUD actions for Scans model.
 */
class ScansController extends Controller
{
        
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index','view','download'],
                        'allow' => true,
                        'roles' => ['@'],                        
                    ],
                    [
                        'actions' => ['create','update','delete'],
                        'allow' => true,
                        'roles' => ['operator'],
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
     * Lists all Scans models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ScansSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Scans model.
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
     * Creates a new Scans model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionCreate()
    {
        if(!(Yii::$app->user->can('operator'))){
            throw new ForbiddenHttpException('Только операторы имеют право сохранять документы');
        }
        $model = new Scans();

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if($model->file){
                $fileName = Yii::$app->user->id . '_' . time();
                $model->file->saveAs('scans_up/'.$fileName.'.'.$model->file->extension);
                $model->path = $fileName.'.'.$model->file->extension;
                $new_path = 'scans_up/mini/'.$fileName.'.'.$model->file->extension;
                $this->imageresize($new_path,'scans_up/'.$model->path, 30, 70); 
                
            }
            $model->user_id = Yii::$app->user->id;
            $model->save();
            return $this->redirect(['view', 'id' => $model->scan_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    
    public function actionDownload($id) {
        
        $model = $this->findModel($id);
        //$fileId = $model->findOne()->where(['scan_id' => $id]);
        //$file = Yii::getPathOfAlias('scans_up') . $model->path;
        $file = 'scans_up/'.$model->path;
        if (file_exists($file)) {
        
            header("Content-Type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Content-Length: " . filesize($file));
            header("Content-Disposition: attachment; filename=" . $file);
            readfile($file);
            
        } else {
            echo "Файл не найден " . $file;
        }
        exit;
    }

    /**
     * Обновляем модель, загружаем картинку и меняем её размер
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if($model->file){
                $oldfile = 'scans_up/'.$model->path;
                $oldmini = 'scans_up/mini/'.$model->path;
                $fileName = Yii::$app->user->id . '_' . time();
                $model->file->saveAs('scans_up/'.$fileName.'.'.$model->file->extension);
                $model->path = $fileName.'.'.$model->file->extension;
                unlink($oldfile);//удаляем старый файл оригинал
                unlink($oldmini);//удаляем старый файл уменьшеную копию
                $new_path = 'scans_up/mini/'.$fileName.'.'.$model->file->extension;
                $this->imageresize($new_path,'scans_up/'.$model->path, 30, 70);
            }
            $model->save();
                       
            return $this->redirect(['view', 'id' => $model->scan_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Scans model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        $oldfile = 'scans_up/'.$model->path;
        $oldmini = 'scans_up/mini/'.$model->path;
        unlink($oldfile);
        unlink($oldmini);
        
        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Scans model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Scans the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Scans::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница недоступна.');
        }
    }
}
