$('span[data-certify]').click(function() {
  $.post(document.location.pathname, {'certify': true}).done(function(d) {
    if (d.success) {
      alert("These results have been certified and are now public.");
      $(this).parent().hide();
    } else {
      alert("Error: " + d.message);
    }
  });
});

$('i[data-disqualify], i[data-approve]').click(function() {
  var allow = $(this).is('i[data-approve]'), d = {
    'userID': $(this).attr('data-disqualify') || $(this).attr('data-approve'),
    'elecID': document.location.pathname.match(/campaign\/([0-9]+)\/results/).length >= 2 ? document.location.pathname.match(/campaign\/([0-9]+)\/results/)[1] : -1
  }, btn = $(this);
  $.ajax({
    url: document.location.pathname.replace(/\/campaign\/([0-9]+)\/results.*$/, "/dashboard/api/" + (allow ? "allow" : "disqualify")),
    data: d,
    type: 'POST'
  }).done(function(d) {
    var msg = d.success ? ('User with ID "' + d['userID'] + '" has been ' + (allow ? 'allow to vote in' : 'disqualified from voting in') + ' this election.') : ('Error: ' + d.message);
    alert(msg);
    if (d.success) {
      btn.prop('data-' + (allow ? 'disqualify' : 'approve'), d['userID']);
      btn.removeProp('data-' + (allow ? 'disqualify' : 'approve'));
      btn.removeClass('glyphicon-' + (allow ? 'ok' : 'ban') + '-circle').addClass('glyphicon-' + (!allow ? 'ok' : 'ban') + '-circle');
    }
  });
});
