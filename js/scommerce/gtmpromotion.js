jQuery(document).ready(function($){var promotionCount=jQuery('a[data-promotion]').size();if(promotionCount>0){var a=['id','name','creative','position'];var promoImpression=[];var promoClick=[];jQuery('a[data-promotion]').each(function(){if($(this).data("promotion")==!0){var obj={};obj[a[0]]=$(this).data("id");obj[a[1]]=$(this).data("name");obj[a[2]]=$(this).data("creative");obj[a[3]]=$(this).data("position");promoImpression.push(obj)}
$(this).click(function(e){href=$(this).attr('href');e.preventDefault();dataLayer.push({'event':'promotionClick','ecommerce':{'promoClick':{'promotions':[obj]}},'eventCallback':function(){if(!(e.ctrlKey||e.which==2)){document.location=href}}})})});dataLayer.push({'ecommerce':{'promoView':{'promotions':promoImpression}}})}})