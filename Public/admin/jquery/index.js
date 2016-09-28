  
function setday(){

  var today = new Date();
  var year=today.getFullYear();
  var month=today.getMonth()+1;
  month=month<10?'0'+month:month;
  var day  =today.getDate();
  var tod=year+'-'+month+'-'+day;
  $('.end_day').attr('value',tod);

  var sev_prev=new Date();
  sev_prev.setDate(today.getDate()-7);
  // alert(sev_prev);
  var year_p=sev_prev.getFullYear();
  var month_p=sev_prev.getMonth()+1;
  month_p =month_p<10?'0'+month_p:month_p;
  var day_p=sev_prev.getDate();

  var sev_p=year_p+'-'+month_p+'-'+day_p;
  $('.start_day').attr('value',sev_p);
}