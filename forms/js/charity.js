

jQuery( document ).ready(function() {
	//var urlBase = "http://localhost:60601/hyletePBHService.asmx/";
	var urlBase =  "https://pbhservice.hylete.com/hyletePBHService.asmx/";
	function getCharityTotal(fieldId, charityId) {
		var requestData = { charityId: charityId };
		jQuery.ajax({ url: "/forms/lib/proxy.php",
			data: {requrl: urlBase + "GetCharityTotal?" + jQuery.param(requestData) },
			contentType: "application/json; charset=utf-8",
			dataType: "json",
			cache: false,
			success: function(data) {
				console.log(data);
				var title = jQuery("#charity_total").data("title");
				var label = jQuery("#charity_total").data("label");
				var width = jQuery("#charity_total").data("width");

				var percent = (data.totalRaised / data.goal) * 100;

				loadChart(Math.ceil(percent), width);
				//loadGauge(data.totalRaised, data.goal, title, label, fieldId, "1000");
			}
		});
	}

	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}

	function loadChart(percent, width) {

		jQuery("#charity_total").progressBar( {
			percent: percent,
			width: width,
			height:20,
			showPercent: false
		});

		jQuery("#charity_total_text").html(percent + "% to goal" );
	}

	function loadGauge(min, max, title, label, id, animationTime) {

		var oGauge = new JustGage({
			hideMinMax: true,
			id: id,
			value: min,
			min: 1,
			max: max,
			reverse: false,
			gaugeWidthScale: 1.4,
			humanFriendly: true,
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

	var charityId = jQuery("#charity_total").data("id");
	getCharityTotal("charity_total", charityId);

});


