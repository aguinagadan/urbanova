$(document).ready(function () {

    var curso = $("#curso");
    var participantes = $("#participantes");
    var options = $("#options");
    var select_x = $("#select_x");
    var selected_options = [];

    $.ajax({
        type: 'POST',
        url: '../local/customfront/api/ajax_controller.php',
        data: {'request_type': 'obtenerCursosByCat', 'idCat' : 1 },
        success: function(data) {
            var curso = $("#curso");
            curso.empty();
            for (var i=0; i<data.data.length; i++) {
                curso.append('<option value="' + data.data[i].id + '">' + data.data[i].title + '</option>');
            }
        },
        async:false
    });

    $.ajax({
        type: 'POST',
        url: '../local/customfront/api/ajax_controller.php',
        data: {'request_type': 'obtenerDepartamentos'},
        success: function(data) {
            var participantes = $("#participantes");
            participantes.empty();
            participantes.append('<option value="0">Selecciona a los participantes</option>');
            participantes.append('<option value="all">MATRICULAR A TODOS LOS USUARIOS</option>');
            for (var i=0; i<data.data.length; i++) {
                participantes.append('<option value="' + data.data[i] + '">' + data.data[i] + '</option>');
            }
            var roles = data.roles;
            $.each(roles, function(i, obj) {
                $("#participantes").append('<option value="' + obj.id + '">' + obj.name + '</option>');
            });
        }
    });

    $.ajax({
        type: 'POST',
        url: '../local/customfront/api/ajax_controller.php',
        data: {'request_type': 'obtenerRecordatorios', 'courseId': curso.children('option:first').val()},
        success: function(data) {
            if(data.lunes == 1) {
                $("#lunes").attr('checked', data.lunes);
            }
            if(data.viernes == 1) {
                $("#viernes").attr('checked', data.viernes);
            }
            if(data.tresdias == 1) {
                $("#tresdias").attr('checked', data.tresdias);
            }
            if(data.undia == 1) {
                $("#undia").attr('checked', data.undia);
            }
        }
    });

    curso.change(function(){
        select_x.css({display: "inline-flex"});
    });

    select_x.click(function(){
        curso.val(0);
        select_x.css({display: "none"});
    });

    curso.change(function(){
        var val = curso.val();
        $("#lunes").attr('checked', false);
        $("#viernes").attr('checked', false);
        $("#tresdias").attr('checked', false);
        $("#undia").attr('checked', false);

        $.ajax({
            type: 'POST',
            url: '../local/customfront/api/ajax_controller.php',
            data: {'request_type': 'obtenerRecordatorios', 'courseId': val},
            success: function(data) {
                if(data.lunes == 1) {
                    $("#lunes").attr('checked', data.lunes);
                }
                if(data.viernes == 1) {
                    $("#viernes").attr('checked', data.viernes);
                }
                if(data.tresdias == 1) {
                    $("#tresdias").attr('checked', data.tresdias);
                }
                if(data.undia == 1) {
                    $("#undia").attr('checked', data.undia);
                }
            }
        });
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

    btn_matricular.click(function() {

        $.ajax({
            type: 'POST',
            url: '../local/customfront/api/ajax_controller.php',
            data: {
                'idCurso': $("#curso").val(),
                'departamentos': selected_options,
                'newUsers': $("#new_users").is(':checked'),
                'lunes': $("#lunes").is(':checked'),
                'viernes': $("#viernes").is(':checked'),
                'tresdias': $("#tresdias").is(':checked'),
                'undia': $("#undia").is(':checked'),
                'request_type': 'matricular'
            },
            success: function(data) {
                if(data.status) {
                    $("#formulario").hide();
                    $("#success").show();
                } else {
                    alert("No se pudo matricular");
                }
            }
        });
    });

    $("#options").on("click", "a.search-choice-close", function(){
        $(this).parent().remove();
        var id = $(this).data("id");
        selected_options = jQuery.grep(selected_options, function(value) {
            return value != id;
        });
    });

    $("#btn_continuar").click(function() {
        window.location.href = '/matricula';
        return false;
    });
});