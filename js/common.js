var ApiUrl = './Api.php';

function Msg(msg) {
    if (window.MsgTimerP) {
        clearTimeout(window.MsgTimerP);
    }
    if (window.MsgTimerC) {
        clearTimeout(window.MsgTimerC);
    }
    $('#tipBox .msg').text(msg);
    $('#tipBox .msg').removeClass('bounceIn').removeClass('zoomOut');
    $('#tipBox .msg').addClass('bounceIn');
    $('#tipBox').show();
    window.MsgTimerP = setTimeout(function () {
        $('#tipBox .msg').removeClass('bounceIn').addClass('zoomOut');
        window.MsgTimerC = setTimeout(function () {
            $('#tipBox').hide();
        },1000);
    },1000);
}