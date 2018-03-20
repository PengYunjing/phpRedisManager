var ApiUrl = './Api.php';

var Compare = function(field1, field2, sort){
    return function(a, b){
        var value1 = a[field1];
        var value2 = b[field1];
        if (value2 < value1) {
            if (sort == 'desc' || sort == 'DESC') return -1;
            return 1;
        } else if (value2 > value1) {
            if (sort == 'desc' || sort == 'DESC') return 1;
            return -1;
        } else {
            return 0;
        }
    }
};

function Refresh() {
    if (window.RefTimer) {
        clearTimeout(window.RefTimer);
        window.RefTimer = null;
        return;
    }
    $('#optBar .ref_btn').addClass('rotate');
    window.RefTimer = setTimeout(function(){
        $('#optBar .ref_btn').removeClass('rotate');
        window.RefTimer = null;
    },1500);
    $('#searchInput').val('');
    LoadData();
}

function RefKey() {
    if (window.RefTimer) {
        clearTimeout(window.RefTimer);
        window.RefTimer = null;
        return;
    }
    $('#detailBox .content_box .top_opt_row .ref_btn').addClass('rotate');
    window.RefTimer = setTimeout(function(){
        $('#detailBox .content_box .top_opt_row .ref_btn').removeClass('rotate');
        window.RefTimer = null;
    },1500);
    var key = $('#detailBox .key_detail_box .window_title').text();
    GetKeyDetail(key);
}

function OpenWbox(title) {
    if (window.WboxTimer) {
        clearTimeout(window.WboxTimer);
    }
    $('#windowBox .children_box').hide();
    $('#windowBox .win_tile').text(title);
    $('#windowBox .content_box').removeClass('zoomOut');
    $('#windowBox,#windowMask').show();
}

function OpenAddNewKey() {
    OpenWbox('添加新Key');
    $('#windowBox .add_new_key_box .key').val('');
    $('#windowBox .add_new_key_box .value').val('');
    $('#windowBox .add_new_key_box .field').val('');
    $('#windowBox .add_new_key_box .expire').val('-1');
    $('#windowBox .add_new_key_box .score').val('0');
    $('#windowBox .add_new_key_box').show();
}

function OpenAddLine() {
    OpenWbox('插入行');
    $('#windowBox .add_line_box .score').val('');
    $('#windowBox .add_line_box .field').val('');
    $('#windowBox .add_line_box .value').val('');
    $('#windowBox .add_line_box .value').attr('class', 'value');
    $('#windowBox .add_line_box .field_row,#windowBox .add_line_box .score_row').hide();
    var Type = $('#detailBox .content_box .type').text();
    if (Type == 'hash') {
        $('#windowBox .add_line_box .field_row').show();
        $('#windowBox .add_line_box .value').addClass('t_hash');
    }
    else if (Type == 'zset') {
        $('#windowBox .add_line_box .score_row').show();
        $('#windowBox .add_line_box .value').addClass('t_zset');
    }
    $('#windowBox .add_line_box').show();
}

function OpenLink() {
    $('#windowBox .add_link_box,#windowBox .link_box .edit_box').hide();
    $('#windowBox .link_box .list').html('');
    OpenWbox('连接到 Redis 服务器');
    $.ajax({
        url: ApiUrl,
        type: 'post',
        dataType: 'json',
        data: {
            method: 'GetLinks'
        },
        success: function (res) {
            if (res.code == 200) {
                CreateLinkList(res.data);
            } else {
                if (res.msg) {
                    Msg(res.msg);
                } else {
                    Msg('加载失败');
                }
            }
        },
        error: function () {
            Msg('系统出错');
        }
    });
    $('#windowBox .link_box').show();
}

function OpenAddLink() {
    $('#windowBox .add_link_box .form_box input').val('');
    $('#windowBox .add_link_box').slideDown();
}

function OpenKeyDetail(key) {
    GetKeyDetail(key);
}

