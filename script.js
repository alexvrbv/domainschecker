jQuery.noConflict;
jQuery(document).ready(function($) {
    var date = $('.date').html();
    var reg_till = $('.reg-till').html();
    //console.log(date);
    //console.log(reg_till);
    
    
    function getDateObject(str) {
          var arr = str.split("-");
          return new Date(arr[2], arr[1], arr[0]);
    }
    
    var correct_date = getDateObject(date);
    //console.log(correct_date);
    
    //var days = (date2 - date1) / 86400000;
    
    $('.reg-till').each(function(){
        //console.log(getDateObject($(this).html()));
        //console.log((correct_date - getDateObject($(this).html())) / -86400000);
        if(((getDateObject($(this).html()) - correct_date) / 86400000) <= 60) {
            $(this.parentNode).addClass('red');
        }
    });
    
    
});