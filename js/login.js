function Login(btn) {
    if (window.Logining === true) return;
    var host = $.trim($('#loginBox .host').val());
    var port = $.trim($('#loginBox .port').val());
    var auth = $.trim($('#loginBox .auth').val());
    var code = $.trim($('#loginBox .code').val());
    if (host.length == 0) {
        host = '127.0.0.1';
    }
    if (port.length == 0) {
        port = '6379';
    }

    LoginStart();
    $.ajax({
        url: ApiUrl,
        type: 'post',
        dataType: 'json',
        data: {
            method: 'Login',
            host: host,
            port: port,
            auth: auth,
            code: code
        },
        success: function(res) {
            if (res.code == 200) {
                location.reload();
            } else {
                if (res.msg) {
                    Msg(res.msg);
                } else {
                    Msg('登录失败');
                }
                LoginEnd();
            }
        },
        error: function() {
            Msg('系统出错');
            LoginEnd();
        }
    });
}

function LoginStart() {
    $('#loginBox .login_btn').attr('disabled', 'disabled');
    $('#loginBox .login_btn').text('登录中...');
    window.Logining = true;
}

function LoginEnd() {
    $('#loginBox .login_btn').attr('disabled', false);
    $('#loginBox .login_btn').text('登录');
    window.Logining = false;
}
