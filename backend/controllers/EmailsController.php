<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 28.05.2016
 * Time: 22:22
 */

namespace backend\controllers;

use Yii;
use backend\models\Emails;
use backend\models\EmailsSearch;
use yii\db\ActiveRecord;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * Class EmailsController
  * @package backend\controllers
 */
class EmailsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [

                    [
                        'actions' => ['index', 'view', 'delete', 'create', 'update'],
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
     * @return string
     */
    public function actionIndex(){
        $emailSearch = new EmailsSearch();
        $emailProvider = $emailSearch->search(Yii::$app->request->queryParams);

        return $this->render('index',[
            'dataProvider' => $emailProvider
        ]);
     }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id){
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionCreate(){
        $model = new Emails();
        if ($model->load(Yii::$app->request->post())){
            $model->attach = UploadedFile::getInstance($model, 'attach');

            if ($model->attach){
                $time = time();
                $model->attach->saveAs('attachs/'. $time .'.'. $model->attach->extension);
                $model->attach = 'attachs/'. $time .'.'. $model->attach->extension;
            }
            if ($model->attach){
                Yii::$app->mailer->compose()
                    ->setFrom(['portal@altburenka.ru' => 'Портал-Алтайская Буренка'])
                    ->setTo($model->receiver_email)
                    ->setSubject($model->subject)
                    ->setHtmlBody($model->content_email)
                    ->attach($model->attach)
                    ->send();
            } else {
                Yii::$app->mailer->compose()
                    ->setFrom(['portal@altburenka.ru' => 'Портал-Алтайская Буренка'])
                    ->setTo($model->receiver_email)
                    ->setSubject($model->subject)
                    ->setHtmlBody($model->content_email)
                    ->send();
            }

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model
        ]);
    }

    public function actionDelete($id){
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Emails::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}