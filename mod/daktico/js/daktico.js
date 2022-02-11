var app = new Vue({
    el: "#app",
    delimiters: ["{(", ")}"],
    data: {
        HTMLcontent: "",
        puntaje: 0,
    },
    created() {},
    methods: {
        dakticoFinalizado: function () {
            let frm = new FormData();
            frm.append("request_type", "actualizarEstadodakticoByUser");
            frm.append("cursoid", cursoid);
            frm.append("puntaje", this.puntaje);
            frm.append("sesskey", sesskey);
            axios.post("api/ajax_controller.php", frm).then((res) => {});
        },
        actividadFinalizada: function () {
            let frm = new FormData();
            frm.append("request_type", "actividadCompletada");
            frm.append("coursemoduleid", coursemoduleid);
            frm.append("completionstate", 1);
            frm.append("sesskey", sesskey);
            axios.post("api/ajax_controller.php", frm).then((res) => {});
        },
        toggleCompletion: function () {
            let frm = new FormData();
            frm.append("id", coursemoduleid);
            frm.append("completionstate", 1);
            frm.append("fromajax", 1);
            frm.append("sesskey", sesskey);
            axios
                .post(url + "/course/togglecompletion.php", frm)
                .then((res) => {
                    console.log(res);
                });
        },
    },
});
