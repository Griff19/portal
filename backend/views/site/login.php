<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = 'Авторизация';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login" align="center">
    <h1><?= 'Добро пожаловать!' ?></h1>
    <?php if ($deny) {?>
    <h2> На сайте ведутся технические работы! Приносим извинения за неудобства. </h2>
    <?php } ?>
    <p>Заполните поля чтобы осуществить вход:</p>

    <div class="row">
        <div style="width: 250px">
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                <?= $form->field($model, 'username') ?>
                <?= $form->field($model, 'password')->passwordInput() ?>

                <?= $form->field($model, 'rememberMe')->checkbox() ?>
                <div class="form-group">
                    <?= Html::submitButton('Войти', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
        <p>Если вы здесь впервые то вам необходимо <?= Html::a('Активировать', ['site/activation'])?> свой аккаунт.</p>
    </div>

</div>
