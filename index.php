<?php
/**
 * 入口文件
 */

/* 加载核心文件 */
require_once ('Config.php');
require_once ('Start.php');
require_once ('RedisClass.php');

/* 加载视图文件 */
if (empty($_SESSION['redis_manager'])) {
	require_once ('view/login.php');
} else {
	require_once ('view/index.php');
}
