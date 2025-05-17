$(document).ready(function () {
    $(".sideBarli").removeClass("activeLi");
    $(".bannersSideA").addClass("activeLi");

    $("#bannersTable").dataTable({
        processing: true,
        serverSide: true,
        serverMethod: "post",
        bFilter: false,
        bInfo: false,
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchBannersList`,
            data: function (data) {},
            error: (error) => {
                console.log(error);
            },
        },
    });

    $("#addBannerForm").on("submit", function (event) {
        event.preventDefault();
        $(".loader").show();
        if (user_type == "1") {
            var formdata = new FormData($("#addBannerForm")[0]);
            $.ajax({
                url: `${domainUrl}addBanner`,
                type: "POST",
                data: formdata,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    $(".loader").hide();
                    $("#addBannerModal").modal("hide");
                    $("#addBannerForm").trigger("reset");
                    $("#bannersTable").DataTable().ajax.reload(null, false);
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

    $("#bannersTable").on("click", ".delete", function (event) {
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
                    var url = `${domainUrl}deleteBanner` + "/" + id;

                    $.getJSON(url).done(function (data) {
                        console.log(data);
                        $("#bannersTable").DataTable().ajax.reload(null, false);
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

    $("#categoriesTable").on("click", ".edit", function (event) {
        event.preventDefault();

        var title = $(this).data("title");
        var icon = $(this).data("icon");
        var id = $(this).attr("rel");

        $("#editSalonCatId").val(id);
        $("#editSalonCatTitle").val(title);
        $("#imgSalonCat").attr("src", icon);

        $("#editSalonCatModal").modal("show");
    });
});
