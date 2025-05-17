$(document).ready(function () {
    $(".sideBarli").removeClass("activeLi");

    var bookingId = $("#bookingId").val();
    var bookingIdBig = $("#bookingIdBig").val();
    console.log(bookingIdBig);

    $("#print-payment").on("click", function (event) {
        event.preventDefault();
        $("#details-body").printThis({
            importCSS: true,
            importStyle: true,
        });
    });
    $("#download-pdf").on("click", function (event) {
        event.preventDefault();
        var element = document.getElementById("details-body");
        var opt = {
            margin: 1,
            filename: `${bookingIdBig}.pdf`,
            image: { type: "jpeg", quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: "in", format: "letter", orientation: "portrait" },
        };

        // New Promise-based usage:
        html2pdf().set(opt).from(element).save();
    });
});
