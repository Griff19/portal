<?php
namespace backend\controllers;

use backend\models\Customers;
use backend\models\Emails;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\Controller;
use common\models\LoginForm;
use yii\filters\VerbFilter;
use backend\models\Site;
use backend\helpers\Logs;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                        //Для нормальной работы сайта - следующую строку закоментировать
                        //'roles' => 'admin'
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['denyindex', 'activation', 'validation', 'activation-complete', 'send-mail-again', 'captcha'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
            ],
        ];
    }

    public function actionIndex()
    {
        if (Yii::$app->user->can('telephone') && !Yii::$app->user->can('admin'))
        	return $this->redirect('/operator');
        else
    	    return $this->render('index');
    }

    public function actionDenyindex(){
        return $this->render('deny_index');
    }

    public function actionLogin()
    {
        /* @var $usr \common\models\User */
        $deny = Site::$deny; //проверяем доступность сайта

//        if (!\Yii::$app->user->isGuest) {
//            return $this->goHome();
//        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            if($deny) {
                if (Yii::$app->user->can('admin')) {
                    Logs::add('Вход в систему.');
                    return $this->goBack();
                } else {
                    return $this->render('deny_index', [
                        'model' => $model,
                    ]);
                }
            } else {
                Logs::add('Вход в систему.');
                $usr = User::findOne(Yii::$app->user->id);
                if ($usr->status == User::STATUS_ACTIVATED) {
                    $usr->status = User::STATUS_ACTIVE;
                    $usr->save();
                }
                if (Yii::$app->user->can('telephone') && !Yii::$app->user->can('admin'))
                	return $this->redirect('/operator');
                else
                    return $this->goHome();
            }
        } else {
            return $this->render('login', [
                'model' => $model,
                'deny' => $deny,
            ]);
        }
    }
    

    public function actionLogout()
    {
        Logs::add('Выход из системы.');
        Yii::$app->user->logout();

        return $this->redirect(['site/index']);
    }

    public function actionSendMailAgain($id){
        /* @var $usr \common\models\User */
        $usr = \common\models\User::findOne($id);
        $newpass = $usr->generatePassword();
        $usr->password_hash = Yii::$app->security->generatePasswordHash($newpass);
        if ($usr->save()){
            //отправляем письмо
            $body = 'Здравствуйте!<br>'
                . 'Вы получили это письмо потому, что повторно запросли авторизационные данные аккаунта на портале www.portal.altburenka.ru<br>'
                . 'Если Вы этого не делали то обратитесь к администратору портала: it7@altburenka.ru<br>'
                . '<br>Ваши регистрационные данные:<br>'
                . 'Логин: <b>'. $usr->username . '</b><br>'
                . 'Пароль: <b>' . $newpass . '</b><br>'
                . '<br> Благодарим за использование нашего портала.';
            Emails::sendMail($usr->email, 'Повторная отправка данных аккаунта', $body);

            Yii::$app->session->setFlash('success', 'На указанный адрес выслано письмо с логином и новым паролем.<br>');
        }
        return $this->redirect(['site/login']);
    }

    /**
     * После того как пользователь получил активационное письмо и перешел по ссылке
     * @param null $auth
     * @return \yii\web\Response
     */
    public function actionActivationComplete($auth = null){
        /* @var $usr \common\models\User */
        if ($auth) {
            $usr = User::findOne(['auth_key' => $auth]);
            if ($usr) {
                $usr->status = User::STATUS_ACTIVATED;
                $usr->save();
                Yii::$app->session->setFlash('success', 'Активация завершена. Можете использовать логин и пароль для входа');
                return $this->redirect('login');
            }
        }
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionActivation(){
        /* @var $usr User */
        $model = new Customers(['scenario' => Customers::ACCOUNT_ACTIVATE]);
        $deny = false;
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $usr = User::findByFullname($model->inn);
                if ($usr){
                    if ($usr->status == User::STATUS_ACTIVATED) {
                        Yii::$app->session->setFlash('info', 'Ваш аккаунт уже активирован! Используйте полученные логин и пароль для входа.<br>'
                            . 'Если Вы забыли пароль, можно еще раз <b>' . Html::a('отправить письмо', ['site/send-mail-again', 'id' => $usr->id])
                            . '</b> с логином и паролем. Старый пароль при этом сбросится!'
                        );
                    } elseif ($usr->status == User::STATUS_ACTIVE) {
                        Yii::$app->session->setFlash('info', 'Ваш аккаунт был активирован ранее, используйте полученный логин и пароль для входа');
                    }
                    return $this->redirect('login');
                }
                $usr = new User();
                $usr->username = $model->customer_email;
                $usr->fullname = $model->inn;
                $usr->_1c_id = 'отсутствует';
                $newpass = $usr->generatePassword();
                $usr->auth_key = Yii::$app->security->generateRandomString();
                $usr->password_hash = Yii::$app->security->generatePasswordHash($newpass);
                $usr->password_reset_token = NULL;
                $usr->email = $model->customer_email;
                $usr->status = User::STATUS_DELETED; //Помечаем пользователя как активированного но еще не выполнившего первый вход
                $usr->save();
                //назначаем новому пользователю роль
                $role = Yii::$app->authManager->getRole('user');
                Yii::$app->authManager->assign($role, $usr->id);
                //привязываем к пользователю всех контрагентов с одинаковым email
                Customers::updateAll(['user_id' => $usr->id], ['customer_email' => $model->customer_email]);
                //отправляем письмо
                $urlHome = 'http://portal.altburenka.ru';

                $body = 'Здравствуйте!<br>'
                    . 'Вы получили это письмо потому, что запросили активацию своего аккаунта на портале www.portal.altburenka.ru<br>'
                    . 'Если Вы этого не делали то обратитесь к администратору портала: it7@altburenka.ru<br>'
                    . '<br>Ваши регистрационные данные:<br>'
                    . 'Логин: <b>'. $model->customer_email . '</b><br>'
                    . 'Пароль: <b>' . $newpass . '</b><br>'
                    . 'Для завершения активации пройдите по ссылке: ' . $urlHome . Url::to(['activation-complete', 'auth' => $usr->auth_key])
                    . '<br> Благодарим за использование нашего портала.';
                Emails::sendMail($model->customer_email, 'Активация аккаунта', $body);

                Yii::$app->session->setFlash('success', 'На указанный адрес выслано письмо с инструкцией по активации...');
            } else {
                Yii::$app->session->setFlash('error', 'Данные введены не верно!');
                return $this->render('activation',[
                    'model' => $model,
                    'deny' => $deny
                ]);
            }
            return $this->redirect('login');
        }
        return $this->render('activation',[
            'model' => $model,
            'deny' => $deny
        ]);
    }

    public function actionValidation(){
        $model = new Customers(['scenario' => Customers::ACCOUNT_ACTIVATE]);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {

            Yii::$app->response->format = 'json';
            return ActiveForm::validate($model);
        }
    }
}
