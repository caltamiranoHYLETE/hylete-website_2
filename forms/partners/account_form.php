<!--- Registration Frame Begins -->
<form id="regForm" method="post" autocomplete="off" action="/partners/process.php">
<fieldset>
<div id="registerRightDiv" align="center">
	<table cellpadding="0" cellspacing="0" border="0" align="center" width="100%" id="my_table">   
		<tr><td>
	 		<div id="regPanelDiv"> 
				<div id="my_signup" align="center">create account</div>
				<div style="font-size:12px; color:#ff0000;width:100%" id='err_msg' align="center"></div>
				<div id="registerDiv"></div>
				<div id="registerShowForm" style="margin-top:10px;min-height:300px;">
					<table style="width:100%" align="center">
						<tr>
							<td align="center">
								<input name="txtFirstName" type="text" id="txtFirstName" value="<?php echo $firstName ?>" maxlength="80" required />
							</td>
						</tr>
						<tr><td height="10"></td></tr>
						<tr>
							<td align="center">
								<input name="txtLastName" type="text" id="txtLastName" value="<?php echo $lastName ?>" maxlength="80" required />
							</td>
						</tr>
						<tr><td height="10"></td></tr>
						<tr>
							<td align="center">
								<input name="txtEmail" type="email" id="txtEmail" value="<?php echo $email ?>" maxlength="240" required />
							</td>
						</tr>
						<tr><td height="10"></td></tr>
						<?php if($showReferral == 'true') { ?>
							<tr>
								<td style="font-size:12px;line-height:22px;" align="center">
									Search for the gym, event or organization<br />that referred you from the list below.
									<br /><input type="text" name="autocomplete" id="autocomplete" style="padding: 2px;" required>
									<br>
									<span style="font-size:10px;">(As you type a list of matches will appear.)</span>
									<div style="text-align:left;width:80%;">Verify Referral: <span class="selection" name="autocomplete2" id="autocomplete2"></span></div>
									<input type="hidden" id="autocomplete2value" name="autocomplete2value" required data-msg-required="Please search and select a referral">
									<input type="hidden" id="affiliateName" name="affiliateName">
								</td>
							</tr>
						<tr><td height="10"></td></tr>
						<?php } ?>

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

						<?php if($showGym == 'true') { ?>
						<tr><td height="10"></td></tr>
						<tr>
							<td style="display:block; margin-left:36px; font-size:12px;line-height:22px;">
								Are you a gym owner?
							</td>
						</tr>
						<tr>
							<td style="display:block; margin-left:36px; font-size:12px;line-height:22px;">
								<input type="radio" name="gymOwner" value="true"> Yes
								<input type="radio" name="gymOwner" value="" checked="checked"> No
							</td>
						</tr>
						<tbody id="gymQuestions" style="display:none">
							<tr><td height="10"></td></tr>
							<tr>
								<td style="display:block; margin-left:36px; font-size:12px;line-height:22px;">
									What's the name of your gym?
								</td>
							</tr>
							<tr>
								<td align="center" >
									<input class="ignore" name="gymName" id="gymName" type="text" required>
								</td>
							</tr>

							<tr><td height="10"></td></tr>
							<tr>
								<td style="display:block; margin-left:36px; font-size:12px;line-height:22px;">
									How many members do you have?
								</td>
							</tr>
							<tr>
								<td align="center" >
									<input class="ignore" name="gymMembers" id="gymMembers" type="text" required>
								</td>
							</tr>
							<tr><td height="10"></td></tr>
							<tr>
								<td style="display:block; margin-left:36px; font-size:12px;line-height:22px;">
									What's your phone number?
								</td>
							</tr>
							<tr>
								<td align="center" >
									<input class="ignore" name="gymPhone" id="gymPhone" type="text" required>
								</td>
							</tr>
						</tbody >
						<?php } ?>

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
							<input type="hidden" name="athleteId" id="athleteId" value="<?php echo $athleteId ?>">
							<input type="hidden" name="eventID" id="eventID" value="<?php echo $eventId ?>">
							<input type="hidden" name="eventName" id="eventName" value="<?php echo $eventName ?>">
							<input type="hidden" name="groupID" id="groupID" value="<?php echo $groupId ?>">
							<input type="hidden" name="partnerID" id="partnerID" value="<?php echo $netsuiteId ?>">
							<input type="hidden" name="partnerName" id="partnerName" value="<?php echo $name ?>">
							<input type="hidden" name="redirect" id="redirect" value="<?php echo $redirect ?>">
							<input type="hidden" name="mcList" id="mcList" value="<?php echo $mailChimpList ?>">
							<input type="hidden" name="mcGroupId" id="mcGroupId" value="<?php echo $mailChimpGroupId ?>">
							<input type="hidden" name="mcGroupName" id="mcGroupName" value="<?php echo $mailChimpGroupName ?>">
							<input type="hidden" name="useCoupon" id="useCoupon" value="<?php echo $useCoupon ?>">
							<input type="hidden" name="createCode" id="createCode" value="<?php echo $personalCode ?>">
							<input type="hidden" name="cloneCode" id="cloneCode" value="<?php echo $cloneCode ?>">
							<input type="hidden" name="codePrefix" id="codePrefix" value="<?php echo $codePrefix ?>">
								<input type="hidden" name="codeName" id="codeName" value="<?php echo $codeName ?>">
							<input type="hidden" name="genericCode" id="genericCode" value="<?php echo $genericCode ?>">
						</td></tr>
					</table>
					<div id="errorShow" style="display:none;">
						<table style="width:90%" align="center">
							<tr>
								<td align="center"><label>There was an error creating your account:</label></td>
							</tr>
							<tr>
								<td align="center" id="errorMessage"></td>
							</tr>
							<tr><td height="10"></td></tr>
						</table>
					</div>
				</div>
				
				<div id="couponShow" style="margin-top:10px;min-height:300px;display:none;">
					<table style="width:90%" align="center">
						<tr>
							<td align="center"><b>Copy the promo code</b> below for use on your first purchase*.</td>
						</tr>
						<tr>
							<td align="center" class="couponCode" id="couponCode"></td>
						</tr>
						<tr>
							<td align="center" style="font-size:.8em;">Code expires 60 days from creation<br><br></td>
						</tr>
						<tr>
							<td align="center" style="font-size:.8em;">Please log in to activate your promo code. The promo code is valid for one use on HYLETE.com.
								<br><br>*Orders of $50 or more. Promotional value will not be applied to clearance locker, featured product, NPGL, or charity items present in your cart. Promo code will not activate if an electronic gift card is in your cart.</td>
						</tr>
						<tr><td height="25"></td></tr>
						<tr>
							<td align="center"><a href="#" id="login-to-shop"><img src="/forms/img/shopnow.png"></a></td>
						</tr>
						<tr><td height="25"></td></tr>
					</table>
				</div>
				
				<div id="accountShow" style="margin-top:10px;min-height:300px;display:none;">
					<table style="width:90%" align="center">
						<tr>
							<td align="center">
								<label for="txtEmail" id="email_in_use" style="text-align:center;">
									Our records show you currently have a HYLETE team account. <br><br><a href="/customer/account/login/">Login</a> to view your team pricing.
								</label>
							</td>
						</tr>
						<tr><td height="10"></td></tr>
					</table>
				</div>
				
				<div id="loginButtonShow" style="margin-top:10px;min-height:300px;display:none;">
                    <table style="width:90%" align="center">
                        <tr>
                            <td align="center">Welcome to the #HYLETEnation. Your account has been created.</td>
                        </tr>
                        <tr><td height="25"></td></tr>
                        <tr>
                            <td align="center"><a href="#" id="login-customer"><img src="/forms/img/shopnow.png"></a></td>
                        </tr>
                    </table>
                </div>
                
                <div id="loginLinkShow" style="margin-top:10px;min-height:300px;display:none;">
                    <table style="width:90%" align="center">
                        <tr>
                            <td align="center">Welcome to the #HYLETEnation. Your powered by HYLETE account has been created.</td>
                        </tr>
                        <tr><td height="25"></td></tr>
                        <tr>
                            <td align="center"><a href="/customer/account/login/">Login</a> to view your team pricing.</td>
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