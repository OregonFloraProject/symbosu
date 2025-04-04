$(document).ready(function () {
    // Gets the video src from the data-src on each button

    var $videoSrc;
    $(".video-modal-btn").click(function () {
        $videoSrc = $(this).data("src");
    });
    //console.log($videoSrc);

    // when the modal is opened autoplay it
    $("#videoModal").on("shown.bs.modal", function (e) {
        // set the video src to autoplay and not to show related video. Youtube related video is like a box of chocolates... you never know what you're gonna get
        $("#video").attr(
            "src",
            $videoSrc + "?autoplay=1&amp;modestbranding=1&amp;showinfo=0&rel=0"
        );
    });

    // stop playing the youtube video when I close the modal
    $("#videoModal").on("hide.bs.modal", function (e) {
        // a poor man's stop video
        $("#video").attr("src", $videoSrc);
    });

    // document ready
});
