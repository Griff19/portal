<?php

namespace backend\controllers;

use Yii;
use backend\models\User;
use backend\models\UserSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [                  
                    [
                        'actions' => ['view','update', 'changepass'],
                        'allow' => true,
                        'roles' => ['@'],                        
                    ],
                    [
                        'actions' => ['index','create','delete','update'],
                        'allow' => true,
                        'roles' => ['operator'],
                    ],
                    [
                        'actions' => ['approle'],
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
     * @param \yii\base\Action $action
     * @return bool
     * @throws ForbiddenHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action) {
//        $param = Yii::$app->request->queryParams;
//        if (ArrayHelper::getValue($param, 'id') == 13)
//            throw new ForbiddenHttpException('Пока нельзя редактировать этого пользователя');
        return parent::beforeAction($action);
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {        
        if ((Yii::$app->user->id == $id) or (Yii::$app->user->can('operator'))){
            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
        } else {
            return $this->redirect(['view', 'id' => Yii::$app->user->id]);
        }        
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post())) {
            $model->auth_key = Yii::$app->security->generateRandomString();
            $model->password_hash = Yii::$app->security->generatePasswordHash($model->newpass);
            $model->password_reset_token = NULL;
            $model->save();
            
            $this->actionApprole($model->id, 'user');
            
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'admin' => true,
            ]);
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        if($id == 13){
            throw new ForbiddenHttpException('Пока нельзя редактировать этого пользователя');
        }
        $model = $this->findModel($id);
        //$oldpass = $model->password_hash;
        
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Изменения внесены успешно');
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка сохранения');
            }
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            if (Yii::$app->user->can('operator')){
                $admin = TRUE; //если редактировать собирается оператор или админ
            } else {
                $admin = FALSE; //если редактирует пользователь
            }
            return $this->render('update', [
                'model' => $model,
                'update' => TRUE,
                'admin' => $admin,
            ]);
        }
    }

    public function actionChangepass($id){
        if($id == 13){
            throw new ForbiddenHttpException('Пока нельзя редактировать этого пользователя');
        }
        $model = $this->findModel($id);
        $oldpass = $model->password_hash;
              
        if($model->load(Yii::$app->request->post())){
            if (Yii::$app->user->can('admin')) {$model->password = 'xxxxxx';}
            if(Yii::$app->security->validatePassword($model->password, $oldpass) or (Yii::$app->user->can('admin'))) {
                if (empty($model->_1c_id)){
                    $model->_1c_id = 'id_is_empty';
                }
                $model->password_hash = Yii::$app->security->generatePasswordHash($model->newpass);
                if ($model->save()){
                    Yii::$app->session->setFlash('success', 'Пароль изменен!');
                    return $this->render('view', [
                        'model' => $model,
                    ]);
                }else{
                    Yii::$app->session->setFlash('fail','Пароль не сохранен :(');
                    return $this->render('update', [
                        'model' => $model,
                        'changepass' => true,
                    ]);
                }                            
            } else {
                Yii::$app->session->setFlash('fail','Старый пароль введен не верно');
                return $this->render('update', [
                    'model' => $model,
                    'changepass' => true,
                ]);                
            }
            
        } else {
            return $this->render('update', [
                'model' => $model,
                'changepass' => true,
            ]);
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        if($id == 13){
            throw new \yii\web\ForbiddenHttpException('Пока нельзя редактировать этого пользователя');
        }
        //конечно же сначала удаляем роль пользователя
        Yii::$app->authManager->revokeAll($id);
        //а затем и самого его :)
        $this->findModel($id)->delete();
                
        return $this->redirect(['index']);
    }
    
    /**
     * Применить к пользователю выбранную роль
     */
    public function actionApprole($id, $rolename){
        if($id == 13){
            throw new ForbiddenHttpException('Пока нельзя редактировать этого пользователя');
        }
        //echo $id .' '.$role;
        Yii::$app->authManager->revokeAll($id);
        $role = Yii::$app->authManager->getRole($rolename);
        Yii::$app->authManager->assign($role, $id);
        
        return $this->redirect(['view', 'id' => $id]);
    }           

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Запрашиваемой страницы не существует.');
        }
    }
}
