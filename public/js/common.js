var _SEAR_ = 'SESSION_EXPIRED_AJAX_REQUEST';
var _IMAGE_UPLOAD_MAX_FILES_ = 10; //Number of files at a time
var _IMAGE_UPLOAD_MAX_FILE_SIZE_ = 4; //in MB
var _IMAGE_UPLOAD_ACCEPTED_FILES_ = 'image/jpg,image/jpeg,image/png';
var _LOGIN_URL_ = null;
var _HISTORY_SEQ_ = 1;

$(function(){
    $('body').append("<div id='reload-modal' style='display:none;'><img src='/img/ajax-loader.gif'></div>");
    $(document).ajaxStart(function(){
        showProcessDialog();
    }).ajaxStop(function() {
        hideProcessDialog();
    });

    $('#close_common_alert').button({icons:{primary:'ui-icon-circle-close'}});
    $('#close_common_alert').click(function(){
        $('#common_alert').dialog("close");
        if(_LOGIN_URL_){
            location.href = _LOGIN_URL_;
        }
    });

    initFunc();

    $(window).bind("statechange", function(e) {
        var State = window.History.getState();
        if(State.data && State.data.backbutton_target){
            closejQueryDialog();
            if(State.data.leftmenu){
                setSideMenuDefaultColor(State.data.leftmenu);
            }else{
                setSideMenuDefaultColor(State.data.url.split('/')[1]);
            }
            if(State.data.url == '/contact/addform' || State.data.url == '/notice/addform'　|| State.data.url == '/mamatalk/addform'){
                loadImageUploadAjaxPage(State.data, State.data.url);
            }else{
                loadAjaxPage(State.data, State.data.url);
            }
        }
    });
});

function initFunc(){
    $("#one_button_common_alert").dialog({
        autoOpen: true
    });
    $("#one_button_common_alert").dialog('close');

    $("#yes_no_common_alert").dialog({
        autoOpen: true
    });
    $("#yes_no_common_alert").dialog('close');


    jQuery.datepicker.setDefaults(jQuery.datepicker.regional['ja']);
}

function closejQueryDialog(){
    if($("#one_button_common_alert").dialog( 'isOpen' )){
        $("#one_button_common_alert").dialog( 'close' )
    }

    if($("#yes_no_common_alert").dialog( 'isOpen' )){
        $("#yes_no_common_alert").dialog( 'close' )
    }
}

var _PROCESS_DIALOG_ = null;
function showProcessDialog(){
    _PROCESS_DIALOG_ = $("#reload-modal");
    _PROCESS_DIALOG_.dialog({
        modal: true,
        draggable: false,
        closeOnEscape: false,
        resizable: false,
        minWidth: 100,
        minHeight: 100,
        width: 'auto',
        height: 'auto',
        open: function(event, ui) {
            $(".ui-dialog-titlebar", $(this).parent()).hide();
        }
    });
    _PROCESS_DIALOG_.css('padding','0em 0em');
}

function hideProcessDialog(){
    if(_PROCESS_DIALOG_){
        _PROCESS_DIALOG_.dialog('close');
    }
}

function showCommonAlertDialog(title, contents, loginUrl){
    _LOGIN_URL_ = loginUrl;
    $("div#common_alert div#common_alert_contents").html(contents);
    $("#common_alert").dialog({
        title:title,
        modal: true,
        closeOnEscape: false,
        draggable: false,
        resizable: false,
        minWidth: 100,
        minHeight: 0,
        width: 'auto',
        open: function(event, ui) {
            setDialogDesign($(this));
        }
    });
    $("common_alert").css('padding','0em 0em');
}

function setDialogDesign(obj){
    $(".ui-dialog-titlebar-close", obj.parent()).hide();
}

var REGEXP_EMAILADDRESS = /^([a-zA-Z0-9\.\_\-\/]+)@([a-zA-Z0-9\._\-]+)\.([a-zA-Z]+)$/;
function isValidEmailAddress(email){
    return REGEXP_EMAILADDRESS.test(email);
}

function loadAjaxPage(params, url){
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        data: params,
        beforeSend: function(xhr){
            xhr.setRequestHeader("If-Modified-Since", "Thu, 01 Jun 1970 00:00:00 GMT");
        },
        error: function(xhr, status, e){
            console.log(status);
            console.log(e);
            showCommonAlertDialog('エラー','システムエラーが発生しました。しばらくしてからやり直して下さい。');
        },
        success: function(d){
            if(d == _SEAR_){
                showCommonAlertDialog('エラー','セッション終了です。再びログインして下さい。','/login');
            }else{
                $('#contents').html(d);
            }
        },
        complete: function(){}
    });
}

