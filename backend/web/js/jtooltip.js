/**
 * ѕодсвечиваем строки таблицы при наведении мыши
 */
$(function() {
   $(".table-bordered tr").hover(
       function(){$(this).css("background-color", "#eeeeee")},
       function(){$(this).css("background-color", "")}
   );

});
