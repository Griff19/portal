/**
 * Функция для открытия модального окна и загрузки в него
 * данных по адресу из параметра
 * @param a
 */
function buttClick(a) {
    $('#modal').modal('show')
        .find('#modalContent')
        .load(a);
    $('.modal-dialog').css({"margin": "150px auto"});
}

/**
 * Функция добавления товаров в заказ через ajax и отображение суммы заказа в шапке страницы
 * Правило 'basketadd' описано в основном конфиге 'config\main.php'
 */
function changeNumber() {
    $("input[name^='count_']").change(function () {
        var hashid = $(this).attr('id');
        var count = $(this).val();
        $.get('basketadd/' + hashid + '/' + count, function (data) {
            //$.get('basketadd', { hash_id : hashid, count : count }, function(data){
            $('#getTotals a').text(data); //в шапке страницы отображаем текущую сумму
        });
    });
}
/**
 * Отображение всплывающих подсказок при наведении на элемент
 */
function showInfo() {

    $("a[name='info']").each(function () {
        $(this).popover({
            trigger: "manual",
            html: true,
            content: '<img src="' + $(this).attr("data-img") + '" width="250px">'

        }).on("mouseenter", function () {
            var _this = this;
            $(this).popover("show");
            $(".popover").on("mouseleave", function () {
                $(_this).popover('hide');
            }).css({
                position: "absolute",
                //left : "600",
                //top : "",
            });
        }).on("mouseleave", function () {
            var _this = this;
            setTimeout(function () {
                if (!$(".popover:hover").length) {
                    $(_this).popover("hide")
                }
            }, 1);
        });
    });
}

$("input").on('focus', function () {
    $(this).select();
});