function CreateTable(data) {
    var type = $('#detailBox .content_box .type').text();
    var html = '';
    for (var i=0; i<data.length; i++) {
        var item = data[i];
        html += '<div class="tr" onclick="SelectLine(this)">';
        html += '   <span class="td td_row">'+item.row+'</span>';
        if (type == 'hash') {
            html += '   <span class="td td_field">'+item.field+'</span>';
            html += '   <span class="td td_value">'+item.value+'</span>';
        } else if (type == 'zset') {
            html += '   <span class="td td_score">'+item.score+'</span>';
            html += '   <span class="td td_value">'+item.value+'</span>';
        } else {
            html += '   <span class="td td_value">'+item.value+'</span>';
        }
        html += '</div>';
    }
    if (type == 'hash') {
        $('#detailBox .content_box .t_hash_box .tbody').html(html);
    } else if (type == 'zset') {
        $('#detailBox .content_box .t_zset_box .tbody').html(html);
    } else {
        $('#detailBox .content_box .t_other_box .tbody').html(html);
    }
}

function CreateLinkList(data) {
    window.LinksData = data;
    var online = data.online;
    var html = '';
    for (var i=0; i<data.list.length; i++) {
        var item = data.list[i];
        var on = '';
        if (item.host == online.host && item.port == online.port) {
            on = 'on';
        }
        html += '<li onclick="SelectLink(this)" data-index="'+ i +'"><span class="host">'+ item.host +':'+ item.port +'</span><i class="'+ on +' fa fa fa-check"></i></li>';
    }
    $('#windowBox .link_box .list').html(html);
}

function CloseWbox() {
    $('#windowBox .content_box').addClass('zoomOut');
    $('#windowMask').hide();
    window.WboxTimer = setTimeout(function(){
        $('#windowBox,#windowBox .children_box').hide();
    },1000);
}

function CloseAddLink() {
    $('#windowBox .add_link_box').slideUp();
}

function CloseDetail() {
    $('#detailBox .key_detail_box').hide();
    ClearTtlTimer();
}

function ClearTtlTimer() {
    window.clearInterval(TtlTimer);
}

function TtlFocus() {
    IsShowTtlRun = false;
}

function TtlBlur() {
    IsShowTtlRun = true;
}

function TtlRun() {
    if (Ttl > 0) {
        Ttl = Ttl - 1;
        if (IsShowTtlRun == true) {
            $('#detailBox .content_box .edit_ttl').val(Ttl);
        }
    } else {
        ClearTtlTimer();
    }
}

function SearchKey() {
    LoadData();
}

function SearchKeywords(keywords) {
    if (keywords.length == 0) {
        CreateTable(window.TableData);
        return;
    }
    var type = $('#detailBox .content_box .type').text();
    var arr = new Array();
    for (var i=0; i<window.TableData.length; i++) {
        var item = window.TableData[i];
        if (type == 'hash') {
            if (item.field.indexOf(keywords) != -1) {
                arr.push(item);
            }
        } else {
            if (item.value.indexOf(keywords) != -1) {
                arr.push(item);
            }
        }
    }
    CreateTable(arr);
}

function SelectDb(els) {
    $(els).parent().siblings().find('.sel').hide();
    $(els).find('.sel').show();
    var db = $(els).attr('data-db');
    window.CurrentDb = db;
    window.OpenDb = 1;
    $(els).siblings().slideToggle();
    $(els).parent().siblings().find('.keysList').slideUp();
}

function SelectLine(els) {
    $(els).addClass('select').siblings().removeClass('select');
    var val = $(els).find('.td_value').text();
    $('#detailBox .edit_value').val(val);
    var Type = $('#detailBox .content_box .type').text();
    if (Type == 'hash') {
        var field = $(els).find('.td_field').text();
        $('#detailBox .edit_field').val(field);
    } else if (Type == 'zset') {
        var score = $(els).find('.td_score').text();
        $('#detailBox .edit_score').val(score);
    }
}

