jQuery( document ).ready(function() {

	var memberId = "";
	if(Cookies.get("memberId") != "" && Cookies.get("memberId") != null) {
		memberId = Cookies.get("memberId");
	}

	if(Cookies.get("email") != "" && Cookies.get("email") != null) {
		jQuery("#email").val(Cookies.get("email"));
	}

	getTopTen(1, "top_ten_men");
	getTopTen(2, "top_ten_women");
	loadCharityLeaderboard();

	function getTopTen(genderNum, tableId) {
		var requestData = {gender: genderNum};
		jQuery.ajax({
			url: "/forms/lib/proxy.php",
			data: {requrl: urlBase + "GetTopTenData?" + jQuery.param(requestData)},
			contentType: "application/json; charset=utf-8",
			dataType: "json",
			cache: false,
			success: function (data) {
				//console.log(data);
				if (data.errorMessage != "" && data.errorMessage != null) {
					jQuery("#errorMessage").text(data.errorMessage);
					jQuery("#errorShow").show();
				} else if (data.length == 0) {
					jQuery('#' + tableId + ' tr:last').after("<tr><td>Awaiting Scores</a></td><td>&nbsp;</td></tr>");
				} else {
					jQuery.each(data, function() {

						var addClass= "";
						if(this.memberId == memberId) {
							addClass = "class='highlight'";
						}

						jQuery('#' + tableId + ' tr:last').after("<tr " + addClass + "><td><a href=\"/forms/challenge/profile.php?memberId=" + this.memberId + "\">" + this.fullName + "</a></td><td>" + this.total + "</td></tr>");
					});
				}
			}
		});
	}

	function loadCharityLeaderboard() {

		jQuery.ajax({
			url: "/forms/lib/proxy.php",
			data: {requrl: urlBase + "GetCharityLeaderboard?"},
			contentType: "application/json; charset=utf-8",
			dataType: "json",
			cache: false,
			success: function (data) {
				if (data.errorMessage != "" && data.errorMessage != null) {
					jQuery("#charity_graph_area").hide();
					jQuery("#no_data").fadeIn();
				} else if (data.length == 0) {
					jQuery("#charity_graph_area").hide();
					jQuery("#no_data").fadeIn();
				} else {
					jQuery('#charity_graph_area').fadeIn('slow', function() {
						var pos = 1;
						var masterPart = 0;
						var masterScore = 0
						jQuery.each(data, function() {

							switch(this.id) {
								case 1:
									img = '/forms/img/logos/large-vertexii_pant_detail_1_31heroes.jpg';
									break;
								case 2:
									img = '/forms/img/logos/large-vertexii_pant_detail_1_bomf.jpg';
									break;
								case 3:
									img = '/forms/img/logos/large-vertexii_pant_detail_1_nbcf.jpg';
									break;
								case 4:
									img = '/forms/img/logos/large-vertexii_pant_detail_1_our.jpg';
									break;
								case 5:
									img = '/forms/img/logos/large-vertexii_pant_detail_1_smb.jpg';
									break;
							}

							jQuery("#pos_" + pos + "_logo").attr("src", img).prop('alt', this.name).fadeIn('slow');

							var delay = 800 + (pos * 500);
							var min = pos *.1;

							if(pos == 1) {
								masterPart = this.participants * (1 + min);
								masterScore = this.totalScore * (1 + min);
							}
							var obj = {};
							obj.Key = this.participants;
							obj.Value = masterPart;
							loadGauge(obj, "Total Participants", "pos_" + pos + "_part_gauge", delay);

							obj.Key = this.totalScore;
							obj.Value = masterScore
							loadGauge(obj, "Total Score", "pos_" + pos + "_total_gauge", delay);

							jQuery('.charity_pos').fadeIn('slow');

							pos++;
						});
					});

				}
			}
		});
	}

	jQuery("#c3LoginForm").validate( {
		ignore: [],
		rules: {
			email: { required: true, email: true }
		},
	 	submitHandler: function(form) {

			jQuery('#errorShow').hide();
			jQuery('#sectionProcessing').show();

			var email = jQuery("#email").val();
			var challengeId = jQuery("#challengeId").val();

			var requestData = {email: email};
			jQuery.ajax({
				url: "/forms/lib/proxy.php",
				data: {requrl: urlBase + "GetChallengerMemberAccount?" + jQuery.param(requestData)},
				contentType: "application/json; charset=utf-8",
				dataType: "json",
				success: function (data) {

					jQuery('#sectionProcessing').hide();

					//console.log(data);

					if (data.errorMessage != "" && data.errorMessage != null) {
						jQuery("#errorMessage").text(data.errorMessage);
						jQuery("#errorShow").show();
					} else {
						var form = jQuery('<form action="/forms/challenge/score.php" method="post">' +
								'<input type="hidden" name="memberId" value="' + data.dbId + '" />' +
								'<input type="hidden" name="challengeId" value="' + challengeId + '" />' +
								'</form>');

						var date = new Date();
						var minutes = 30;
						date.setTime(date.getTime() + (minutes * 60 * 1000));

						Cookies.set("memberId", data.dbId, { expires: date });
						Cookies.set("email", data.email, { expires: date });

						jQuery('body').append(form);
						jQuery(form).submit();
					}
				}
			})
		}
	});

	function loadGauge(data, title, id, animationTime) {

		var oGauge = new JustGage({
			id: id,
			value: data.Key,
			min: 1,
			max: data.Value,
			reverse: false,
			gaugeWidthScale: 1.4,
			levelColors: [
				"#AAAAAA", "#444444"
			],
			hideMinMax:true,
			pointer: true,
			pointerOptions: {
				toplength: -30,
				bottomlength: 5,
				bottomwidth: 10,
				color: '#000000',
				stroke: '#ffffff',
				stroke_width: 2,
				stroke_linecap: 'round'
			},
			titlePosition: 'below',
			titleFontFamily: 'Eurostile',
			valueFontFamily: 'Eurostile',
			labelFontFamily: 'Eurostile',
			labelFontSize: 24,
			counter: true,
			shadowOpacity: 1,
			shadowSize: 6,
			shadowVerticalOffset: 8,
			label: title,
			humanFriendly:true,
			startAnimationTime: animationTime,
			startAnimationType: "bounce"
		});
	}
	
});
