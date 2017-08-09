<style>
#contact {
	background:#CD853F url(/img/icon/btn_main_contact.png) 3px 7px no-repeat;
	background-size:18px;
}
#notice {
	background:#CD853F url(/img/icon/btn_main_notice.png) 3px 7px no-repeat;
	background-size:18px;
}
#event {
	background:#CD853F url(/img/icon/btn_main_event.png) 3px 7px no-repeat;
	background-size:18px;
}
#dailymenu {
	background:#CD853F url(/img/icon/btn_main_dailymenu.png) 3px 7px no-repeat;
	background-size:18px;
}
#album {
	background:#CD853F url(/img/icon/btn_main_album.png) 3px 7px no-repeat;
	background-size:18px;
}
#mamatalk {
	background:#CD853F url(/img/icon/btn_main_mamatalk.png) 3px 7px no-repeat;
	background-size:18px;
}
#schedule {
	background:#CD853F url(/img/icon/btn_main_schedule.png) 3px 7px no-repeat;
	background-size:18px;
}
#approval {
	background:#CD853F url(/img/icon/btn_approval.png) 3px 7px no-repeat;
	background-size:18px;
}
#attendance {
	background:#CD853F url(/img/icon/btn_attendance.png) 3px 7px no-repeat;
	background-size:18px;
}
#cls {
	background:#CD853F url(/img/icon/cls.png) 3px 7px no-repeat;
	background-size:18px;
}
#mngkids {
	background:#CD853F url(/img/icon/kids.png) 3px 7px no-repeat;
	background-size:18px;
}
#mngteacher {
	background:#CD853F url(/img/icon/teacher.png) 3px 7px no-repeat;
	background-size:18px;
}
#invitationcode {
	background:#CD853F url(/img/icon/invitationcode.png) 3px 7px no-repeat;
	background-size:18px;
}
#updatememberinfoform {
	background:#CD853F url(/img/icon/user.png) 3px 7px no-repeat;
	background-size:18px;
}
#chgpwform {
	background:#CD853F url(/img/icon/password.png) 3px 7px no-repeat;
	background-size:18px;
}
#pushreceiveynform {
	background:#CD853F url(/img/icon/push.png) 3px 7px no-repeat;
	background-size:18px;
}
#logout {
	background:#CD853F url(/img/icon/logout.png) 3px 7px no-repeat;
	background-size:18px;
}
#setting {
	background:#CD853F url(/img/icon/btn_main_setting.png) 3px 7px no-repeat;
	background-size:18px;
}
</style>
<script>
$(function(){
	if(!$('div#side > a').attr('id')){
		$('div#side > a').remove();
	}
	if(!$('div#side ul > a').attr('id')){
		$('div#side ul > a').remove();
	}
	$('#contact').click(function(){
		$(this).blur();
		var curY = new Date().getFullYear();
	    var curM = new Date().getMonth() + 1;
	    if(curM < 10){
	        curM = '0' + curM;
	    }
	    var params = {
	        url:'/contact/list',
	        year:curY,
	        month:curM,
	        pageno:1,
	        trigger:'pageno',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq()
	    };
	    window.History.pushState(params, null, '/');
    });

	$('#notice').click(function(){
		$(this).blur();
		var params = {
	        url:'/notice/list',
	        pageno:1,
	        trigger:'pageno',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq()
	    };
	    window.History.pushState(params, null, '/');
	});

	$('#event').click(function(){
		$(this).blur();
    	var params = {
	        url:'/event/list',
	        pageno:1,
	        trigger:'pageno',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq()
	    };
	    window.History.pushState(params, null, '/');
	});

	$('#dailymenu').click(function(){
		$(this).blur();
		var params = {
	        url:'/dailymenu/list',
	        pageno:1,
	        trigger:'pageno',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq()
	    };
	    window.History.pushState(params, null, '/');
	});
	$('#schedule').click(function(){
		$(this).blur();
		var params = {
	        url:'/schedule/list',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq()
	    };
	    window.History.pushState(params, null, '/');
	});
	$('#album').click(function(){
		$(this).blur();
		var params = {
	        url:'/album/list',
	        pageno:1,
	        trigger:'pageno',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq()
	    };
	    window.History.pushState(params, null, '/');
	});
	$('#mamatalk').click(function(){
		$(this).blur();
		var params = {
			class_id:'<?php echo $this->loginedInfo["class_id"];?>',
	        url:'/mamatalk/list',
	        pageno:1,
	        trigger:'pageno',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq()
	    };
	    window.History.pushState(params, null, '/');
	});
	$('#approval').click(function(){
		$(this).blur();
		var params = {
	        url:'/mng/approvallist',
	        pageno:1,
	        trigger:'pageno',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq(),
	        leftmenu:'approval'
	    };
	    window.History.pushState(params, null, '/');
	});
	$('#attendance').click(function(){
		$(this).blur();
		var params = {
	        url:'/mng/attendanceinfo',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq(),
	        leftmenu:'attendance'
	    };
	    window.History.pushState(params, null, '/');
	});
	$('#cls').click(function(){
		$(this).blur();
		var params = {
	        url:'/mng/clslist',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq(),
	        leftmenu:'cls'
	    };
	    window.History.pushState(params, null, '/');
	});
	$('#mngkids').click(function(){
		$(this).blur();
		var params = {
	        url:'/mng/kidslist',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq(),
	        leftmenu:'mngkids'
	    };
	    window.History.pushState(params, null, '/');
	});
	$('#mngteacher').click(function(){
		$(this).blur();
		var params = {
	        url:'/mng/teacherlist',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq(),
	        leftmenu:'mngteacher'
	    };
	    window.History.pushState(params, null, '/');
	});
	$('#invitationcode').click(function(){
		$(this).blur();
		var params = {
	        url:'/mng/invitationcode',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq(),
	        leftmenu:'invitationcode'
	    };
	    window.History.pushState(params, null, '/');
	});
	$('#updatememberinfoform').click(function(){
		$(this).blur();
		var params = {
	        url:'/setting/updatememberinfoform',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq(),
	        leftmenu:'updatememberinfoform'
	    };
	    window.History.pushState(params, null, '/');
	});
	$('#chgpwform').click(function(){
		$(this).blur();
		var params = {
	        url:'/setting/chgpwform',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq(),
	        leftmenu:'chgpwform'
	    };
	    window.History.pushState(params, null, '/');
	});
	$('#pushreceiveynform').click(function(){
		$(this).blur();
		var params = {
	        url:'/setting/pushreceiveynform',
	        backbutton_target:true,
	        history_seq:getNewHistorySeq(),
	        leftmenu:'pushreceiveynform'
	    };
	    window.History.pushState(params, null, '/');
	});
	$('#logout').click(function(){
		$(this).blur();
		$("#yes_no_common_alert").html('ログアウトしますか？');
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
                    location.href = '/login/logout';
                },
                "キャンセル": function() {
                    $(this).dialog("close");
                }
            }
        });
	});
});
</script>
<div id="side">
	<ul>
		<li><a href="javascript:void(0);" id='contact'>連絡帳</a></li>
		<li><a href="javascript:void(0);" id='notice'>お知らせ</a></li>
		<li><a href="javascript:void(0);" id='event'>行事</a></li>
		<li><a href="javascript:void(0);" id='dailymenu'>給食</a></li>
		<li><a href="javascript:void(0);" id='schedule'>スケジュール</a></li>
