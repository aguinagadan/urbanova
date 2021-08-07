$(document).ready(function () {

    var curso = $("#curso");
    var participantes = $("#participantes");
    var options = $("#options");
    var select_x = $("#select_x");

    var selected_options = [];

    $.post("../local/customfront/api/ajax_controller.php", {
            'request_type': 'obtenerCursosByCat', 'idCat' : 1 },
        function(data) {
            var curso = $("#curso");
            curso.empty();
            for (var i=0; i<data.data.length; i++) {
                curso.append('<option value="' + data.data[i].id + '">' + data.data[i].title + '</option>');
            }
        }, "json");

    $.post("../local/customfront/api/ajax_controller.php", {
            'request_type': 'obtenerDepartamentos'},
        function(data) {
            var participantes = $("#participantes");
            participantes.empty();
            for (var i=0; i<data.data.length; i++) {
                participantes.append('<option value="' + data.data[i].title + '">' + data.data[i].title + '</option>');
            }
        }, "json");

    curso.change(function(){
        select_x.css({display: "inline-flex"});
    });

    select_x.click(function(){
        curso.val(0);
        select_x.css({display: "none"});
    });

    participantes.change(function(){
        var val = participantes.val();
        if(jQuery.inArray(val, selected_options) == -1 && val != '0'){
            selected_options.push(val);
            var value = $("#participantes option:selected").text();
            options.append('<li class="option"><span>'+value+'</span><a class="search-choice-close" data-id="'+val+'">x</a></li>');
        }
    });

    var btn_matricular = $("#btn_matricular");

    btn_matricular.click(function(){
        $("#formulario").hide();
        $("#success").show();
    });

    $("#options").on("click", "a.search-choice-close", function(){
        $(this).parent().remove();
        var id = $(this).data("id");
        selected_options = jQuery.grep(selected_options, function(value) {
            return value != id;
        });
    });

});