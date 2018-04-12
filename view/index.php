<?php
/**
 * 首页视图
 */
if (empty($_SESSION['redis_manager'])) {
    exit();
}
if (!isset($GlobalConfig['sys_name'])) {
    exit();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
        <title><?php echo $GlobalConfig['sys_name']; ?></title>
        <link rel="icon" href="img/favicon.ico">
        <link href="./plugins/bootstrap-3.3.7/css/bootstrap.min.css" rel="stylesheet">
        <link href="./plugins/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">
        <link href="./plugins/animate/animate.min.css" rel="stylesheet">
        <link href="./css/style.css?v=<?php echo $GlobalConfig['static_version']; ?>" rel="stylesheet">
        <script src="./plugins/jquery-2.1.4/jquery.min.js"></script>
        <script src="./plugins/bootstrap-3.3.7/js/bootstrap.min.js"></script>
    </head>
    <body >

        <div id="optBar">
            <button id="connectNew" type="button" onclick="OpenLink()"><i class="glyphicon glyphicon-plus"></i> 连接到 Redis 服务器</button>
            <!-- <span class="opt_btn glyphicon glyphicon-cog"></span> -->
            <span class="opt_btn ref_btn glyphicon glyphicon-refresh" onclick="Refresh()"></span>
            <span class="opt_btn add_btn fa fa-plus-circle" onclick="OpenAddNewKey()"></span>
            <span class="logout_btn btn btn-default btn-sm" onclick="Logout()">注销</span>
        </div>

        <div id="keysBox">
            <div id="searchBar">
                <input id="searchInput" type="text" name="key" placeholder="输入Key" spellcheck="false">
                <button id="searchBtn" type="button" onclick="SearchKey()">搜索</button>
            </div>
            <div id="keysListBox"></div>
        </div>

        <div id="infoBox">
            <div class="list">
            </div>
        </div>

        <div id="detailBox">
            <div class="key_detail_box">
                <div class="window_top_row">
                    <div class="r_box"><i class="fa fa-key"></i><span class="window_title"></span><i class="fa fa-times close_detail" onclick="CloseDetail()"></i></div>
                </div>
                <div class="content_box">
                    <div class="top_opt_row">
                        <label class="key_label">Key：</label>
                        <input class="edit_key" type="text" name="edit_key" spellcheck="false">
                        <label class="ttl_label">TTL：</label>
                        <input class="edit_ttl" type="number" name="edit_ttl" onfocus="TtlFocus()" onblur="TtlBlur()">
                        <label class="type_label">Type：</label><span class="type"></span>
                        <span class="ref_btn top_opt_btn fa fa-refresh" onclick="RefKey()"></span>
                        <label class="size_label">Size：</label><span class="size"></span>
                    </div>

                    <div class="table_row">

                        <div class="table_box t_other_box">
                            <div class="thead">
                                <div class="tr">
                                    <span class="th th_row" data-field="row" data-sort="desc">row<i class="asc fa fa-sort-asc"></i><i class="desc fa fa-sort-desc"></i></span>
                                    <span class="th th_value" data-field="value" data-sort="desc">value<i class="asc fa fa-sort-asc"></i><i class="desc fa fa-sort-desc"></i></span>
                                </div>
                            </div>
                            <div class="tbody">
                            </div>
                        </div>

                        <div class="table_box t_hash_box">
                            <div class="thead">
                                <div class="tr">
                                    <span class="th th_row" data-field="row" data-sort="desc">row<i class="asc fa fa-sort-asc"></i><i class="desc fa fa-sort-desc"></i></span>
                                    <span class="th th_field" data-field="field" data-sort="asc">field<i class="asc fa fa-sort-asc"></i><i class="desc fa fa-sort-desc"></i></span>
                                    <span class="th th_value" data-field="value" data-sort="desc">value<i class="asc fa fa-sort-asc"></i><i class="desc fa fa-sort-desc"></i></span>
                                </div>
                            </div>
                            <div class="tbody">
                            </div>
                        </div>

                        <div class="table_box t_zset_box">
                            <div class="thead">
                                <div class="tr">
                                    <span class="th th_row" data-field="row" data-sort="desc">row<i class="asc fa fa-sort-asc"></i><i class="desc fa fa-sort-desc"></i></span>
                                    <span class="th th_score" data-field="score" data-sort="asc">score<i class="asc fa fa-sort-asc"></i><i class="desc fa fa-sort-desc"></i></span>
                                    <span class="th th_value" data-field="value" data-sort="desc">value<i class="asc fa fa-sort-asc"></i><i class="desc fa fa-sort-desc"></i></span>
                                </div>
                            </div>
                            <div class="tbody">
                            </div>
                        </div>

                        <div class="table_opt">
                            <div><button class="insert_line" type="button" onclick="OpenAddLine()"><i class="fa fa-plus-circle"></i>插入行</button></div>
                            <div><button class="delete_line" type="button" onclick="DelLine()"><i class="fa fa-trash"></i>删除行</button></div>
                            <div><input class="keywords" type="text" name="keywords" placeholder="关键词搜索" spellcheck="false"></div>
                        </div>

                    </div>

                    <div class="field_row">
                        <label>Field：</label>
                        <textarea class="edit_field" name="edit_field" spellcheck="false"></textarea>
                    </div>

                    <div class="score_row">
                        <label>Score：</label>
                        <input class="edit_score" type="number" name="edit_score" min="0">
                    </div>

                    <div class="value_row">
                        <label>Value：</label>
                        <textarea class="edit_value" name="edit_value" spellcheck="false"></textarea>
                    </div>

                    <div>
                        <button class="bottom_opt_btn del_btn" type="button" onclick="DelKey()">删除</button>
                        <button class="bottom_opt_btn save_btn" type="button" onclick="EditKey()">保存</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="windowBox">
            <div class="content_box animated zoomIn">
                <div class="top_opt_row"><span class="win_tile">信息</span><i class="fa fa-close close_btn" onclick="CloseWbox()"></i></div>

                <div class="add_new_key_box children_box">
                    <div class="key_row">
                        <p>Key：</p>
                        <input class="key" type="text" name="key" spellcheck="false">
                    </div>
                    <div class="type_row">
                        <p>Type：</p>
                        <select class="type" name="type" onchange="ChangeAddKeyType()">
                            <option value="string">string</option>
                            <option value="list">list</option>
                            <option value="set">set</option>
                            <option value="zset">zset</option>
                            <option value="hash">hash</option>
                        </select>
                    </div>
                    <div class="expire_row">
                        <p>Expire：</p>
                        <input class="expire" type="number" name="expire" value="-1" min="-1">
                    </div>
                    <div class="score_row">
                        <p>Score：</p>
                        <input class="score" type="number" name="score" value="0" min="0">
                    </div>
                    <div class="field_row">
                        <p>Field：</p>
                        <textarea class="field" name="field" spellcheck="false"></textarea>
                    </div>
                    <div class="value_row">
                        <p>Value：</p>
                        <textarea class="value" name="value" spellcheck="false"></textarea>
                    </div>
                    <div class="btn_row">
                        <button type="button" class="cancel" onclick="CloseWbox()">取消</button>
                        <button type="button" class="ok" onclick="AddKey()">保存</button>
                    </div>
                </div>

                <div class="add_line_box children_box">
                    <div class="field_row">
                        <p>Field：</p>
                        <textarea class="field" name="field" spellcheck="false"></textarea>
                    </div>
                    <div class="score_row">
                        <p>Score：</p>
                        <input class="score" type="number" name="score" min="0">
                    </div>
                    <div class="value_row">
                        <p>Value：</p>
                        <textarea class="value" name="value" spellcheck="false"></textarea>
                    </div>
                    <div class="btn_row">
                        <button type="button" class="cancel" onclick="CloseWbox()">取消</button>
                        <button type="button" class="ok" onclick="AddLine()">保存</button>
                    </div>
                </div>

                <div class="link_box children_box">
                    <div class="list_box">
                        <ul class="list">
                        </ul>
                        <div class="btn_row">
                            <i class="plus fa fa-plus" onclick="OpenAddLink()"></i>
                            <i class="minus fa fa-minus" onclick="DelLink()"></i>
                        </div>
                    </div>
                    <div class="edit_box">
                        <p><span class="text">host</span><input class="host" type="text" name="host" spellcheck="false"></p>
                        <p><span class="text">port</span><input class="port" type="text" name="port" spellcheck="false"></p>
                        <p><span class="text">auth</span><input class="auth" type="password" name="auth"></p>
                        <p class="btn_row"><button class="link_btn" type="button" onclick="ChangeLink()">连接</button></p>
                    </div>
                    <div class="add_link_box">
                        <div class="form_box">
                            <h3>添加新的连接</h3>
                            <p><span class="text">host</span><input class="host" type="text" name="host" placeholder="127.0.0.1" spellcheck="false"></p>
                            <p><span class="text">port</span><input class="port" type="text" name="port" placeholder="6379" spellcheck="false"></p>
                            <p><span class="text">auth</span><input class="auth" type="password" name="auth"></p>
                        </div>
                        <div class="btn_row">
                            <button class="cancel_btn" type="button" onclick="CloseAddLink()">取消</button>
                            <button class="add_link_btn" type="button" onclick="AddLink()">添加</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="windowMask"></div>

        <div id="tipBox"><p class="msg animated"></p></div>
    </body>
    <script src="js/common.js?v=<?php echo $GlobalConfig['static_version']; ?>"></script>
    <script src="js/index.js?v=<?php echo $GlobalConfig['static_version']; ?>"></script>
    <script>
        var TtlTimer;
        var Ttl=0;
        var IsShowTtlRun = true;
        window.CurrentDb = 0;
        window.OpenDb = 0;

        $(function () {
            LoadData();

            $('#detailBox .content_box .edit_ttl').focus(function () {
                ClearTtlTimer();
            });

            $('#detailBox .th').click(function () {
                var field = $(this).attr('data-field');
                var sort = $(this).attr('data-sort');
                var data = window.TableData;
                data.sort(Compare(field, 'row', sort));
                CreateTable(data);
                $('#detailBox .th').attr('data-sort', 'asc');
                var new_sort = sort=='asc' ? 'desc' : 'asc';
                $(this).attr('data-sort', new_sort);
                $('#detailBox .th .asc,#detailBox .th .desc').hide();
                $(this).find('.'+sort).show();
                
            });

            $('#detailBox .keywords').bind('input propertychange', function() {
                SearchKeywords($(this).val());
            });

            $('#detailBox .edit_value').bind('input propertychange', function() {
                var type = $('#detailBox .content_box .type').text();
                if (type == 'string') {
                    var value = $(this).val();
                    $('#detailBox .content_box .size').text(value.length+' characters');
                }
            });
        });
    </script>
</html>
