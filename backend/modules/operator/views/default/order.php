<?php
/**
 * Окно дозвона и набора заказа
 */

use yii\grid\GridView;
use yii\grid\Column;
use yii\helpers\Html;

/**
 * @var $customer \backend\models\Customers
 * @var $goodData \yii\data\ActiveDataProvider
 * @var $goodSearch \backend\models\GoodsSearch
 */
?>

<h1>Звоним: "<?= $customer->customer_name ?>" </h1>
<p>Ответственный: <b><?= $customer->directResponsible ?></b></p>

<?= GridView::widget([
        'tableOptions' => ['id' => 'goods', 'class' => 'table table-bordered'],
        'dataProvider' => $goodData,
        'filterModel' => $goodSearch,
        'columns' => [
            ['attribute' => 'good_name',
                'value' => function($model){
                    return Html::a($model->good_name, '#');
                },
                'format' => 'raw',
                'filterInputOptions' => ['id' => 'search', 'class' => 'form-control', 'autocomplete' => 'off']
            ],
            'good_description',
            ['class' => Column::className(),
                'header' => 'Количество',
                'content' => function($model){
                    return Html::input('number', 'count'.$model->good_id, 0, [
                            'class' => 'form-control count', 'id' => $model->good_id
                    ]);
                }
            ],
        ]]);
?>
<?php
$script = <<<JS
    var idx;
    var len;
    // Останавливаем "всплытие" события 'change' чтобы предотвратить стандартную обработку этого события
    // и не допустить обновления страницы
    $('#search').on('change', function(e) {
        e.stopPropagation();  
    });
    // Ожидаем ввод в строку фильтра и отправляем текущее состояние строки на сервер для поиска данных
    // Перед выводом очищаем таблицу и блок пагинации, получаем строку html и вставляем в тело таблицы как есть
    $('#search').on('input', function () {
        $('ul.pagination').text('');
        //$('#goods > tbody').text('');
        $.post('/goods/search-good?text='+ $(this).val() +"&tp=$customer->typeprices_id", function(res) {
            var thtml = $.parseJSON(res);
            $('#goods > tbody').html(thtml);              
        });
        idx = -1;
    });
    // Для перехода к строкам таблицы ждем когда в поле ввода нажмут клавишу "вниз"
    // После этого фокусируемся на первой строке таблицы        
    $('#search').on('keydown', function(e) {
        if (e.keyCode === 40) {
            idx = 0; 
            $('.chain').eq(idx).focus();
            $('#goods > tbody > tr').eq(idx).addClass('success').siblings().removeClass('success');
         }
    });
    // Для перехода по строкам таблицы вверх и вниз, ожидаем нажатие клавиш "вверх" и "вниз" в теле таблицы
    // соответствующим образом изменяем индекс текущего элемента +1 или -1
    $('#goods > tbody').on('keydown', function(e) {
        if (e.target.nodeName !== "INPUT"){ 
            len = $('#goods > tbody > tr').length - 1;
            if (e.keyCode === 40) {
                if (idx < len) idx += 1; 
                else idx = len;
            } else if (e.keyCode === 38) {
                if (idx > 0) idx -= 1;
                else idx = 0;
            } 
            
            $('.chain').eq(idx).focus();
            $('#goods > tbody > tr').eq(idx).addClass('success').siblings().removeClass('success');
        }
    });
    // Для возобновления поиска просто начинаем вводить новую строку, произойдет переход в поле ввода
    // для установки количества заказываемого товара набираем число, произойтет заполнение поля количества
    // при нажатии клавиши "enter" происходит переход к следующей строке
    $('#goods > tbody').on('keypress', function(e) {
        if (e.keyCode === 13) {
            idx += 1; 
            $('.chain').eq(idx).focus();
            $('#goods > tbody > tr').eq(idx).addClass('success').siblings().removeClass('success');
            return true
        }        
        
        if (e.which != 0 && e.charCode != 0) { // все кроме IE
            if (!e.which < 32) // спец. символ
                var c = String.fromCharCode(e.which); // остальные
        }
        if (isNaN(parseInt(c))) {
            $('#search').focus();
            //$('#search').val(c);
        } else {
            if (e.target.nodeName !== "INPUT")
                $('.count').eq(idx).select();              
        }        
    });   
JS;

$this->registerJs($script);
?>
