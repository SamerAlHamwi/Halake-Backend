$(document).ready(function () {
    $(".sideBarli").removeClass("activeLi");
    $(".salonsSideA").addClass("activeLi");

    $("#pendingSalonTable").dataTable({
        dom: "Bfrtip",
        buttons: ["copy", "csv", "excel", "pdf", "print"],
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4, 5],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchPendingSalonList`,
            data: function (data) {},
            error: (error) => {
                console.log(error);
            },
        },
    });
    $("#bannedSalonTable").dataTable({
        dom: "Bfrtip",
        buttons: ["copy", "csv", "excel", "pdf", "print"],
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4, 5],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchBannedSalonList`,
            data: function (data) {},
            error: (error) => {
                console.log(error);
            },
        },
    });
    $("#activeSalonTable").dataTable({
        dom: "Bfrtip",
        buttons: ["copy", "csv", "excel", "pdf", "print"],
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4, 5, 6, 7],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchActiveSalonList`,
            data: function (data) {},
            error: (error) => {
                console.log(error);
            },
        },
    });

    $("#activeSalonTable").on("change", ".topRated", function (event) {
        event.preventDefault();
        if ($(this).prop("checked") == true) {
            var value = 1;
        } else {
            value = 0;
        }
        var itemId = $(this).attr("rel");

        var url = `${domainUrl}changeSalonTopRatedStatus/${itemId}/${value}`;

        $.getJSON(url).done(function (data) {
            $("#activeSalonTable").DataTable().ajax.reload(null, false);

            iziToast.success({
                title: strings.success,
                message: strings.operationSuccessful,
                position: "topRight",
                timeOut: 3000,
            });
        });
    });
    $("#signUpOnlySalonTable").dataTable({
        dom: "Bfrtip",
        buttons: ["copy", "csv", "excel", "pdf", "print"],
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchSignUpOnlySalonList`,
            data: function (data) {},
            error: (error) => {
                console.log(error);
            },
        },
    });

    function reloadTables() {
        $("#activeSalonTable").DataTable().ajax.reload(null, false);
        $("#bannedSalonTable").DataTable().ajax.reload(null, false);
        $("#pendingSalonTable").DataTable().ajax.reload(null, false);
    }

    $("#activeSalonTable").on("click", ".ban", function (event) {
        event.preventDefault();
        swal({
            title: strings.doYouReallyWantToContinue,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                if (user_type == "1") {
                    var id = $(this).attr("rel");
                    var url = `${domainUrl}banSalon` + "/" + id;

                    $.getJSON(url).done(function (data) {
                        console.log(data);

                        reloadTables();

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
    $("#pendingSalonTable").on("click", ".ban", function (event) {
        event.preventDefault();
        swal({
            title: strings.doYouReallyWantToContinue,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                if (user_type == "1") {
                    var id = $(this).attr("rel");
                    var url = `${domainUrl}banSalon` + "/" + id;

                    $.getJSON(url).done(function (data) {
                        console.log(data);

                        reloadTables();

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
    $("#bannedSalonTable").on("click", ".activate", function (event) {
        event.preventDefault();
        swal({
            title: strings.doYouReallyWantToContinue,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                if (user_type == "1") {
                    var id = $(this).attr("rel");
                    var url = `${domainUrl}activateSalon` + "/" + id;

                    $.getJSON(url).done(function (data) {
                        console.log(data);

                        reloadTables();

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