<?php if($this->loginedInfo['member_type'] == '3'){?>
		<li><a href="javascript:void(0);" id='album'>アルバム</a></li>
		<li><a href="javascript:void(0);" id='mamatalk'>ママトーク</a></li>
<?php }else if($this->loginedInfo['member_type'] == '1'){?>
		<li><a href="javascript:void(0);" id='approval'>未承認</a></li>
		<li><a href="javascript:void(0);" id='attendance'>出席簿</a></li>
		<li><a href="javascript:void(0);" id='cls'>組の管理</a></li>
		<li><a href="javascript:void(0);" id='mngkids'>子供の管理</a></li>
		<li><a href="javascript:void(0);" id='mngteacher'>教師の管理</a></li>
		<li><a href="javascript:void(0);" id='invitationcode'>承認コードの確認</a></li>
<?php }else if($this->loginedInfo['member_type'] == '2'){?>
		<li><a href="javascript:void(0);" id='approval'>未承認</a></li>
		<li><a href="javascript:void(0);" id='attendance'>出席簿</a></li>
		<li><a href="javascript:void(0);" id='mngkids'>子供の管理</a></li>
		<li><a href="javascript:void(0);" id='invitationcode'>承認コードの確認</a></li>		
<?php }?>
		<li><a href="javascript:void(0);" id='updatememberinfoform'>ユーザ情報変更</a></li>
		<li><a href="javascript:void(0);" id='chgpwform'>パスワード変更</a></li>
		<li><a href="javascript:void(0);" id='pushreceiveynform'>プッシュ通知設定</a></li>
		<li><a href="javascript:void(0);" id='logout'>ログアウト</a></li>
	</ul>
</div>