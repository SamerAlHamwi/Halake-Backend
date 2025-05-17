$(document).ready(function () {
    $(".sideBarli").removeClass("activeLi");
    $(".staffSideA").addClass("activeLi");

    // Fetch Sound Categories
    var url = `${domainUrl}getFaqCats`;
    var faqCategories;
    $.getJSON(url).done(function (data) {
        faqCategories = data.data;
    });

    $("#staffTable").dataTable({
        dom: "Bfrtip",
        buttons: ["copy", "csv", "excel", "pdf", "print"],
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
            url: `${domainUrl}fetchStaffList`,
            data: function (data) {},
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
});