function SelectLink(els) {
    $(els).addClass('sel').siblings().removeClass('sel');
    var index = $(els).attr('data-index');
    var item = window.LinksData.list[index];
    $('#windowBox .link_box .edit_box .host').val(item.host);
    $('#windowBox .link_box .edit_box .port').val(item.port);
    $('#windowBox .link_box .edit_box .auth').val(item.auth);
    $('#windowBox .link_box .edit_box').show();
    if ($(els).find('.fa').hasClass('on')) {
        $('#windowBox .link_box .list_box .btn_row .minus').hide();
    } else {
        $('#windowBox .link_box .list_box .btn_row .minus').show();
    }
}

function ChangeAddKeyType() {
    var type = $.trim($('#windowBox .add_new_key_box .type').val());
    if (type == 'zset') {
        $('#windowBox .add_new_key_box .score_row').show();
        $('#windowBox .add_new_key_box .value').addClass('t_zset');
    } else {
        $('#windowBox .add_new_key_box .score_row').hide();
        $('#windowBox .add_new_key_box .value').removeClass('t_zset');
    }
    if (type == 'hash') {
        $('#windowBox .add_new_key_box .field_row').show();
        $('#windowBox .add_new_key_box .value').addClass('t_field');
    } else {
        $('#windowBox .add_new_key_box .field_row').hide();
        $('#windowBox .add_new_key_box .value').removeClass('t_field');
    }
}

function ChangeLink() {
    var index = $('#windowBox .list_box li.sel').attr('data-index');
    if (index) {
        var host = $.trim($('#windowBox .link_box .edit_box .host').val());
        var port = $.trim($('#windowBox .link_box .edit_box .port').val());
        var auth = $.trim($('#windowBox .link_box .edit_box .auth').val());
        if (host.length == 0) {
            Msg('请输入host');
            return;
        }
        if (host.length == 0) {
            Msg('请输入port');
            return;
        }

        $.ajax({
            url: ApiUrl,
            type: 'post',
            dataType: 'json',
            data: {
                method: 'ChangeLink',
                index: index,
                host: host,
                port: port,
                auth: auth
            },
            success: function (res) {
                if (res.code == 200) {
                    location.reload();
                } else {
                    if (res.msg) {
                        Msg(res.msg);
                    } else {
                        Msg('连接失败');
                    }
                }
            },
            error: function () {
                Msg('系统出错');
            }
        });
    } else {
        Msg('请先选择一项');
        return;
    }
}

