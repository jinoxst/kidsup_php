$(function(){
    $('#email').focus();
    $('#btn_login').click(function(){
        loginCheck();
    });

    $('#email, #pw').on('keypress', function(e){
        if(e.keyCode == 13){
            loginCheck();     
            return false; // prevent the button click from happening
        }
    });
    $('#issuepwform').click(function(){
        location.href = '/login/issuepwform?email=' + $.trim($('input[type=text][name=email]').val());
    });
});

function loginCheck(){
    var email = $.trim($('#email').val());
    var pw = $('#pw').val();
    if(email == ''){
        $('#email').focus();
        showCommonAlertDialog('注意','Eメールを入力して下さい。');
        return;
    }
    if(isValidEmailAddress(email) === false){
        $('#email').focus();
        showCommonAlertDialog('注意','Eメール形式じゃありません。Eメールを直して下さい。');
        return;
    }
    if(pw == ''){
        $('#pw').focus();
        showCommonAlertDialog('注意','パスワードを入力して下さい。');
        return;
    }
    $.ajax({
        url: '/login/logincheck',
        type: 'GET',
        dataType: 'json',
        data: {
            email:email, 
            pw:pw, 
            id_pw_save:$('#id_pw_save').prop('checked') === true ? '1' : '0'
        },
        async: true,
        beforeSend: function(xhr){
            xhr.setRequestHeader("If-Modified-Since", "Thu, 01 Jun 1970 00:00:00 GMT");
        },
        success: function(d){
            data = d;
            //$('#tmp-work-layer').html(d);
        },
        error: function(xhr, status, e){
            console.log(xhr);
            console.log(status);
            console.log(e);
            showCommonAlertDialog('エラー','システムエラーが発生しました。しばらくしてからやり直して下さい。');
        },
        complete: function(){
            if(data.login_result == 1){
                location.href = '/contact';
            }else{
                $('#email').focus();
                showCommonAlertDialog('注意','Eメール / パスワードを確認して下さい。');
                return;
            }
        }
    });
}

function setCheckBoxValue(v){
    if(v == '1'){
        $('#id_pw_save').prop('checked', true);
    }
}