<!--- Registration Frame Begins -->
<form id="regForm" method="post" autocomplete="off" action="process.php">
<fieldset>
<div id="registerRightDiv" align="center">
	<table cellpadding="0" cellspacing="0" border="0" align="center" width="100%" id="my_table">   
		<tr><td>
	 		<div id="regPanelDiv"> 
				<div id="my_signup" align="center">create account</div>
				<div style="font-size:12px; color:#ff0000;width:100%" id='err_msg' align="center"></div>
				<div id="registerDiv"></div>
				<div id="registerShowForm" style="margin-top:10px;min-height:300px;">
					<table style="width:100%" align="center" id="account-form">
						<tr>
							<td align="center">
								<input name="txtFirstName" type="text" id="txtFirstName" value="first name" maxlength="80" required />
							</td>
						</tr>
						<tr><td height="10"></td></tr>
						<tr>
							<td align="center">
								<input name="txtLastName" type="text" id="txtLastName" value="last name" maxlength="80" required />
							</td>
						</tr>
						<tr><td height="10"></td></tr>
						<tr>
							<td align="center">
								<input name="txtEmail" type="email" id="txtEmail" value="email address" maxlength="240" required />
							</td>
						</tr>
						<tr><td height="10"></td></tr>
						<tr>
							<td align="center">
								<input name="defaultPassword" id="defaultPassword" value="password" type="text" required >
							</td>
						</tr>
						<tr><td height="10"></td></tr>
						<tr>
							<td align="center" >
								<input  name="defaultRePassword" id="defaultRePassword" value="confirm password" type="text" data-rule-equalto="input[name=defaultPassword]" data-msg-required="Please make sure your passwords match" data-msg-equalto="Passwords do not match" required > 
							</td>
						</tr>
						<tr><td height="10"></td></tr>
						<tr>
							<td align="center" id="sectionRegister">
								<div id="sectionProcessing" align="center" style="display: none;"><label for="form_submit">Creating account, please wait...</label><br />
								<img src="/forms/img/ajax-loader.gif"  border="0" />
								</div>
								<br/>
								<input type="submit" value="Create Account" id="form_submit" class="button">
							</td>
						</tr>
						<tr><td height="20">
							<input type="hidden" name="mcList" id="mcList" value="">
							<input type="hidden" name="mcGroupId" id="mcGroupId" value="">
							<input type="hidden" name="mcGroupName" id="mcGroupName" value="">
						</td></tr>
					</table>
					<div id="errorShow" style="display:none;">
						<table style="width:90%" align="center">
							<tr>
								<td align="center" id="errorMessage"></td>
							</tr>
							<tr><td height="10"></td></tr>
						</table>
					</div>
				</div>

				<div id="accountShow" style="margin-top:10px;min-height:300px;display:none;">
					<table style="width:90%" align="center">
						<tr>
							<td align="center">
								<label for="txtEmail" id="email_in_use" style="text-align:center;">
									Our records show you currently have a HYLETE account. We have upgraded this account but you'll need to use your previous password to log in. <br><br><a href="/customer/account/login/">Login</a> to view your Pro Deal pricing..
								</label>
							</td>
						</tr>
						<tr><td height="10"></td></tr>
					</table>
				</div>
				
				<div id="newAccountShow" style="margin-top:10px;min-height:300px;display:none;">
                    <table style="width:90%" align="center">
                        <tr>
                            <td align="center">Welcome to the HYLETE Pro Deal Program. Your account has been created.</td>
                        </tr>
                        <tr><td height="25"></td></tr>
                        <tr>
                            <td align="center"><a href="#" id="login-customer"><img src="/forms/img/shopnow.png"></a></td>
                        </tr>
                    </table>
                </div>
                

			</td>
		</tr>
	</table>
</div>
</fieldset>
</form>
	<!--Signup panel ends-->