function GetKeyDetail(key) {
    $.ajax({
        url: ApiUrl,
        type: 'post',
        dataType: 'json',
        data: {
            method: 'GetKeyDetail',
            db: window.CurrentDb,
            key: key
        },
        success: function(res) {
            if (res.code == 200) {
                Ttl = res.data.ttl;
                var Type = res.data.type;
                var Value = res.data.value;
                window.TableData = new Array();
                $('#detailBox .key_detail_box .window_title').text($.trim(key));
                $('#detailBox .content_box .edit_key').val($.trim(key));
                $('#detailBox .content_box .edit_ttl').val(Ttl);
                $('#detailBox .content_box .type').text(Type);
                $('#detailBox .content_box .edit_value').val('');
                $('#detailBox .content_box .edit_field').val('');
                $('#detailBox .content_box .edit_score').val('');
                $('#detailBox .keywords').val('');
                $('#detailBox .content_box .table_box').hide();
                $('#detailBox .content_box .field_row').hide();
                $('#detailBox .content_box .score_row').hide();
                $('#detailBox .th .asc,#detailBox .th .desc').hide();
                $('#detailBox .tbody .tr').removeClass('select');
                if (Type == 'string') {
                    $('#detailBox .content_box .table_row').hide();
                    $('#detailBox .content_box .size').text(Value.length+' characters');
                    $('#detailBox .value_row .edit_value').val(Value);
                    $('#detailBox .value_row .edit_value').removeClass('t_list')
                } else {
                    $('#detailBox .content_box .size').text(Value.length+' items');
                    var html = '';
                    for (var i=0; i<Value.length; i++) {
                        html += '<div class="tr" onclick="SelectLine(this)">';
                        html += '   <span class="td td_row">'+(i+1)+'</span>';
                        if (Type == 'hash') {
                            html += '   <span class="td td_field">'+Value[i]['field']+'</span>';
                            html += '   <span class="td td_value">'+Value[i]['value']+'</span>';
                            var arr = {row:i+1, field:Value[i]['field'], value:Value[i]['value']};
                            window.TableData.push(arr);
                        } else if (Type == 'zset') {
                            html += '   <span class="td td_score">'+Value[i]['score']+'</span>';
                            html += '   <span class="td td_value">'+Value[i]['value']+'</span>';
                            var arr = {row:i+1, score:Value[i]['score'], value:Value[i]['value']};
                            window.TableData.push(arr);
                        } else {
                            html += '   <span class="td td_value">'+Value[i]+'</span>';
                            var arr = {row:i+1, value:Value[i]};
                            window.TableData.push(arr);
                        }
                        html += '</div>';
                    }

                    if (Type == 'hash') {
                        $('#detailBox .content_box .t_hash_box .tbody').html(html);
                        $('#detailBox .content_box .t_hash_box').show();
                        $('#detailBox .content_box .field_row').show();
                    } else if (Type == 'zset') {
                        $('#detailBox .content_box .t_zset_box .tbody').html(html);
                        $('#detailBox .content_box .t_zset_box').show();
                        $('#detailBox .content_box .score_row').show();
                    } else {
                        $('#detailBox .content_box .t_other_box .tbody').html(html);
                        $('#detailBox .content_box .t_other_box').show();
                    }

                    $('#detailBox .content_box .table_row').show();
                    $('#detailBox .value_row .edit_value').val('');
                    $('#detailBox .value_row .edit_value').addClass('t_list');
                }

                ClearTtlTimer();
                TtlTimer = self.setInterval('TtlRun()',1000);
                $('#detailBox .th_row .asc').show();
                $('#detailBox .key_detail_box').show();
            } else {
                if (res.msg) {
                    Msg(res.msg);
                } else {
                    Msg('加载失败');
                }
            }
        },
        error: function() {
            Msg('系统出错');
        }
    });
}

function EditKey() {
    var type = $('#detailBox .content_box .type').text();
    var expire = $.trim($('#detailBox .content_box .edit_ttl').val());
    var row = parseInt($('#detailBox .tbody .select .td_row').text());
    var old_key = $('#detailBox .window_top_row .window_title').text();
    var old_field = $('#detailBox .tbody .select .td_field').text();
    var old_score = $('#detailBox .tbody .select .td_score').text();
    var old_value = $('#detailBox .tbody .select .td_value').text();
    var new_key = $.trim($('#detailBox .content_box .edit_key').val());
    var new_field = $.trim($('#detailBox .content_box .edit_field').val());
    var new_score = $('#detailBox .content_box .edit_score').val();
    var new_value = $.trim($('#detailBox .content_box .edit_value').val());
    if (new_key.length == 0) {
        Msg('请输入：KEY');
        return;
    }
    if (type == 'hash') {
        if (old_field.length > 0) {
            if (new_field.length == 0) {
                Msg('请输入：Field');
                return;
            }
            if (new_value.length == 0) {
                Msg('请输入：Value');
                return;
            }
        }
    }
    if (type == 'zset') {
        if (old_value.length > 0) {
            if (new_value.length == 0) {
                Msg('请输入：Value');
                return;
            }
        }
    }
    if (type == 'string' && new_value.length == 0) {
        Msg('请输入：Value');
        return;
    }
    $.ajax({
        url: ApiUrl,
        type: 'post',
        dataType: 'json',
        data: {
            method: 'EditKey',
            db: window.CurrentDb,
            type: type,
            expire: expire,
            row: row,
            old_key: old_key,
            old_field: old_field,
            old_score: old_score,
            old_value: old_value,
            new_key: new_key,
            new_field: new_field,
            new_score: new_score,
            new_value: new_value
        },
        success: function(res) {
            if (res.code == 200) {
                Msg('保存成功');
                if (new_key != old_key) {
                    LoadData();
                }
                GetKeyDetail(new_key);
            } else {
                if (res.msg) {
                    Msg(res.msg);
                } else {
                   Msg('保存失败'); 
                }
            }
        },
        error: function() {
            Msg('系统出错');
        }
    });
}

