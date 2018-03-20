<?php
/**
 * 入口文件
 */

/* 加载核心文件 */
require_once ('Start.php');
require_once ('RedisClass.php');
require_once ('Constant.php');

/* 加载视图文件 */
$SysName = SYS_NAME;
$Version = SYS_VERSION;
if (empty($_SESSION['redis_manager'])) {
	require_once ('view/login.php');
} else {
	require_once ('view/index.php');
}
