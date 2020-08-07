function DoSignIn(resp) {
  if( resp && ( resp.errors.length <= 0 || resp.status == "0.0") ){
    // $('#register-form').reset();
    if ( resp.rdt.length > 0 ) {
      setTimeout(function(){ window.location = resp.rdt; },3200);
    } else {
      setTimeout(function(){ removeAlert(); },3200);
    }
  }
}
function PwdReset(resp) {
  if( resp && ( resp.errors.length <= 0 || resp.status == "0.0") ){
    // $('#register-form').reset();
    if ( resp.rdt.length > 0 ) {
      setTimeout(function(){ window.location = resp.rdt; },3200);
    } else {
      setTimeout(function(){ removeAlert(); },3200);
    }
  }
}
function minTimer(duration, display, callback) {
  var timer = duration, minutes, seconds;
  display = $(display);
  var tymer = setInterval(function () {
    minutes = parseInt(timer / 60, 10);
    seconds = parseInt(timer % 60, 10);

    minutes = minutes < 10 ? "0" + minutes : minutes;
    seconds = seconds < 10 ? "0" + seconds : seconds;

    display.text(minutes + ":" + seconds);

    if (--timer < 0) {
      display.text("00:00");
      if ( typeof callback == 'function') {
        clearInterval(tymer);
        callback();
      }
    }
  }, 1000);
}
function enblResend (){
  $("#rsd-click").prop("disabled", false);
}
function dsblResend (){
  $("#rsd-click").prop("disabled", true);
}
$.fn.fetchLocal = function(param){
  var dom = $(this).find('optgroup'),
      url = ("/app/tymfrontiers-cdn/admin.soswapp/service/fetch-local.php"),
      containr = $(this);
  dom.html('');
  containr.prop('disabled',true).css('cursor','progress');
  $.ajax({
    url :  url,
    dataType : 'json',
    type : 'GET',
    data : param,
    success : function(data) {
      // console.log(data);
      if( data && (data.status == '0.0' || data.errors.length <= 0)){
        var opts="";
        $.each(data.results, function(i, res) {
          opts += ("<option value='"+res.code+"'>"+res.name+"</option>");
        });
        dom.html(opts);
        containr.prop('disabled',false).css('cursor','auto');
      }
    },
    error : function(xhr, textStatus, errorThrown){
      var errorMessage = xhr.responseText;
      console.error("Failed to load requested local recources: "+errorMessage);
    }
  });
};
function otpResent(resp) {
  if( resp && ( resp.errors.length <= 0 || resp.status == "0.0") ){
    $("#res-cnt-view").fadeIn();
    dsblResend();
    minTimer(7 * 60,"#cnt-timer", enblResend);
  }
}
function resetSent(resp) {
  if( resp && ( resp.errors.length <= 0 || resp.status == "0.0") ){
    if ($("#password-reset-form").hasClass('resend-otp')) {
      dsblResend();
      minTimer(10 * 60,"#cnt-timer", enblResend);
      setTimeout(function(){
        removeAlert();
      },3500);
    } else {
      $("#res-cnt-view").fadeIn();
      dsblResend();
      minTimer(7 * 60,"#cnt-timer", enblResend);
      $("#btn-msg").text("Resend");
      $("#password-reset-form input[name=email]").prop("disabled",true);
      $("#password-reset-form").attr("action", "/ResendOTP.php");
      $("#password-reset-form").addClass("resend-otp");
    }
  }
}
$.fn.page = function(){
  var page = parseInt($(this).data('page')),
      cur_page = parseInt($('#page').val());
  if( page > 0 && page !== cur_page){
    $('#page').val(page);
    $('#query-form').submit();
  }
};
(function(){
  // generic runtime tasks
  if ($('select[name=country_code]').length > 0 && $('select[name=state_code]').length > 0) {
    var has_city = $('select[name=city_code]').length > 0;
    $('select[name=country_code]').change(function(){
      if( $(this).val().length > 0 ){
        $('select[name=state_code]').fetchLocal({type:'state',code:$(this).val()});
      }
      if (has_city) $('select[name=city_code]').val('');
    });
    if (has_city) {
      $('select[name=state_code]').change(function(){
        if( $(this).val().length > 0 ){
          $('select[name=city_code]').fetchLocal({type:'city',code:$(this).val()});
        }
      });
    }
  }
})();
