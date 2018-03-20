<?php
/**
 * 登录视图
 */
if (!isset($SysName)) {
    exit();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
        <title><?php echo $SysName; ?></title>
        <link rel="icon" href="./img/favicon.ico">
        <link href="./plugins/bootstrap-3.3.7/css/bootstrap.min.css" rel="stylesheet">
        <link href="./plugins/animate/animate.min.css" rel="stylesheet">
        <link href="./css/style.css" rel="stylesheet">
        <script src="./plugins/jquery-2.1.4/jquery.min.js"></script>
        <script src="./plugins/bootstrap-3.3.7/js/bootstrap.min.js"></script>
    </head>
    <body id="loginBody">
        <div id="loginBox">
            <div class="login_form">
                <p><span class="text">host</span><input class="host" type="text" name="host" placeholder="127.0.0.1" spellcheck="false"></p>
                <p><span class="text">port</span><input class="port" type="text" name="port" placeholder="6379" spellcheck="false"></p>
                <p><span class="text">auth</span><input class="auth" type="password" name="auth"></p>
                <p class="btn_row"><span class="login_btn btn btn-primary" onclick="Login(this)">登录</span></p>
            </div>
            <p class="version">Version：<?php echo $Version; ?></p>
        </div>
        <p id="copyright">Copyright © 2018-2019 彭运京 版权所有.</p>

        <div id="tipBox"><p class="msg animated"></p></div>
    </body>
    <script src="js/js.js"></script>
</html>
