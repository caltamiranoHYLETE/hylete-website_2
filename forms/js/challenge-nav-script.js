jQuery( document ).ready(function() {

	if(Cookies.get("memberId") != "" && Cookies.get("memberId") != null) {
		jQuery("#c3_nav").prepend("<a href=\"/forms/challenge/profile.php?memberId=" + Cookies.get("memberId") + "\">My Scores</a> | ");
	}

});
