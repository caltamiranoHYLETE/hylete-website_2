//var urlBase = "http://localhost:60601/hyletePBHService.asmx/";
var urlBase =  "https://pbhservice.hylete.com/hyletePBHService.asmx/";

function nth(d) {
    if(d>3 && d<21) return d+'th';
    switch (d % 10) {
        case 1:  return d+"st";
        case 2:  return d+"nd";
        case 3:  return d+"rd";
        default: return d+"th";
    }
}

function getGenderFormal(intGender) {
    switch(intGender) {
        case 1:
            return "Male";
        case 2:
            return "Female"
    }
}

function getGenderInformal(intGender) {
    switch(intGender) {
        case 1:
            return "Men";
        case 2:
            return "Women"
    }
}