function AddKey() {
    var type = $.trim($('#windowBox .add_new_key_box .type').val());
    var expire = $.trim($('#windowBox .add_new_key_box .expire').val());
    var key = $.trim($('#windowBox .add_new_key_box .key').val());
    var value = $.trim($('#windowBox .add_new_key_box .value').val());
    var field = $.trim($('#windowBox .add_new_key_box .field').val());
    var score = $('#windowBox .add_new_key_box .score').val();

    if (key.length == 0) {
        Msg('请输入：Key');
        return;
    }
    if (value.length == 0) {
        Msg('请输入：Value');
        return;
    }

    $.ajax({
        url: ApiUrl,
        type: 'post',
        dataType: 'json',
        data: {
            method: 'AddKey',
            db: window.CurrentDb,
            key: key,
            value: value,
            type: type,
            expire: expire,
            score: score,
            field: field
        },
        success: function(res) {
            if (res.code == 200) {
                Msg('添加成功');
                CloseWbox();
                LoadData();
            } else {
                if (res.msg) {
                    Msg(res.msg);
                } else {
                    Msg('添加失败');
                }
            }
        },
        error: function() {
            Msg('系统出错');
        }
    });
}

function AddLine() {
    var key = $('#detailBox .key_detail_box .window_title').text();
    var type = $('#detailBox .content_box .type').text();
    var field = $.trim($('#windowBox .add_line_box .field').val());
    var value = $.trim($('#windowBox .add_line_box .value').val());
    var score = $('#windowBox .add_line_box .score').val();

    if (type == 'hash' && field.length == 0) {
        Msg('请输入：Field');
        return;
    }
    if (type == 'zset' && score.length == 0) {
        Msg('请输入：Score');
        return;
    }
    if (value.length == 0) {
        Msg('请输入：Value');
        return;
    }

    $.ajax({
        url: ApiUrl,
        type: 'post',
        dataType: 'json',
        data: {
            method: 'AddLine',
            db: window.CurrentDb,
            key: key,
            value: value,
            type: type,
            score: score,
            field: field
        },
        success: function(res) {
            if (res.code == 200) {
                Msg('添加成功');
                CloseWbox();
                GetKeyDetail(key);
            } else {
                if (res.msg) {
                    Msg(res.msg);
                } else {
                    Msg('添加失败');
                }
            }
        },
        error: function() {
            Msg('系统出错');
        }
    });
}

function AddLink() {
    var host = $.trim($('#windowBox .add_link_box .form_box .host').val());
    var port = $.trim($('#windowBox .add_link_box .form_box .port').val());
    var auth = $.trim($('#windowBox .add_link_box .form_box .auth').val());
    if (host.length == 0) {
        host = '127.0.0.1';
    }
    if (port.length == 0) {
        port = '6379';
    }
    $.ajax({
        url: ApiUrl,
        type: 'post',
        dataType: 'json',
        data: {
            method: 'AddLink',
            host: host,
            port: port,
            auth: auth
        },
        success: function (res) {
            if (res.code == 200) {
                location.reload();
            } else {
                if (res.msg) {
                    Msg(res.msg);
                } else {
                    Msg('添加失败');
                }
            }
        },
        error: function () {
            Msg('系统出错');
        }
    });
}

function DelKey() {
    CloseDetail();
    var key = $('#detailBox .key_detail_box .window_title').text();
    $.ajax({
        url: ApiUrl,
        type: 'post',
        dataType: 'json',
        data: {
            method: 'DelKey',
            db: window.CurrentDb,
            key: key
        },
        success: function(res) {
            if (res.code == 200) {
                Msg('删除成功');
                LoadData();
            } else {
                Msg('删除失败');
            }
        },
        error: function() {
            Msg('系统出错');
        }
    });
}

