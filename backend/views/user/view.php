<?php

use backend\models\User;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model backend\models\User */

$this->title = 'Пользователь: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->username;
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php if (Yii::$app->session->hasFlash('fail')) { ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('fail'); ?>
        </div>
    <?php } ?>
    <p>

        <?php
        echo Html::a('Изменить пароль', ['changepass', 'id' => $model->id], ['class' => 'btn btn-primary']);
        if (Yii::$app->user->can('operator')) {
            echo ' ';
            echo Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']);
            echo ' ';
            echo Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Удалить пользователя?',
                    'method' => 'post',
                ],
            ]);
        } ?>
    </p>
    <?php
    $rowsuser = [
        'model' => $model,
        'attributes' => [
            //'id',
            'username',
            'fullname',
            //'auth_key',
            //'password_hash',
            //'password_reset_token',
            'email:email',
            //'status',
            'created_at:datetime',
            //'updated_at:datetime',
        ]
    ];

    $rowsadmin = [
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            'fullname',
            //'auth_key',
            //'password_hash',
            //'password_reset_token',
            'email:email',
            //'status',
            'created_at:datetime',
            //'updated_at:datetime',
            [
                'label' => 'Роль',
                'value' => $model->getRole()->name
            ]
        ]
    ];
    $rowscurr = Yii::$app->user->can('operator') ? $rowsadmin : $rowsuser;
    ?>
    <?=
    DetailView::widget($rowscurr)
    ?>

    <?php
    //Получаем роль выбранного пользователя и выводим её
    $roleUsr = $model->getRole();
    //если форму открыл администратор или оператор то выводим список всех ролей
    if (Yii::$app->user->can('operator')) { //скобка закроется вконце страницы
        //формируем массив всех ролей
        $rolesArr = Yii::$app->authManager->getRoles();
        foreach ($rolesArr as $role) {
            $roles[] = ['name' => $role->name, 'desc' => $role->description];
        }
        //оформляем массив в провайдер для отображения в GridView
        $provider = new ArrayDataProvider([
            'allModels' => $roles,
            'pagination' => ['pageSize' => 10]
        ]);
        //print_r($provider);
        ?>


        <?php Pjax::begin(); ?>
        <?=
        GridView::widget([
            //выводим таблицу ролей и выделяем цветом роль выбранного пользователя
            'dataProvider' => $provider,
            'rowOptions' => function ($model) use ($roleUsr) {
                if ($roleUsr) {
                    if ($model['name'] == $roleUsr->name) {
                        return [
                            'class' => 'success',
                        ];
                    }
                }
            },
            'columns' => [
                [
                    'header' => 'Роль',
                    'value' => 'name',
                ],
                [
                    'header' => 'Описаниие',
                    'value' => 'desc',
                ],
                ['class' => 'yii\grid\Column',
                    'header' => '',
                    'content' => function ($role) use ($model) {
                        //var_dump($model);
                        return Html::a('Применить эту роль', ['user/approle', 'id' => $model->id, 'rolename' => $role['name']], ['data-method' => 'post']);
                    },
                ],
            ],
        ]);
        ?>
        <?php Pjax::end();
    } ?>


</div>
