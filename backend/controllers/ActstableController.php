<?php

namespace backend\controllers;

use Yii;
use backend\models\Actstable;
use backend\models\ActstableSearch;
use backend\models\Scans;
use backend\models\Images;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * ActstableController implements the CRUD actions for ActsTable model.
 */
class ActstableController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index','update'],
                        'allow' => true,
                        'roles' => ['@'],                        
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['operator'],
                    ],
                    [
                        'actions' => ['create'],
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
     * Lists all ActsTable models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ActstableSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ActsTable model.
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
     * Creates a new ActsTable model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ActsTable();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * На данный момент функция обновляет только поле комментария и картинки
     * через ajax
     * @param string $bm
     * @param integer $page
     * @param integer $acts_id
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id, $acts_id = 0, $page = 1, $bm='')
    {
        //$model = $this->findModel($id);
        $model = $this->findModelByNum($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->file && $model->validate()){
                if (Images::createImgs($model->file, ActsTable::tableName() . $id)){
                    Yii::$app->session->setFlash('success', 'Изображение сохранено');
                } else {
                    Yii::$app->session->setFlash('error', 'Изображение не загружено');
                }
            }
            if($model->save()){
                return $this->redirect(['actsdoc/view', 'id' => $acts_id, 'page' => $page, '#'=>$bm]);
            }
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
                'acts_id' => $acts_id,
            ]);
        }
    }

    /**
     * Deletes an existing ActsTable model.
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
     * Finds the ActsTable model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ActsTable the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ActsTable::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не доступна.');
        }
    }

    /**
     * @param $num
     * @return null|static
     * @throws NotFoundHttpException
     */
    protected function findModelByNum($num){
        if (($model = ActsTable::findOne(['num_doc' => $num])) !== null){
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не доступна.');
        }
    }
}
