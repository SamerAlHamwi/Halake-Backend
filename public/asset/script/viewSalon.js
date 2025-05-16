$(document).ready(function () {
    $(".sideBarli").removeClass("activeLi");

    var salonId = $("#salonId").val();
    console.log(salonId);

    $(".activateSalon").on("click", function (event) {
        event.preventDefault();
        swal({
            title: strings.doYouReallyWantToContinue,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((isTrue) => {
            if (isTrue) {
                if (user_type == "1") {
                    var url = `${domainUrl}activateSalon` + "/" + salonId;

                    $.getJSON(url).done(function (data) {
                        console.log(data);

                        location.reload();

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
    $("#banSalon").on("click", function (event) {
        event.preventDefault();
        swal({
            title: strings.doYouReallyWantToContinue,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((isTrue) => {
            if (isTrue) {
                if (user_type == "1") {
                    var url = `${domainUrl}banSalon` + "/" + salonId;

                    $.getJSON(url).done(function (data) {
                        console.log(data);

                        location.reload();

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

    $("#earningsTable").dataTable({
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchSalonEarningsList`,
            data: function (data) {
                data.salonId = salonId;
            },
            error: (error) => {
                console.log(error);
            },
        },
    });

    $("#salonDetailsForm").on("submit", function (event) {
        event.preventDefault();
        if (user_type == "1") {
            var formdata = new FormData($("#salonDetailsForm")[0]);
            $.ajax({
                url: `${domainUrl}updateSalonDetails_Admin`,
                type: "POST",
                data: formdata,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    location.reload();
                },
                error: (error) => {
                    $(".loader").hide();
                    console.log(JSON.stringify(error));
                },
            });
        } else {
            $(".loader").hide();
            iziToast.error({
                title: strings.error,
                message: strings.youAreTester,
                position: "topRight",
            });
        }
    });
    $("#addSalonImagesForm").on("submit", function (event) {
        event.preventDefault();
        $(".loader").show();
        if (user_type == "1") {
            var formdata = new FormData($("#addSalonImagesForm")[0]);
            $.ajax({
                url: `${domainUrl}addImagesToSalon`,
                type: "POST",
                data: formdata,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    location.reload();
                },
                error: (error) => {
                    $(".loader").hide();
                    console.log(JSON.stringify(error));
                },
            });
        } else {
            $(".loader").hide();
            iziToast.error({
                title: strings.error,
                message: strings.youAreTester,
                position: "topRight",
            });
        }
    });

    $("#awardsTable").dataTable({
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchSalonAwardsList`,
            data: function (data) {
                data.salonId = salonId;
            },
            error: (error) => {
                console.log(error);
            },
        },
    });
    $("#reviewsTable").dataTable({
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchSalonReviewsList`,
            data: function (data) {
                data.salonId = salonId;
            },
            error: (error) => {
                console.log(error);
            },
        },
    });
    $("#staffTable").dataTable({
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
            url: `${domainUrl}fetchSalonStaffList`,
            data: function (data) {
                data.salonId = salonId;
            },
            error: (error) => {
                console.log(error);
            },
        },
    });
    $("#staffTable").on("change", ".onoff", function (event) {
        event.preventDefault();
        if ($(this).prop("checked") == true) {
            var value = 1;
        } else {
            value = 0;
        }
        var itemId = $(this).attr("rel");

        var url = `${domainUrl}changeStaffStatus/${itemId}/${value}`;

        $.getJSON(url).done(function (data) {
            $("#staffTable").DataTable().ajax.reload(null, false);

            iziToast.success({
                title: strings.success,
                message: strings.operationSuccessful,
                position: "topRight",
                timeOut: 3000,
            });
        });
    });
    $("#galleryTable").dataTable({
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
            url: `${domainUrl}fetchSalonGalleryList`,
            data: function (data) {
                data.salonId = salonId;
            },
            error: (error) => {
                console.log(error);
            },
        },
    });
    $("#salonPayOutsTable").dataTable({
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4, 5, 6],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchSalonPayoutRequestsList`,
            data: function (data) {
                data.salonId = salonId;
            },
            error: (error) => {
                console.log(error);
            },
        },
    });
    $("#salonPayOutsTable").on("click", ".complete", function (event) {
        event.preventDefault();
        var id = $(this).attr("rel");
        $("#completeId").val(id);

        $("#completeModal").modal("show");
    });
    $("#salonPayOutsTable").on("click", ".reject", function (event) {
        event.preventDefault();
        var id = $(this).attr("rel");
        $("#rejectId").val(id);

        $("#rejectModal").modal("show");
    });

    $("#staffTable").on("click", ".delete", function (event) {
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
                    var url = `${domainUrl}deleteStaffItem` + "/" + id;

                    $.getJSON(url).done(function (data) {
                        console.log(data);
                        $("#staffTable").DataTable().ajax.reload(null, false);
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

    $("#completeForm").on("submit", function (event) {
        event.preventDefault();
        $(".loader").show();
        if (user_type == "1") {
            var formdata = new FormData($("#completeForm")[0]);
            $.ajax({
                url: `${domainUrl}completeSalonWithdrawal`,
                type: "POST",
                data: formdata,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    $(".loader").hide();
                    $("#completeModal").modal("hide");
                    $("#completeForm").trigger("reset");
                    $("#salonPayOutsTable")
                        .DataTable()
                        .ajax.reload(null, false);
                    iziToast.success({
                        title: strings.success,
                        message: strings.operationSuccessful,
                        position: "topRight",
                    });
                },
                error: (error) => {
                    $(".loader").hide();
                    console.log(JSON.stringify(error));
                },
            });
        } else {
            $(".loader").hide();
            iziToast.error({
                title: strings.error,
                message: strings.youAreTester,
                position: "topRight",
            });
        }
    });
    $("#rejectForm").on("submit", function (event) {
        event.preventDefault();
        $(".loader").show();
        if (user_type == "1") {
            var formdata = new FormData($("#rejectForm")[0]);
            $.ajax({
                url: `${domainUrl}rejectSalonWithdrawal`,
                type: "POST",
                data: formdata,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    $(".loader").hide();
                    $("#rejectModal").modal("hide");
                    $("#rejectForm").trigger("reset");
                    $("#salonPayOutsTable")
                        .DataTable()
                        .ajax.reload(null, false);
                    iziToast.success({
                        title: strings.success,
                        message: strings.operationSuccessful,
                        position: "topRight",
                    });
                },
                error: (error) => {
                    $(".loader").hide();
                    console.log(JSON.stringify(error));
                },
            });
        } else {
            $(".loader").hide();
            iziToast.error({
                title: strings.error,
                message: strings.youAreTester,
                position: "topRight",
            });
        }
    });
    $("#walletStatementTable").dataTable({
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchSalonWalletStatementList`,
            data: function (data) {
                data.salonId = salonId;
            },
            error: (error) => {
                console.log(error);
            },
        },
    });
    $("#bookingsTable").dataTable({
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchSalonBookingsList`,
            data: function (data) {
                data.salonId = salonId;
            },
            error: (error) => {
                console.log(error);
            },
        },
    });
    $("#servicesTable").dataTable({
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchSalonServicesList`,
            data: function (data) {
                data.salonId = salonId;
            },
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


    $("#awardsTable").on("click", ".delete", function (event) {
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
                    var url = `${domainUrl}deleteAward` + "/" + id;

                    $.getJSON(url).done(function (data) {
                        console.log(data);
                        $("#awardsTable").DataTable().ajax.reload(null, false);
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
    $("#reviewsTable").on("click", ".delete", function (event) {
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
                    var url = `${domainUrl}deleteReview` + "/" + id;

                    $.getJSON(url).done(function (data) {
                        console.log(data);
                        $("#reviewsTable").DataTable().ajax.reload(null, false);
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
    $("#galleryTable").on("click", ".delete", function (event) {
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
                    var url = `${domainUrl}deleteGalleryItem` + "/" + id;

                    $.getJSON(url).done(function (data) {
                        console.log(data);
                        $("#galleryTable").DataTable().ajax.reload(null, false);
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
    $("#galleryTable").on("click", ".view", function (event) {
        event.preventDefault();

        var id = $(this).attr("rel");
        var desc = $(this).data("desc");
        var imageUrl = `${sourceUrl}${$(this).data("image")}`;

        $("#imggalleryPreview").attr("src", imageUrl);
        $("#descGalleryPreview").text(desc);

        $("#previewGalleryModal").modal("show");
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

    $(document).on("click", ".img-delete", function (event) {
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
                    var url = `${domainUrl}deleteSalonImage` + "/" + id;

                    $.getJSON(url).done(function (data) {
                        console.log(data);
                        location.reload();
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
