<script type="text/javascript">
<!--
        window.addEvent('domready', function(){
            //-vertical
            var mySlide = new Fx.Slide('login');
            mySlide.hide();
            $('loginToggle').addEvent('click', function(e){
                e = new Event(e);
                mySlide.toggle();
                e.stop();
            });
            $('loginToggle2').addEvent('click', function(e){
                e = new Event(e);
                mySlide.toggle();
                e.stop();
            });
        });
		function checkTopForm(inputhandler) {
			if(inputhandler.user_password.value.length==0) {
				alert("Blank password not allowed.");
				return false;
			}
			return checkEmail(this.user_email);
		}
-->
</script>
<div id="loginbox" style="font-size:0.9em;position:absolute;left:575px;top:0px;color:#fff;z-index:2;">&nbsp;&nbsp;<a id="loginToggle" href="#" style="text-decoration:none;color:#fff;">Login</a>
<div id="login" style="font-size: 0.75em;margin-top:-15px;margin-left:0px;">
	<form method="post" name="user_toplogin_form" onsubmit="return checkTopForm(this)" action="./+login">
		<div>
			<fieldset>
			<legend style="padding-left:30px; padding-right:30px;" id="loginToggle2"><a href="#" style="text-decoration:none;color:#fff;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></legend>
				<table cellspacing="6">
					<tr>
						<td><label for="user_email"  class="labelrequired">Email</label></td>
						<td><input type="text" name="user_email" id="user_email" class="required" onchange="if(this.length!=0) return checkEmail(this);"/><br /></td>
						<td><a style="cursor:pointer;color:#fff;" onclick="window.location='./+login&subaction=register'"> Sign up </a></td>
					</tr>
					<tr>
						<td><label for="user_password" class="labelrequired">Password</label></td>
						<td><input type="password" name="user_password"  id="user_password"  class="required" /></td>
						<td><input type="submit" value="Login" /> </td>
					</tr>
				</table>
			</fieldset>
		</div>
	</form>
</div>
</div>
