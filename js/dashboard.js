function apiurlify(s) {
  var url = document.location.pathname;
  return url + (url.endsWith('/') ? '' : '/') + 'api/' + s;
}

var ec = {
  remove: function(id) {
    this.do(0, id);
  },
  approve: function(id) {
    this.do(1, id);
  },
  do: function(n, id) {
    var opts = {
      url: apiurlify('commissioners' + (n ? '' : ('/' + id))),
      method: (n ? 'POST' : 'DELETE')
    };
    if (n) opts.data = {'user': id};
    $.ajax(opts).done(function(d) {
      msg = 'User with ID ' + id + ' is no';
      if (typeof d == 'object' && d.success) {
        msg += (n ? 'w' : ' longer') + ' an election commissioner.';
      } else {
        msg = d.message;
      }
      alert(msg);
    });
  }
}

$(function(){
  $('input[data-ec]').change(function() {
    var msg = 'Are you sure you wish to ' + ($(this).prop('checked') ? 'add' : 'remove') + ' this user as an election commissioner?';
    if (confirm(msg)) {
      if ($(this).prop('checked')) {
        ec.approve($(this).attr('data-ec'));
      } else {
        ec.remove($(this).attr('data-ec'));
      }
    } else {
      $(this).prop('checked', !$(this).prop('checked'));
    }
  });

  $('input[name="adminView"]').change(function() {
      $('#studentPanel').toggleClass('hidden');
      $('#adminPanel').toggleClass('hidden');
  });

  $('span[data-approve], span[data-remove]').click(function() {
    var btn = $(this),
        type = $(this).prop('data-approve') ? 'approve' : 'remove',
        id = $(this).attr('data-' + type),
        row = $(this).parent().parent();
    if (id.length != 0 && !isNaN(id)) {
      var cb = function(d) {
        if (typeof d == 'object' && d.success) {
          row.removeClass('danger').addClass('success');
          btn.removeClass('btn-success').addClass('btn-danger');
          btn.children(':first').removeClass('glyphicon-ok').addClass('glyphicon-remove');
        } else if (typeof d == 'object' && !d.success) {
          alert(d.message);
        }
      }
      if (type == 'approve') {
        $.post(apiurlify('approve'), {'elecID': id}, cb);
      } else {
        $.ajax({
          url: document.location.pathname.replace(/dashboard\/?$/, 'campaign/' + id),
          method: 'DELETE'
        }).done(cb);
      }
    }
  });

});
