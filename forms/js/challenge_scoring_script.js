
jQuery( document ).ready(function() {

	jQuery('input').keydown( function(e) {
		var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
		if(key == 13) {
			e.preventDefault();
			var inputs = jQuery(this).closest('form').find(':input:visible');
			inputs.eq( inputs.index(this)+ 1 ).focus();
		}
	});

	var memberId = jQuery("#memberId").val();
	var challengeId = jQuery("#challengeId").val();
	//we need to load any member data here
	if(memberId != "") {
		var requestData = { memberId: memberId, challengeId: challengeId };
		jQuery.ajax({ url: "/forms/lib/proxy.php",
			data: {requrl: urlBase + "GetChallengerMemberInfo?" + jQuery.param(requestData) },
			contentType: "application/json; charset=utf-8",
			dataType: "json",
			cache: false,
			success: function(data) {
				//console.log(data);
				//var jsonObj = jQuery.parseJSON('[' + data + ']');
				if(data.errorMessage != "" && data.errorMessage != null) {
					jQuery("#loadingMessage").text(data.errorMessage);
					jQuery("#loadingImage").hide();
				} else{
					jQuery("#first_name").text(data.firstName);
					jQuery("#last_name").text(data.lastName);
					jQuery("#charity_name").text(data.charityName);
					jQuery("#memberId").val(data.dbId);

					if(data.scoring.rawJson != null && data.scoring.rawJson != "") {
						var fieldArray = data.scoring.rawJson.split('&');
						for(var i=0; i < fieldArray.length; i++){
							var valueArray = fieldArray[i].split('=');
							//console.log(valueArray[0] + " " + valueArray[1]);
							if(valueArray[1] == "on") {
								jQuery("input[name=" + valueArray[0] + "]").prop("checked", true);
							} else if(valueArray[1] == "true") {
								jQuery("input[name=" + valueArray[0] + "]").prop("checked", true);
							} else {
								jQuery("input[name=" + valueArray[0] + "]").val(valueArray[1]);
							}
						}

						setTotal();
					}

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

					jQuery("#loading_area").fadeOut(600, function() {
						jQuery("#content_area").fadeIn();
					});
				}
			}
		});
	}

	jQuery('.reps').blur(function() {

		if(jQuery(this).val() != "") {
			if(!jQuery.isNumeric(jQuery(this).val())) {
				jQuery(this).val(jQuery(this).data('maxreps'));
			}
		}
		if(jQuery(this).val() > jQuery(this).data('maxreps')) {
			jQuery(this).val(jQuery(this).data('maxreps'));
		}

		if(jQuery(this).val() < 0) {
			jQuery(this).val(0);
		}

		jQuery(this).parents('table').find('td').find('.alt_exercise').each(function() {
			if(jQuery(this).is(':checked')) {
				setAltReps(jQuery(this));
			}
		});

		setTotal();
	});

	jQuery('.complete_cycle').on('click', function() {

		var loopMax = parseInt(jQuery(this).data('loop'));
		if(jQuery(this).is(':checked')) {

			jQuery(this).parents('table').find('th').find('.complete_cycle').each(function() {

				if(loopMax >= parseInt(jQuery(this).data('loop'))) {
					jQuery(this).prop('checked', true);
				}
			});

			//we need to loop through all the input and set the max reps
			jQuery(this).parents('table').find('td').each(function () {
				jQuery('.reps', this).each(function () {

					if(loopMax >= parseInt(jQuery(this).data('loop'))) {
						jQuery(this).val(jQuery(this).data('maxreps'));
					}
				})
			});

			jQuery(this).parents('table').find('td').find('.alt_exercise').each(function() {
				if(jQuery(this).is(':checked')) {
					setAltReps(jQuery(this));
				}
			});

			setTotal();

		} else {
			jQuery(this).parents('table').find('.complete_cycle').each(function() {
				var currLoop = parseInt(jQuery(this).data('loop'));
				if(currLoop > loopMax) {
					jQuery(this).prop('checked', false);
				}
			});

			//we need to loop through all the input and set the max reps
			jQuery(this).parents('table').find('td').each(function () {
				jQuery('.reps', this).each(function () {
					if(loopMax <= parseInt(jQuery(this).data('loop'))) {
						jQuery(this).val("");
					}
				})
			});
		}
	});

	jQuery("#save_scores").click( function (event) {
		event.preventDefault();
		jQuery("#c3_scoring").submit();
	});

	jQuery("#c3_scoring").validate({
		ignore: "",
		rules: {},
		messages: {},
		debug: false,
		errorLabelContainer: "#scoreErrorNotice",
		wrapper: "li",
		submitHandler: function()
		{
			//we need to loop through the product form and validate it
			jQuery('#scoreErrorNotice').hide();

			var dataString  = jQuery("#c3_scoring").serialize();

			jQuery("#myModal").modal();

			jQuery.ajax({
						type        : 'POST',
						url         : '/forms/challenge/score-process.php',
						data        : dataString ,
						dataType    : 'json',
						encode      : true,
						cache: false,
					})
					.done(function(data) {
						//if we don't have any errors, we will show the customer a return label
						//console.log(data);
						if(data.success == 'false') {
							jQuery('#print-label-area').hide();
							jQuery('#modal-message').html(data.message);
						} else {
							var jsonObj = jQuery.parseJSON('[' + data.SaveChallengeScoreResult + ']');

							console.log(jsonObj[0])
							if(jsonObj[0].errorMessage != null && jsonObj[0].errorMessage != "") {
								jQuery('#print-label-area').hide();
								jQuery('#modal-message').html("<h2>There was an error saving your scores!</h2><h4>" + jsonObj[0].errorMessage + "</h4>");
							} else{

								jQuery('#modal-message').html("<h4>Your scores have been saved!</h4>");
								jQuery('#thank-you-message').show();

								jQuery('#overall_gauge').empty();
								jQuery('#agegroup_gauge').empty();
								jQuery('#gender_gauge').empty();
								jQuery('#genderage_gauge').empty();

								var obj = jsonObj[0].challengeRanks[0];
								var overAllLabel = nth(obj.overallRank.Key) + " among " + obj.overallRank.Value + " athletes overall";
								var overGenderLabel = nth(obj.genderRank.Key) + " among " + obj.genderRank.Value + " " + getGenderInformal(jsonObj[0].gender).toLowerCase() + " overall";
								var overAgeLabel = nth(obj.ageGroupRank.Key) + " among " + obj.ageGroupRank.Value + " athletes ages " + jsonObj[0].ageGroup;
								var overGenderAgeLabel = nth(obj.ageGenderRank.Key) + " among " + obj.ageGenderRank.Value + " " + getGenderInformal(jsonObj[0].gender).toLowerCase() + " ages " + data.ageGroup;;
								var hideMinMax = true;

								jQuery("#gaugeArea").fadeIn('slow', function() {
									loadGauge(obj.overallRank, "Circuit Rank",overAllLabel, "overall_gauge", "1000");
									loadGauge(obj.genderRank, getGenderInformal(jsonObj[0].gender), overGenderLabel, "gender_gauge", "1400");
									loadGauge(obj.ageGroupRank, "Age " + jsonObj[0].ageGroup, overAgeLabel, "agegroup_gauge", "1200");
									loadGauge(obj.ageGenderRank, getGenderInformal(jsonObj[0].gender) + " " + jsonObj[0].ageGroup, overGenderAgeLabel, "genderage_gauge", "1600");
								});

							}
						}

					});
		}
	});

	jQuery("#clear_all").click(function(event) {
		event.preventDefault();
		jQuery(this).closest('form').find('.reps').val("");
		jQuery(this).closest('form').find('.complete_cycle').each(function() {
			jQuery(this).prop('checked', false);
		});
		setTotal();
	});

	jQuery('.alt_exercise').on('click', function() {
		setAltReps(jQuery(this));
		setTotal();
	});

	function loadGauge(data, title, label, id, animationTime) {

		if(data.Key == data.Value) {
			data.Value = data.Value +1 ;
		}
		var oGauge = new JustGage({
			hideMinMax: true,
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
				toplength: -40,
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
			counter: true,
			shadowOpacity: 1,
			shadowSize: 6,
			shadowVerticalOffset: 8,
			label: label,
			title: title,
			startAnimationTime: animationTime,
			startAnimationType: "bounce"
		});
	}

	function setTotal() {
		var total = 0;
		jQuery('.container').find('.reps').each(function() {
			//console.log(jQuery(this).val());
			if(jQuery(this).val() != '') {
				total = total + parseInt(jQuery(this).val());
			}
		});
		jQuery('#final_score').text(total);
		jQuery('#total').val(total);
	}

	function setAltReps(obj) {
		var currentEx = jQuery(obj).data('exercise');
		var isChecked = jQuery(obj).prop('checked');

		jQuery(obj).parents('table').find('td').find('.alt_exercise').each(function() {
			if(currentEx == jQuery(this).data('exercise')) {
				jQuery(this).prop('checked', isChecked);
			}
		});

		jQuery(obj).parents('table').find('td').each(function () {
			jQuery('.reps', this).each(function () {
				if(currentEx == parseInt(jQuery(this).data('exercise'))) {
					if(jQuery(this).val() != '') {
						if(isChecked) {
							jQuery(this).val(jQuery(this).data('altreps'));
						} else{
							jQuery(this).val(jQuery(this).data('maxreps'));
						}

					}
				}
			})
		})
	}

});