function loadImageUploadAjaxPage(params, url){
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        data: params,
        beforeSend: function(xhr){
            xhr.setRequestHeader("If-Modified-Since", "Thu, 01 Jun 1970 00:00:00 GMT");
        },
        success: function(d){
            if(d == _SEAR_){
                showCommonAlertDialog('エラー','セッション終了です。再びログインして下さい。','/login');
            }else{
                $('#contents').html(d);
            }
        },
        complete: function(){
            Dropzone.autoDiscover = false; // keep this line if you have multiple dropzones in the same page
            $(".uploadform").dropzone({
                acceptedFiles: _IMAGE_UPLOAD_ACCEPTED_FILES_,
                url: '/' + params.action + '/uploadimage',
                maxFiles: _IMAGE_UPLOAD_MAX_FILES_,
                maxFilesize: _IMAGE_UPLOAD_MAX_FILE_SIZE_,
                dictFileTooBig: "ファイルが大きすぎます。({{filesize}}MB) 最大サイズ: {{maxFilesize}}MB",
                dictInvalidFileType: "画像ファイル以外は対応できません。",
                dictMaxFilesExceeded: "一度にアップロード出来るのは" + _IMAGE_UPLOAD_MAX_FILES_ + "ファイルまでです。",
                addRemoveLinks: true,
                sending: function(file, xhr, formData){
                    formData.append('action', params.action);
                },
                dzstart: function(){
                    if($('#' + params.action + '_add').is(':visible') === true || $('#' + params.action + '_add_cancel').is(':visible') === true){
                        $('#' + params.action + '_add').hide();
                        $('#'+ params.action + '_add_cancel').hide();
                    }
                },
                queuecomplete: function(){
                    $('#' + params.action + '_add').show();
                    $('#' + params.action + '_add_cancel').show();
                }
            });
        }
    });
}

function addAlbumDataForAjax(params){
    $("#yes_no_common_alert").html('この写真をアルバムに追加しますか？');
    $("#yes_no_common_alert").dialog({
        title:'確認',
        modal: true,
        draggable: false,
        closeOnEscape: false,
        resizable: false,
        height: 'auto',
        minHeight: 0,
        width: 'auto',
        open: function(event, ui) {
            setDialogDesign($(this));
            $('.ui-dialog-buttonpane').find('button:contains("はい")').button({icons: {primary: 'ui-icon-circle-triangle-e'}});
            $('.ui-dialog-buttonpane').find('button:contains("キャンセル")').button({icons: {primary: 'ui-icon-circle-close'}});
        },
        buttons: {
            "はい": function() {
                $(this).dialog("close");
                $.ajax({
                    url: '/album/add',
                    type: 'GET',
                    dataType: 'json',
                    data: params,
                    beforeSend: function(xhr){
                        xhr.setRequestHeader("If-Modified-Since", "Thu, 01 Jun 1970 00:00:00 GMT");
                    },
                    error: function(xhr, status, e){
                        console.log(xhr);
                        console.log(status);
                        console.log(e);
                        showCommonAlertDialog('エラー','システムエラーが発生しました。しばらくしてからやり直して下さい。');
                    },
                    success: function(d){
                        console.log(d);
                        if(d.status == _SEAR_){
                            showCommonAlertDialog('エラー','セッション終了です。再びログインして下さい。','/login');
                        }else{
                            if(d.status == '0'){
                                showInfoMessage(250,'アルバムに追加しました。',2000,'slow');
                            }else{
                                showCommonAlertDialog('エラー',d.message);
                            }
                        }
                    },
                    complete: function(){}
                });
            },
            "キャンセル": function() {
                $(this).dialog("close");
            }
        }
    });
}

function getNewHistorySeq(){
    return _HISTORY_SEQ_++;
}

function setSideMenuDefaultColor(id){
    $('div#side ul li a#'+id).removeClass('side_menu_default').addClass('side_menu_click');
    $('div#side ul li a').each(function(){
        if(!$(this).attr('id')){
            $(this).remove();
        }
        if($(this).attr('id') && (id != $(this).attr('id'))){
            $(this).removeClass('side_menu_click').addClass('side_menu_default');
        }
    });
}

function showInfoMessage(width, msg, delay, speed){
    var top = $(document).scrollTop() + $(window).height() / 2 - 20;
    var left = $(window).width() / 2 - width / 2;
    $('#info-message #_contents_').html(msg);
    $("#info-message").css({"position":"absolute","top":top+"px","left":left+"px","width":width+"px","font-size":"14px","z-index":"102"}).fadeIn(speed, function(){
        $(this).delay(delay).fadeOut(speed);
    });
}

function showErrorMessage(width, msg, delay, speed){
    var top = $(document).scrollTop() + $(window).height() / 2 - 20;
    var left = $(window).width() / 2 - width / 2;
    $('#error-message #_contents_').html(msg);
    $("#error-message").css({"position":"absolute","top":top+"px","left":left+"px","width":width+"px","font-size":"14px"}).fadeIn(speed, function(){
        $(this).delay(delay).fadeOut(speed);
    });
}

function getDateYYYYMMDD(){
    var date = new Date();
    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    var d = date.getDate();

    if (m < 10) m = "0" + m;
    if (d < 10) d = "0" + d;

    return y + "" + m + "" + d;
}

function getFileNameFromUrl(url){
    return url.substring(url.lastIndexOf('/')+1);
}

function checkBrowser(browser){
    strUA = navigator.userAgent.toLowerCase();
    if(strUA.indexOf(browser) != -1){
        return true;
    }else{
        if(!!navigator.userAgent.match(/Trident.*rv[ :]*11\./)){//ie11 check logic
            return true;
        }else{
            return false;
        }
    }
}