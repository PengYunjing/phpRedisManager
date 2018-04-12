<?php
/**
 * 接口
 */

require_once ('Config.php');
require_once ('Start.php');
require_once ('RedisClass.php');

if (isset($_POST['method']) && !empty($_POST['method'])) {
    $Method = $_POST['method'];
    $ret['code'] = null;

    if ($Method == 'Login') {
        $host = trim($_POST['host']);
        $port = intval(trim($_POST['port']));
        $auth = trim($_POST['auth']);
        $code = trim($_POST['code']);
        if ($code != $GlobalConfig['code']) {
            $ret['msg'] = '内码错误';
            exit(json_encode($ret));
        }
        $Redis = new Redis();
        if ($Redis->connect($host, $port) !== false) {
            if (!empty($auth)) {
                if ($Redis->auth($auth) !== false) {
                    $ret['code'] = 200;
                } else {
                    $ret['msg'] = '连接失败';
                }
            } else {
                $ret['code'] = 200;
            }
        } else {
            $ret['msg'] = '连接失败';
        }
        if ($ret['code'] == 200) {
            $con = array(
                'host' => $host,
                'port' => $port,
                'auth' => $auth
            );
            $_SESSION['redis_manager'] = array(
                'online' => $con,
                'list' => array($con)
            );
        }

        exit(json_encode($ret));
    }
    if ($Method == 'Logout') {
        unset($_SESSION['redis_manager']);
        $ret['code'] = 200;
        exit(json_encode($ret));
    }

    $RedisManager = $_SESSION['redis_manager'];
    if (empty($RedisManager)) {
        exit(json_encode($ret));
    }
    if (empty($RedisManager['list']) || empty($RedisManager['online'])) {
        exit(json_encode($ret));
    }
    if ($Method == 'GetLinks') {
        $ret['data']['list'] = $RedisManager['list'];
        $ret['data']['online'] = $RedisManager['online'];
        $ret['code'] = 200;
        exit(json_encode($ret));
    }
    if ($Method == 'AddLink') {
        $host = trim($_POST['host']);
        $port = intval(trim($_POST['port']));
        $auth = trim($_POST['auth']);
        $list = $RedisManager['list'];
        foreach ($list as $k=> $v) {
            if ($host == $v['host'] && $port == $v['port']) {
                $ret['msg'] = '已有相同的连接';
                exit(json_encode($ret));
            }
        }
        $Redis = new Redis();
        if ($Redis->connect($host, $port) !== false) {
            if (!empty($auth)) {
                if ($Redis->auth($auth) !== false) {
                    $ret['code'] = 200;
                } else {
                    $ret['msg'] = '连接失败';
                }
            } else {
                $ret['code'] = 200;
            }
        } else {
            $ret['msg'] = '连接失败';
        }
        if ($ret['code'] == 200) {
            $con = array(
                'host' => $host,
                'port' => $port,
                'auth' => $auth
            );
            $list[] = $con;
            $_SESSION['redis_manager'] = array(
                'online' => $con,
                'list' => $list
            );
        }
        exit(json_encode($ret));
    }
    if ($Method == 'DelLink') {
        $index = intval($_POST['index']);
        $online = $RedisManager['online'];
        $list = $RedisManager['list'];
        $count = count($list);
        if ($count > 1) {
            array_splice($list, $index, 1);
            $data = array(
                'online' => $online,
                'list' => $list
            );
            $_SESSION['redis_manager'] = $data;
            if (count($_SESSION['redis_manager']['list']) < $count) {
                $ret['data'] = $data;
                $ret['code'] = 200;
            }
        }

        exit(json_encode($ret));
    }
    if ($Method == 'ChangeLink') {
        $index = intval($_POST['index']);
        $host = trim($_POST['host']);
        $port = intval(trim($_POST['port']));
        $auth = trim($_POST['auth']);
        $Redis = new Redis();
        if ($Redis->connect($host, $port) !== false) {
            if (!empty($auth)) {
                if ($Redis->auth($auth) !== false) {
                    $ret['code'] = 200;
                } else {
                    $ret['msg'] = '连接失败';
                }
            } else {
                $ret['code'] = 200;
            }
        } else {
            $ret['msg'] = '连接失败';
        }
        if ($ret['code'] == 200) {
            $con = array(
                'host' => $host,
                'port' => $port,
                'auth' => $auth
            );
            $list = $RedisManager['list'];
            $list[$index] = $con;
            $_SESSION['redis_manager'] = array(
                'online' => $con,
                'list' => $list
            );
        }

        exit(json_encode($ret));
    }

    $RedisConfig = $RedisManager['online'];
    $RedisClass = new RedisClass($RedisConfig);
    $db = intval($_POST['db']);
    $RedisClass->select($db);
    if ($Method == 'LoadData') {
        $key = '*';
        if (isset($_POST['key']) && !empty($_POST['key'])) {
            $key = $key . trim($_POST['key']) . $key;
        }
        for ($i=0; $i < 16; $i++) { 
            $RedisClass->select($i);
            $item['db'] = $i;
            $item['dbname'] = 'db' . $i;
            $item['keys'] = $RedisClass->keys($key);
            $item['total'] = count($item['keys']);
            $ret['data'][] = $item; 
        }
        $arr = $RedisClass->info();
        $info[] = array('text'=>$GlobalConfig['sys_name'].'版本', 'val'=>$GlobalConfig['sys_version']);
        foreach ($arr as $k => $val) {
            if ($k == 'redis_version') {
                $info[] = array('text'=>'Redis版本', 'val'=>$val);
            }
            if ($k == 'connected_clients') {
                $info[] = array('text'=>'连接的客户端数', 'val'=>$val);
            }
            if ($k == 'used_memory_human') {
                $info[] = array('text'=>'当前占用内存', 'val'=>$val);
            }
            if ($k == 'rdb_last_bgsave_status') {
                $info[] = array('text'=>'最近一次rdb持久化是否成功', 'val'=>$val);
            }
            if ($k == 'loading') {
                $info[] = array('text'=>'是否正在载入持久化文件', 'val'=>$val);
            }
            if ($k == 'rdb_bgsave_in_progress') {
                $info[] = array('text'=>'是否正在创建rdb文件', 'val'=>$val);
            }
            if ($k == 'used_memory_peak_human') {
                $info[] = array('text'=>'内存消耗峰值', 'val'=>$val);
            }
        }
        $ret['info'] = $info;
        $ret['code'] = 200;

        exit(json_encode($ret));
    }
    if ($Method == 'GetKeyDetail') {
        if (isset($_POST['key']) && !empty($_POST['key'])) {
            $key = trim($_POST['key']);
            $value = $RedisClass->get($key);
            if ($value !== false) { // 判断类型是否为：string
                $data['type'] = 'string';
            } else {
                $values = $RedisClass->hGetAll($key);
                if (!empty($values)) { // 判断类型是否为：hash
                    $data['type'] = 'hash';
                    foreach ($values as $k => $val) {
                        $item = array(
                            'field' => $k,
                            'value' => $val
                        );
                        $value[] = $item;
                    }
                } else {
                    if ($RedisClass->lLen($key)) { // 判断类型是否为：list
                        $data['type'] = 'list';
                        $value = $RedisClass->lRange($key, 0, 999999999);
                    } else {
                        if ($RedisClass->scard($key)) { // 判断类型是否为：set
                            $data['type'] = 'set';
                            $value = $RedisClass->sMembers($key);
                        } else {
                            if ($RedisClass->zCount($key, 0, 999999999)) { // 判断类型是否为：zset
                                $data['type'] = 'zset';
                                $zsetVals = $RedisClass->zRangeByScore($key,0,999999999, array('withscores'=>false, 'limit'=>array(0,999999999)));
                                $zsetScos = $RedisClass->zRangeByScore($key,0,999999999, array('withscores'=>true, 'limit'=>array(0,999999999)));
                                foreach ($zsetVals as $k => $val) {
                                    $item = array();
                                    $item['value'] = $val;
                                    $item['score'] = $zsetScos[$val];
                                    $value[] = $item;
                                }
                            } else {

                            }
                        }
                    }
                }
            }

            $data['value'] = $value;
            $data['ttl'] = $RedisClass->ttl($key);
            $ret['data'] = $data;
            if (!empty($value) || $value == '') {
                $ret['code'] = 200;
            }

            exit(json_encode($ret));
        }
    }
    if ($Method == 'DelKey') {
        if (isset($_POST['key']) && !empty($_POST['key'])) {
            $key = trim($_POST['key']);
            if ($RedisClass->del($key)) {
                $ret['code'] = 200;
            }

            exit(json_encode($ret));
        }
    }
    if ($Method == 'EditKey') {
        if (isset($_POST['new_key']) && !empty($_POST['new_key'])) {
            $type = trim($_POST['type']);
            $expire = intval($_POST['expire']);
            $row = intval($_POST['row']);
            $oldKey = trim($_POST['old_key']);
            $oldField = trim($_POST['old_field']);
            $oldScore = trim($_POST['old_score']);
            $oldValue = trim($_POST['old_value']);
            $newKey = trim($_POST['new_key']);
            $newField = trim($_POST['new_field']);
            $newScore = trim($_POST['new_score']);
            $newValue = trim($_POST['new_value']);
            if ($newKey != $oldKey) {
                if ($RedisClass->exists($newKey)) {
                    $ret['msg'] = '已有重复Key';
                    exit(json_encode($ret));
                }
            }
            if ($type == 'string') {
                if ($newValue != $oldValue) {
                    if ($RedisClass->set($newKey, $newValue)) {
                        $ret['code'] = 200;
                    }
                } else {
                    $ret['code'] = 200;
                }
            } elseif ($type == 'hash') {
                $key = $oldKey;
                if ($newKey != $oldKey) {
                    $values = $RedisClass->hGetAll($oldKey);
                    if ($RedisClass->hMset($newKey, $values)) {
                        $key = $newKey;
                        $ret['code'] = 200;
                    } else {
                        exit(json_encode($ret));
                    }
                }
                if (!empty($oldField)) { // 判断是否已选择行，如果已选择行
                    if ($newField == $oldField) { // 没有修改field
                        if ($RedisClass->hSet($key, $oldField, $newValue) !== false) {
                            $ret['code'] = 200;
                        }
                    } else {
                        if ($RedisClass->hExists($key, $newField)) {
                            $ret['msg'] = '已有重复Field';
                        } else {
                            if ($RedisClass->hSet($key, $newField, $newValue) !== false) {
                                $RedisClass->hdel($key, $oldField);
                                $ret['code'] = 200;
                            }
                        }
                    }
                } else {
                    $ret['code'] = 200;
                }
            } elseif ($type == 'zset') {
                $key = $oldKey;
                if ($newKey != $oldKey) {
                    $zsetVals = $RedisClass->zRangeByScore($key,0,999999999, array('withscores'=>false, 'limit'=>array(0,999999999)));
                    $zsetScos = $RedisClass->zRangeByScore($key,0,999999999, array('withscores'=>true, 'limit'=>array(0,999999999)));
                    $zCountOld = count($zsetVals);
                    foreach ($zsetVals as $k => $val) {
                        $zVal = $val;
                        $zSco = $zsetScos[$val];
                        $RedisClass->zAdd($newKey, $zSco, $zVal);
                    }
                    if ($RedisClass->zCard($newKey) == $zCountOld) {
                        $key = $newKey;
                        $ret['code'] = 200;
                    } else {
                        $RedisClass->del($newKey);
                        exit(json_encode($ret));
                    }
                }
                if (!empty($oldValue)) { // 判断是否已选择行，如果已选择行
                    if ($RedisClass->zAdd($key, $newScore, $newValue) !== false) {
                        if ($newValue != $oldValue) {
                            $RedisClass->zRem($key, $oldValue);
                        }
                        $ret['code'] = 200;
                    }
                } else {
                    $ret['code'] = 200;
                }
            } elseif ($type == 'set') {
                $key = $oldKey;
                if ($newKey != $oldKey) {
                    $values = $RedisClass->sMembers($key);
                    if ($RedisClass->sAdd($newKey, $values)) {
                        $key = $newKey;
                        $ret['code'] = 200;
                    } else {
                        $RedisClass->del($newKey);
                        exit(json_encode($ret));
                    }
                }
                if (!empty($oldValue)) { // 判断是否已选择行，如果已选择行
                    if ($newValue != $oldValue) {
                        $values = $RedisClass->sMembers($key);
                        if (in_array($newValue, $values)) {
                            $ret['msg'] = '已有重复Value';
                        } else {
                            if ($RedisClass->sAdd($key, $newValue)) {
                                $RedisClass->sRem($key, $oldValue);
                                $ret['code'] = 200;
                            }
                        }
                    } else {
                        $ret['code'] = 200;
                    }
                } else {
                    $ret['code'] = 200;
                }
            } elseif ($type == 'list') {
                $key = $oldKey;
                if ($newKey != $oldKey) {
                    $values = $RedisClass->lRange($key, 0, 999999999);
                    $oldCount = count($values);
                    $ret['values'] = $values;
                    $ret['oldCount'] = $oldCount;
                    foreach ($values as $k => $val) {
                        $RedisClass->rPush($newKey, $val);
                    }
                    $newCount = $RedisClass->lLen($newKey);
                    if ($newCount == $oldCount) {
                        $key = $newKey;
                        $ret['code'] = 200;
                    } else {
                        $RedisClass->del($newKey);
                        exit(json_encode($ret));
                    }
                }
                if (!empty($oldValue)) { // 判断是否已选择行，如果已选择行
                    if ($newValue != $oldValue) {
                        $index = $row - 1;
                        if ($RedisClass->lSet($key, $index, $newValue)) {
                            $ret['code'] = 200;
                        }
                    } else {
                        $ret['code'] = 200;
                    }
                } else {
                    $ret['code'] = 200;
                }
            }

            if ($ret['code'] == 200) {
                if ($expire > 0) {
                    $RedisClass->expire($newKey, $expire);
                }
                if ($newKey != $oldKey) {
                    $RedisClass->del($oldKey);
                }
            }
            
            exit(json_encode($ret));
        }
    }
    if ($Method == 'AddKey') {
        if (isset($_POST['key']) && !empty($_POST['key'])) {
            $type = trim($_POST['type']);
            $key = trim($_POST['key']);
            $value = trim($_POST['value']);
            $field = trim($_POST['field']);
            $expire = intval($_POST['expire']);
            $score = intval($_POST['score']);
            if ($RedisClass->exists($key)) {
                $ret['msg'] = '已有重复Key';
                exit(json_encode($ret));
            }
            if ($type == 'string') {
                if ($RedisClass->set($key, $value)) {
                    $ret['code'] = 200;
                }
            } elseif ($type == 'list') {
                if ($RedisClass->rPush($key, $value)) {
                    $ret['code'] = 200;
                }
            } elseif ($type == 'set') {
                if ($RedisClass->sAdd($key, $value)) {
                    $ret['code'] = 200;
                }
            } elseif ($type == 'zset') {
                if ($RedisClass->zAdd($key, $score, $value)) {
                    $ret['code'] = 200;
                }
            } elseif ($type == 'hash') {
                if ($RedisClass->hSet($key, $field, $value)) {
                    $ret['code'] = 200;
                }
            }
            if ($ret['code'] == 200 && $expire > 0) {
                $RedisClass->expire($key, $expire);
            }

            exit(json_encode($ret));
        }
    }
    if ($Method == 'AddLine') {
        if (isset($_POST['key']) && !empty($_POST['key'])) {
            $type = trim($_POST['type']);
            $key = trim($_POST['key']);
            $value = trim($_POST['value']);
            $field = trim($_POST['field']);
            $score = intval($_POST['score']);
            if ($type == 'hash') {
                if ($RedisClass->hExists($key, $field)) {
                    $ret['msg'] = '已有重复Field';
                } else {
                    if ($RedisClass->hSet($key, $field, $value)) {
                        $ret['code'] = 200;
                    }
                }
            } elseif ($type == 'zset') {
                if ($RedisClass->zAdd($key, $score, $value)) {
                    $ret['code'] = 200;
                } else {
                    $ret['msg'] = '请检查是否有重复的值';
                }
            } elseif ($type == 'set') {
                $values = $RedisClass->sMembers($key);
                if (in_array($value, $values)) {
                    $ret['msg'] = '已有重复Value';
                } else {
                    if ($RedisClass->sAdd($key, $value)) {
                        $ret['code'] = 200;
                    }
                }
            } elseif ($type == 'list') {
                if ($RedisClass->rPushx($key, $value)) {
                    $ret['code'] = 200;
                }
            }

            exit(json_encode($ret));
        }
    }
    if ($Method == 'DelLine') {
        if (isset($_POST['key']) && !empty($_POST['key'])) {
            $type = trim($_POST['type']);
            $key = trim($_POST['key']);
            $value = trim($_POST['value']);
            $field = trim($_POST['field']);
            $score = intval($_POST['score']);
            $index = intval($_POST['row']) - 1;
            if ($type == 'hash') {
                if ($RedisClass->hdel($key, $field)) {
                    $ret['code'] = 200;
                    $ret['data']['size'] = $RedisClass->hLen($key);
                }
            } elseif ($type == 'zset') {
                if ($RedisClass->zRem($key, $value)) {
                    $ret['code'] = 200;
                    $ret['data']['size'] = $RedisClass->zCard($key);
                }
            } elseif ($type == 'set') {
                if ($RedisClass->sRem($key, $value)) {
                    $ret['code'] = 200;
                    $ret['data']['size'] = $RedisClass->sCard($key);
                }
            } elseif ($type == 'list') {
                $RedisClass->lSet($key, $index, 'spe_mark_del');
                if ($RedisClass->lRem($key, 'spe_mark_del',  0)) {
                    $ret['code'] = 200;
                    $ret['data']['size'] = $RedisClass->lLen($key);
                }
            }

            exit(json_encode($ret));
        }
    }

}






