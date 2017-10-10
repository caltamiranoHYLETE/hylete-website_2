
// A jQuery( document ).ready() block.
jQuery( document ).ready(function() {

	jQuery(function() {

		var thread = null;

		jQuery.getJSON( "/forms/freshdesk/freshdesk_knowledgebase.json", function( jsonData ) {
			function findTag(tags) {
				var myTags = tags.split(" ");
				var arrArticles = [];
				for(var i = 0; i < myTags.length; i++)
				{
					var tagToSearch = myTags[i];

					//console.log("JSON DATA: " + jsonData);

					jQuery.each(jsonData, function(index, obj) {
						for(var key in obj.tags) {
							if(obj.tags[key].name == tagToSearch) {
								var article = {title:obj.title, description:obj.description, id:obj.id};

								if(!containsObject(article, arrArticles)) {
									arrArticles.push(article);
								}
							}
						}
					});
				}

				jQuery('#sectionResults').html("").hide();

				//we may or may not have any articles.
				jQuery.each(arrArticles, function(index, obj) {
					//we build up some html to display on the screen
					//console.log(obj);
					jQuery("<div>").html(obj.title).attr({
						'class': "title"
					}).appendTo("#sectionResults");
					jQuery("<div>").html(obj.description).appendTo("#sectionResults");
				});

				jQuery('#sectionResults').show();

			}

			jQuery('#smartSearch').keyup(function() {
				clearTimeout(thread);
				var jQuerythis = jQuery(this); thread = setTimeout(function(){findTag(jQuerythis.val())}, 1000);

			});
		})



	});

	function containsObject(obj, list) {
		var i;
		for (i = 0; i < list.length; i++) {
			if (list[i].id === obj.id) {
				return true;
			}
		}
		return false;
	}
});
