<?php
use backend\models\Actstable;
use backend\models\Images;
use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model backend\models\Actstable */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="actstable-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?php
        if (!empty($model->act_num)){
            echo '<p> <strong> Номер Акта: </strong>' . $model->act_num . '</p>'; 
            echo '<p> <strong> Дата док: </strong>' . $model->date_doc . ' ';
            echo '<strong> Номер док: </strong>' . $model->num_doc . ' ';
            echo '<strong> Наименование: </strong>' . $model->name_doc . '</p>';
            echo '<p> <strong> Начальное сальдо: </strong>' . ($model->beg_sald / 100) . ' ';
            echo '<strong> Конечное сальдо: </strong>' . ($model->end_sald / 100) . ' </p>';
        }else{
            echo $form->field($model, 'act_num')->textInput(['maxlength' => 18]);
            echo $form->field($model, 'date_doc')->textInput();
            echo $form->field($model, 'num_doc')->textInput(['maxlength' => 11]);
            echo $form->field($model, 'name_doc')->textInput(['maxlength' => 200]);
            echo $form->field($model, 'beg_sald')->textInput();
            echo $form->field($model, 'end_sald')->textInput();
        }        
    ?>
     
    <?= $form->field($model, 'actstable_comm')->textarea(['rows' => 4, 'style' => 'resize:vertical']); ?>
    <?= $form->field($model, 'file')->fileInput(); ?>
    <?php
    $imgs = Images::getImages(Actstable::tableName() . $model->num_doc);
    //die;
    if (!empty($imgs)){
        echo '<div class="row" style="position: relative">';
        foreach ($imgs as $img) {
            echo '<div class="col-lg-3" >';
            //echo '<img src="'.$img->img_newname.'" style="height:250px">';
            echo Html::img('../../' . $img->img_newname, ['style' => 'height:250px']);
            echo '<br>';
            //
            echo '</div>';
        }
        echo '</div>';
        echo Html::a('Изображения', ['images/index', 'owner' => ActsTable::tableName() . $model->num_doc, 'id_act' => $model->acts_id, 'num' => $model->num_doc], ['style' => 'position:absolute; right:50%; padding: 2px; margin: 2px;']);

    }?>
    <br>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
