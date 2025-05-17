$(document).ready(function () {
    $(".sideBarli").removeClass("activeLi");

    var staffId = $("#staffId").val();
    console.log(staffId);

    $("#staffBookingsTable").dataTable({
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
            url: `${domainUrl}fetchStaffBookingList`,
            data: function (data) {
                data.staffId = staffId;
            },
            error: (error) => {
                console.log(error);
            },
        },
    });

    $("#editStaffForm").on("submit", function (event) {
        event.preventDefault();
        $(".loader").show();
        if (user_type == "1") {
            var formdata = new FormData($("#editStaffForm")[0]);
            $.ajax({
                url: `${domainUrl}editStaff_Admin`,
                type: "POST",
                data: formdata,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    $(".loader").hide();
                    location.reload();
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
});
