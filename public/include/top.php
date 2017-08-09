<header>
	<h1><a href='/contact' style='font-size:20px'><img src="/img/logo_slim.png">KidsUp<a/></h1>
	<div class="user">
		<p>
			<?php 
				$show_userinfo_str = '<img src="'.$this->loginedInfo['img'].'" style="width:28px;height:28px;vertical-align:middle"/>&nbsp;&nbsp;';
				if($this->loginedInfo['member_type'] == '1'){
					$show_userinfo_str .= $this->loginedInfo['member_name'];
					$show_userinfo_str .= '&nbsp;&nbsp;|&nbsp;&nbsp;';
					$show_userinfo_str .= $this->loginedInfo['center_name'];
					
				}else if($this->loginedInfo['member_type'] == '2'){
					$show_userinfo_str .= $this->loginedInfo['member_name'];
					$show_userinfo_str .= '&nbsp;&nbsp;|&nbsp;&nbsp;';
					$show_userinfo_str .= $this->loginedInfo['center_name'];
					$show_userinfo_str .= '&nbsp;&nbsp;|&nbsp;&nbsp;';
					$show_userinfo_str .= $this->loginedInfo['class_name'];
				}else{
					$show_userinfo_str .= $this->loginedInfo['kids_name'];
					$show_userinfo_str .= '&nbsp;&nbsp;|&nbsp;&nbsp;';
					$show_userinfo_str .= $this->loginedInfo['center_name'];
					$show_userinfo_str .= '&nbsp;&nbsp;|&nbsp;&nbsp;';
					$show_userinfo_str .= $this->loginedInfo['class_name'];
				}

				echo $show_userinfo_str;
			?>
		</p>
	</div>
</header>