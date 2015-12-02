$('#castVote').click(function(e) {
  e.preventDefault();
  var d = [];
  $('form.raceForm').each(function() {
    d.push(getFormData($(this)));
  });
  if (confirm("Are you sure you'd like to cast your ballot now? This can't be changed later.")) {
    $.ajax({
      url: document.location.pathname + (!document.location.pathname.endsWith('/') ? '/' : '') + 'vote',
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(d)
    }).done(function(d) {
      console.log(d);
      if(d.success) {
        alert("Your vote has been cast.");
        location.reload();
      } else {
        alert("Error: " + d.error);
      }
    });
  }
});

$('#straight-ticket').on('change', function() {
  $('input[data-party="' + this.value + '"]').prop("checked", true);
});

$('input[type="radio"][name^="race:"][value!="candidate:writein"]').on('change', function(e){
  if($(this).is(':checked')) {
    $('input[name="' + e.currentTarget.name + ':writein"]').val('');
  }
});

$('input[name^="race:"][name$=":writein"]').on('input', function(){
  if($(this).val().length > 0) {
    $(this).prev().attr('checked', true);
  }
});
