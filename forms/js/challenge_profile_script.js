jQuery( document ).ready(function() {

	var memberId = jQuery("#memberId").val();
	loadProfile(memberId);

	function loadProfile(memberId) {
		//we need to load any member data here
		if(memberId != "") {
			var requestData = { memberId: memberId, activeChallenges: "0,1,2,3,4" };
			jQuery.ajax({ url: "/forms/lib/proxy.php",
				data: {requrl: urlBase + "GetChallengerMemberProfile?" + jQuery.param(requestData) },
				contentType: "application/json; charset=utf-8",
				cache: false,
				dataType: "json",
				success: function(data) {
					//console.log(data);
					//var jsonObj = jQuery.parseJSON('[' + data + ']');
					if(data.errorMessage != "" && data.errorMessage != null) {
						jQuery("#loadingMessage").text(data.errorMessage);
						jQuery("#loadingImage").hide();
					} else{
						jQuery("#first_name").text(data.firstName.trim());
						jQuery("#last_name").text(data.lastName.trim());
						jQuery("#age_group").text(data.ageGroup.trim());
						jQuery("#gender").text(getGenderFormal(data.gender));

						if(data.state.trim() != "") {
							jQuery("#location").text(data.state.trim() + ", " + data.country.trim());
						} else{
							jQuery("#location").text(data.country.trim());
						}

						jQuery("#gym_name").text(data.affiliateName);

						jQuery("#edit_ageGroup").val(data.ageGroup.trim());
						jQuery("#edit_gender").val(data.gender);
						jQuery("#edit_state").val(data.state.trim());
						jQuery("#edit_country").val(data.country.trim());
						jQuery("#edit_gymName").val(data.affiliateName);
						jQuery("#edit_memberId").val(data.dbId);

						var img = "";
						switch(data.charityId) {
							case 1:
								jQuery("#about_31heroes").show();
								img = '/forms/img/logos/large-vertexii_pant_detail_1_31heroes.jpg';
								break;
							case 2:
								jQuery("#about_bomf").show();
								img = '/forms/img/logos/large-vertexii_pant_detail_1_bomf.jpg';
								break;
							case 3:
								jQuery("#about_3nbcf").show();
								img = '/forms/img/logos/large-vertexii_pant_detail_1_nbcf.jpg';
								break;
							case 4:
								jQuery("#about_our").show();
								img = '/forms/img/logos/large-vertexii_pant_detail_1_our.jpg';
								break;
							case 5:
								jQuery("#about_smb").show();
								img = '/forms/img/logos/large-vertexii_pant_detail_1_smb.jpg';
								break;
						}

						jQuery("#charity_img").prop('src', img);

						//console.log(data.challengeRanks);
						if(data.challengeRanks[0].overallRank.Key == 0) {
							jQuery("#loading_area").fadeOut(600, function() {
								jQuery("#content_area").fadeIn( function () {
									jQuery("#no_data").fadeIn();
								});
							});
						} else {
							jQuery("#loading_area").fadeOut(600, function() {
								jQuery("#content_area").fadeIn( function () {
									jQuery("#gaugeArea").fadeIn( function() {

										for (var item in data.challengeRanks) {
											var obj = data.challengeRanks[item];
											if(obj.overallRank.Key != 0) {
												var id = "rank_" + obj.challengeId + "_" + item;

												var title = getCircuitTitle(obj.challengeId);

												title += "<span><a href='/forms/challenge/score-view.php?memberId=" + data.dbId + "&challengeId=" + obj.challengeId + "'>View Scores</a></span>"

												if(data.dbId == Cookies.get("memberId")) {
													//title += "<span><a href='/forms/challenge/score.php?memberId=" + data.dbId + "&challengeId=" + obj.challengeId + "'>View and Update Scores</a></span>"
												} else {

												}

												if(obj.challengeId == 0) {
													title = "Overall Rankings";
												}

												//var gaugeCode = "<h2 class='header'>" + title + "</h2>";
												var gaugeCode = "<div class=\"profile_gauges\">";
												gaugeCode += "<h2 class='header'>" + title + "</h2>";
												gaugeCode += "<div id=\"oa_" + id + "\" class=\"gauge\"></div>";
												gaugeCode += "<div id=\"gen_" + id + "\" class=\"gauge\"></div>";
												gaugeCode += "<div id=\"age_" + id + "\" class=\"gauge\"></div>";
												gaugeCode += "<div id=\"agegen_" + id + "\" class=\"gauge\"></div>";
												gaugeCode += "</div>";
												if(obj.challengeId == 0) {
													jQuery( "#gaugeArea" ).append(gaugeCode);
												} else{
													jQuery( "#challengeGaugeArea" ).append(gaugeCode);
												}

											}
										}

										for (var item in data.challengeRanks) {

											var obj = data.challengeRanks[item];
											if(obj.challengeId == 0) {
												var pointerLength = -35;
											} else{
												pointerLength = -30;
											}

											if(obj.overallRank.Key != 0) {
												id = "rank_" + obj.challengeId + "_" + item;
												title = "Circuit Rank";
												if(obj.challengeId == 0) {
													title = "Overall Rank";
												}

												if(obj.challengeId == 0) {
													var overAllLabel = nth(obj.overallRank.Key) + " among " + obj.overallRank.Value + " athletes overall";
													var overGenderLabel = nth(obj.genderRank.Key) + " among " + obj.genderRank.Value + " " + getGenderInformal(data.gender).toLowerCase() + " overall";
													var overAgeLabel = nth(obj.ageGroupRank.Key) + " among " + obj.ageGroupRank.Value + " athletes ages " + data.ageGroup;
													var overGenderAgeLabel = nth(obj.ageGenderRank.Key) + " among " + obj.ageGenderRank.Value + " " + getGenderInformal(data.gender).toLowerCase() + " ages " + data.ageGroup;;
													var hideMinMax = true;
												} else{
													overAllLabel = "";
													overGenderLabel = "";
													overAgeLabel = "";
													overGenderAgeLabel = "";
													hideMinMax = false;
												}

												loadGauge(obj.overallRank, title, overAllLabel, "oa_" + id, 1000, pointerLength, hideMinMax);
												loadGauge(obj.genderRank, getGenderInformal(data.gender), overGenderLabel, "gen_" + id, 1300, pointerLength, hideMinMax);
												loadGauge(obj.ageGroupRank, "Age " + data.ageGroup, overAgeLabel, "age_" + id, 1700, pointerLength, hideMinMax);
												loadGauge(obj.ageGenderRank, getGenderInformal(data.gender) + " " + data.ageGroup, overGenderAgeLabel,"agegen_" + id, 2000, pointerLength, hideMinMax);
											}

										}
									})
								});
							});
						}
					}
				}
			});
		} else {
			jQuery("#loadingMessage").text("No member information was supplied to show a profile. Please go back to the leaderboard and select the member from the list.");
			jQuery("#loadingImage").hide();
		}
	}

	jQuery(".edit_img").click(function() {
		jQuery("#profile_block").fadeOut(function() {
			jQuery("#edit_block").fadeIn();
		});
	});

	jQuery("#edit_form").validate({
		ignore: "",
		rules: {},
		messages: {},
		debug: false,
		errorLabelContainer: "#editErrorNotice",
		wrapper: "",
		submitHandler: function()
		{
			//we need to loop through the product form and validate it
			jQuery('#editErrorNotice').hide();

			var dataString  = jQuery("#edit_form").serialize();
			var requestData = { eventForm: dataString };
			jQuery.ajax({
				url: "/forms/lib/proxy.php",
				data: {requrl: urlBase + "SaveProfileEdit?" + jQuery.param(requestData)},
				contentType: "application/json; charset=utf-8",
				dataType: "json",
				success: function (data) {
					console.log(data);
					if (data.success == 'false') {


					} else {

						jQuery( "#gaugeArea" ).empty();
						jQuery( "#challengeGaugeArea").empty();
						jQuery(".gauge").empty();

						jQuery("#edit_block").fadeOut(function() {
							jQuery("#profile_block").fadeIn();
						});

						loadProfile(memberId);
					}
				}

			});
		}

	});

	function getCircuitTitle(challengeId) {
		switch(challengeId) {
			case 1:
				return "Circuit 1: Magnesium";
				break;
			case 2:
				return "Circuit 2: Cadmium";
				break;
			case 3:
				return "Circuit 3: Titanium";
				break;
			case 4:
				return "Circuit 4: Mercury";
				break;
			default:
				return "";
				break;
		}
	}

	function loadGauge(data, title, label, id, animationTime, pointerLength, hideMinMax) {

		if(data.Key == data.Value) {
			data.Value = data.Value +1 ;
		}
		var oGauge = new JustGage({
			relativeGaugeSize: true,
			hideMinMax: hideMinMax,
			id: id,
			value: data.Key,
			min: 1,
			max: data.Value,
			reverse: true,
			gaugeWidthScale: 1.4,
			levelColors: [
				"#AAAAAA", "#444444"
			],
			pointer: true,
			pointerOptions: {
				toplength: pointerLength,
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
			counter: true,
			shadowOpacity: 1,
			shadowSize: 6,
			shadowVerticalOffset: 8,
			title: title,
			label:label,
			startAnimationTime: animationTime,
			startAnimationType: "bounce"
		});
	}
});
