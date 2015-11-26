$.ajax({
      url: 'http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css',
      success: function(data){
           $('<style></style>').appendTo('head').html(data);
      }
});

$.getScript('http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js').done(function(){
  var newVal = moment().add(1, 'hour').startOf('hour').format('L LT');
  newVal += ' - ' + moment().add(7, 'day').endOf('hour').format('L LT');
  $('input[name="ballotRange"]').val(newVal);
  $('input[name="ballotRange"]').daterangepicker({
   timePicker: true,
   timePickerIncrement: 30,
   locale: {
       format: 'MM/DD/YYYY h:mm A'
   }
  });
});

$('[data-submit!=""]').click(function(e) {
  e.preventDefault();
  var id = $(this).attr('data-submit');
  $(id).submit();
});

$('#addRace, #addCandidate').click(function(e){
  e.preventDefault();
  $('#' + $(this).attr('id') + 'Modal').modal('show');
});

$('input[name=allowWriteIn]').change(function() {
  $('input[name=verifyWriteIn]').prop('disabled', !$(this).is(":checked"));
});

$('input[name=ballotRaces]').change(function() {
  var data = JSON.parse($(this).attr('value') || '[]');
  var src = $('#ballotRacesResultsTemplate').html();
  var tmpl = Handlebars.compile(src);
  $('#ballotRacesResults')[0].innerHTML = tmpl({race: data});
  $('#ballotRacesResults').prop('class', false);
});

$('input[name=candidates]').change(function() {
  var data = JSON.parse($(this).attr('value') || '[]');
  var src = $('#candidatesResultsTemplate').html();
  var tmpl = Handlebars.compile(src);
  $('#candidatesResults')[0].innerHTML = tmpl(data);
  $('#candidatesResults').prop('class', false);
});

$('body').on('click', '.glyphicon-trash[data-action!=""]', function(){
  console.log("clicked trash");
  var v = $(this).attr('data-action').split(':');
  var elm = 'input[name=' + (v[0] == 'removeRace' ? 'ballotRaces' : 'candidates') + ']';
  var data = JSON.parse($(elm).attr('value') || '[]');

  if(v[1] < data.length) {
    data.splice(v[1], 1);
  }

  $(elm).attr('value', JSON.stringify(data)).trigger('change');
});

$('#addRaceForm, #addCandidateForm').submit(function(e){
  e.preventDefault();
  var vals = {'addCandidateForm':['candidates', 'addCandidateModal'], 'addRaceForm':['ballotRaces', 'addRaceModal']};
  var data = JSON.parse($('input[name="'+vals[$(this).attr('id')][0]+'"]').val() || '[]');
  data.push(getFormData($(this)));;
  $('input[name="'+vals[$(this).attr('id')][0]+'"]').attr('value', JSON.stringify(data)).trigger('change');
  $(this)[0].reset();
  $('#' + vals[$(this).attr('id')][1]).modal('hide');
});

$('#createBallot').submit(function(e) {
  e.preventDefault();
  var data = getFormData($(this));
  console.log(data);
  var valid = data['ballotName'].length > 0 &&
    data['ballotRange'].split("-").length == 2 &&
    Array.isArray(data['ballotRaces']) || isJsonStr(data['ballotRaces']);

  if(!valid) {
    alert("Please fill in all required fields before attempting to submit.");
  } else {
    $.ajax({
      url: document.location,
      type: "POST",
      data: JSON.stringify(data),
      contentType: "application/json",
    }).done(function(d){
      if(d.success) {
        window.location = window.location.pathname.replace(/\/dashboard\/create\-ballot\/?/, "/campaign/" + d.id);
      }
    });
  }
});
