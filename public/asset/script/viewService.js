$(document).ready(function () {
    $(".sideBarli").removeClass("activeLi");

    var serviceId = $("#serviceId").val();
    console.log(serviceId);

    $("#serviceStatus").on("change", function (event) {
        event.preventDefault();
        if ($(this).prop("checked") == true) {
            var value = 1;
        } else {
            value = 0;
        }
        var itemId = $(this).attr("rel");

        var url = `${domainUrl}changeServiceStatus/${itemId}/${value}`;

        $.getJSON(url).done(function (data) {
            location.reload();
        });
    });

    $("#serviceForm").on("submit", function (event) {
        event.preventDefault();
        if (user_type == "1") {
            var formdata = new FormData($("#serviceForm")[0]);

            $.ajax({
                url: `${domainUrl}updateService_Admin`,
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

    $("#deleteService").on("click", function (event) {
        event.preventDefault();
        swal({
            title: strings.doYouReallyWantToContinue,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((isConfirm) => {
            if (isConfirm) {
                if (user_type == "1") {
                    var url = `${domainUrl}deleteService` + "/" + serviceId;
                    $.getJSON(url).done(function (data) {
                        console.log(data);
                        history.back();
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
                    var url = `${domainUrl}deleteServiceImage` + "/" + id;

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
