<?php

namespace backend\controllers;

use Yii;
use backend\models\Images;
use backend\models\ImagesSearch;
use backend\helpers\Logs;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;

/**
 * ImagesController implements the CRUD actions for Images model.
 */
class ImagesController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['select', 'create'],
                        'allow' => true,
                        'roles' => ['operator']
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['admin'],
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
     * Получаем список молелей. Если вызван из комментария к акту сверки то выбираем только принадлежащщие ему.
     * @param string $owner
     * @return string
     */
    public function actionIndex($owner = '', $id_act = 0, $num = '')
    {
        if (!Yii::$app->user->can('operator') && $id_act == 0){
            return $this->goBack();
        }
        $searchModel = new ImagesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $owner);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'id_act' => $id_act,
            'num' => $num,
        ]);
    }

    /**
     * Выбор рисунка для номерклатуры (потом можно расширить для любого объекта)
     * @return string
     */
    public function actionSelect($id_good){
        $searchModel = new ImagesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('select', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'id_good' => $id_good

        ]);
    }

    /**
     * Displays a single Images model.
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
     * Создаем новое изображение
     * @return mixed
     */
    public function actionCreate($owner = null)
    {
        $model = new Images();

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->file){
                $oldname = $model->file->baseName;
                $ext = $model->file->extension;
                $filename = 'imgs/' . md5($oldname);
                $model->file->saveAs($filename. '.' . $ext);
            }
            $model->img_newname = $filename . '.' . $ext;
            $model->img_oldname = $oldname . '.' . $ext;
            if ($owner)
                $model->img_owner = $owner;
            $model->save();
            if ($owner)
                return $this->redirect(Url::previous());
            else
                return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
        Logs::add('Сохранено изображение: ' . $model->img_oldname . ' - ' . $model->img_newname);
    }

    /**
     * Updates an existing Images model.
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
     * Удаляем файл картинки и запись о нем из БД
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id, $owner = '', $id_act = 0, $num = '')
    {
        $model = $this->findModel($id);
        $name = $model->img_newname;
        if (file_exists($name)){
            unlink($name);
        }

        $model->delete();
        Logs::add('Удалено изображение: ' . $model->img_oldname . ' - ' . $model->img_newname);
        if ($id_act !== 0) {
            return $this->redirect(['index', 'owner' => $owner, 'id_act' => $id_act, 'num' => $num]);
        } else {
            return $this->redirect(['index']);
        }
    }

    /**
     * Finds the Images model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Images the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Images::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
