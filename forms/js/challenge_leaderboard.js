jQuery( document ).ready(function() {

    var db = {};
    db.ageGroups = [
        { Name: "All", Id: "" },
        { Name: "24 or Under", Id: "24 or Under" },
        { Name: "25 - 29", Id: "25 - 29" },
        { Name: "30 - 34", Id: "30 - 34" },
        { Name: "35 - 39", Id: "35 - 39" },
        { Name: "40 - 44", Id: "40 - 44" },
        { Name: "45 - 49", Id: "45 - 49" },
        { Name: "50 - 54", Id: "50 - 54" },
        { Name: "55 or Over", Id: "55 or Over" }
    ];

    db.genders = [
        { Name: "All", Id: 0 },
        { Name: "Male", Id: 1 },
        { Name: "Female", Id: 2 }
    ];

    db.charities = [
        { Name: "All", Id: 0 },
        { Name: "31Heroes", Id: 1 },
        { Name: "Back On My Feet", Id: 2 },
        { Name: "NBCF", Id: 3 },
        { Name: "O.U.R. Rescue", Id: 4 },
        { Name: "Scratch My Belly", Id: 5 }
    ];

    db.challenge = [
        { Name: "All", Id: 0 },
        { Name: "Circuit 1: Magnesium", Id: 1 },
        { Name: "Circuit 2: Cadmium", Id: 2 },
        { Name: "Circuit 3: Titanium", Id: 3 },
        { Name: "Circuit 4: Mercury", Id: 4 }
    ];

    var memberId = "";
    if(Cookies.get("memberId") != "") {
        memberId = Cookies.get("memberId");

        //if we have a memberID cookie, we can load our graphs

    }

    jQuery("#jsGrid").jsGrid({
        width: "100%",
        height: "760",
        pageSize: 50,
        filtering: true,
        editing: false,
        sorting: true,
        paging: true,

        loadIndication: true,
        loadIndicationDelay: 100,
        loadShading: true,
        pagerFormat: "Pages: {first} {prev} {pages} {next} {last} &nbsp;-&nbsp; Page {pageIndex} of {pageCount} &nbsp;-&nbsp; Total Records: {itemCount}",

        rowClick: function(args) {
            window.location.href = "/forms/challenge/profile.php?memberId=" + args.item.memberId;
        },

        autoload: true,
        controller: {
            loadData: function(filter) {
                var requestData = { challengeId: 1, filters: JSON.stringify(filter) };
                return jQuery.ajax({
                    type: "GET",
                    url: "/forms/lib/proxy.php",
                    data: {requrl: urlBase + "GetChallengeLeaderboardData?" + jQuery.param(requestData) },
                    contentType: "application/json; charset=utf-8",
                    cache: false,
                    dataType: "json"
                });
            }
        },
        loadIndicator: function(config) {
            return {
                show: function() {
                    jQuery("#loading_area").show();
                },
                hide: function() {
                    jQuery("#loading_area").hide();
                }
            };
        },
        fields: [
            { name: "memberId", css:"hide member", type: "number"},
            { name: "filterRank", align: "left", title:"Filter Rank", sorter:"number", type: "number", filtering: false, width: 40, headercss: "hylete_black" },
            { name: "total", align: "left", title:"Score", sorter:"number", type: "number", filtering: false, width: 40 },
            { name: "fullName", title:"Name", type: "text", autosearch: true, width: 150 },
            { name: "ageGroup", align: "left", items: db.ageGroups, valueField:"Id", textField:"Name", title:"Age Group", type: "select", width: 50 },
            { name: "gender", type: "select", items: db.genders, align: "left", valueField:"Id", textField:"Name", title:"Gender", valueType: "string", width: 50},
            { name: "state",  title:"State", type: "text", width: 50 },
            { name: "country", title:"Country", type: "text",  width: 50},
            { name: "charityId", align: "left", title:"Charity", type: "select", items: db.charities, valueField:"Id", textField:"Name", width: 100},
            { name: "gymName", title:"Gym Name", type: "text", width: 100},
            { name: "challengeId", align: "left", title:"Circuit", type: "select", items: db.challenge, valueField:"Id", textField:"Name", width: 80},
            { name: "controller", align: "center", title:"", type: "control", clearFilterButton: true, editButton: false, deleteButton: false, width: 35}
        ],
        onDataLoaded: function(args) {
            jQuery('td.hide').each(function(){
                if(jQuery(this).text() == memberId) {
                    jQuery(this).closest('tr').addClass('highlight');
                }
            });
        }
    });

});