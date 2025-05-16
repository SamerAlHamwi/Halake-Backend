$(document).ready(function () {
    $(".sideBarli").removeClass("activeLi");
    $(".servicesSideA").addClass("activeLi");

    $("#servicesTable").dataTable({
        dom: "Bfrtip",
        buttons: ["copy", "csv", "excel", "pdf", "print"],
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchAllServicesList`,
            data: function (data) {},
            error: (error) => {
                console.log(error);
            },
        },
    });

    $("#servicesTable").on("change", ".onoff", function (event) {
        event.preventDefault();
        if ($(this).prop("checked") == true) {
            var value = 1;
        } else {
            value = 0;
        }
        var itemId = $(this).attr("rel");

        var url = `${domainUrl}changeServiceStatus/${itemId}/${value}`;

        $.getJSON(url).done(function (data) {
            $("#servicesTable").DataTable().ajax.reload(null, false);

            iziToast.success({
                title: strings.success,
                message: strings.operationSuccessful,
                position: "topRight",
                timeOut: 3000,
            });
        });
    });

    $("#servicesTable").on("click", ".delete", function (event) {
        event.preventDefault();
        swal({
            title: strings.doYouReallyWantToContinue,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((isConfirm) => {
            if (isConfirm) {
                if (user_type == "1") {
                    var id = $(this).attr("rel");
                    var url = `${domainUrl}deleteService` + "/" + id;

                    $.getJSON(url).done(function (data) {
                        console.log(data);
                        $("#servicesTable")
                            .DataTable()
                            .ajax.reload(null, false);
                        iziToast.success({
                            title: strings.success,
                            message: strings.operationSuccessful,
                            position: "topRight",
                        });
                    });
                } else {
                    iziToast.error({
                        title: strings.error,
                        message: strings.youAreTester,
                        position: "topRight",
                    });
                }
            }
        });
    });
});