function DelLine() {
    var key = $('#detailBox .key_detail_box .window_title').text();
    var type = $('#detailBox .content_box .type').text();
    var value = $('#detailBox .tbody .select .td_value').text();
    var field = $('#detailBox .tbody .select .td_field').text();
    var score = $('#detailBox .tbody .select .td_score').text();
    var row = $('#detailBox .tbody .select .td_row').text();
    if (row.length == 0) {
        Msg('请选择要删除的行');
        return;
    } else {
        row = parseInt(row);
    }

    $.ajax({
        url: ApiUrl,
        type: 'post',
        dataType: 'json',
        data: {
            method: 'DelLine',
            db: window.CurrentDb,
            type: type,
            key: key,
            value: value,
            field: field,
            score: score,
            row: row
        },
        success: function(res) {
            if (res.code == 200) {
                Msg('删除成功');
                if (res.data.size > 0) {
                    GetKeyDetail(key);
                } else {
                    CloseDetail();
                    LoadData();
                }
            } else {
                Msg('删除失败');
            }
        },
        error: function() {
            Msg('系统出错');
        }
    });
}

function DelLink(els) {
    var index = $('#windowBox .link_box .list_box li.sel').attr('data-index');
    if (index) {
        $.ajax({
            url: ApiUrl,
            type: 'post',
            dataType: 'json',
            data: {
                method: 'DelLink',
                index: index
            },
            success: function (res) {
                if (res.code == 200) {
                    CreateLinkList(res.data);
                } else {
                    if (res.msg) {
                        Msg(res.msg);
                    } else {
                        Msg('删除失败');
                    }
                }
            },
            error: function () {
                Msg('系统出错');
            }
        });
    } else {
        Msg('请先选择一项');
        return;
    }
}

function LoadData() {
    $('#keysListBox').html('');
    $.ajax({
        url: ApiUrl,
        type: 'post',
        dataType: 'json',
        data: {
            method: 'LoadData',
            db: window.CurrentDb,
            key: $('#searchInput').val()
        },
        success: function(res) {
            if (res.code == 200) {
                var html = '';
                for (var i=0; i<res.data.length; i++) {
                    var item = res.data[i];
                    html += '<div class="db_box db_'+ i +'">';
                    html += '   <div class="database" onclick="SelectDb(this)" data-db="' + item.db + '"><i class="fa fa-database"></i><span class="dbname">'+ item.dbname +'</span>（<span class="total">'+ item.total +'</span>）<i class="fa fa-check sel"></i></div>';
                    html += '   <ul class="keysList">';
                    for (var j=0; j<item.keys.length; j++) {
                        item_c = item.keys[j];
                        html += '<li onclick="OpenKeyDetail(\'' + item_c + '\')"><i class="fa fa-key"></i><span class="key_name">' + item_c + '</span></li>';
                    }
                    html += '   </ul>';
                    html += '</div>';
                    
                }
                $('#keysListBox').html(html);
                if (window.OpenDb) {
                    $('#keysListBox .db_'+window.CurrentDb+' .keysList').show();
                    $('#keysListBox .db_'+window.CurrentDb).find('.sel').show();
                }

                var infoHtml = '';
                for (var i=0; i<res.info.length; i++) {
                    var item = res.info[i];
                    infoHtml += '<p><span class="text">'+ item.text +'</span>：<span class="val">'+ item.val +'</span></p>';
                }
                $('#infoBox .list').html(infoHtml);
            } else {
                Msg('加载失败');
            }
        },
        error: function() {
            Msg('系统出错');
        }
    });
}

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

function Login(btn) {
    if (window.Logining === true) return;
    var host = $.trim($('#loginBox .host').val());
    var port = $.trim($('#loginBox .port').val());
    var auth = $.trim($('#loginBox .auth').val());
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
            auth: auth
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

function Logout() {
    $.ajax({
        url: ApiUrl,
        type: 'post',
        dataType: 'json',
        data: {
            method: 'Logout'
        },
        success: function(res) {
            if (res.code == 200) {
                location.reload();
            }
        },
        error: function() {
            Msg('系统出错');
        }
    });
}