var ec = {
  change : function(n, id) {
    return this.do(-1, {'user': id, 'elec': n});
  },
  remove: function(id) {
    return this.do(0, id);
  },
  approve: function(id) {
    return this.do(1, id);
  },
  do: function(n, id) {
    return $.ajax({
      url: apiurlify('commissioners' + (Boolean(Math.abs(n)) ? '' : ('/' + id))),
      method: (Boolean(Math.abs(n)) ? 'POST' : 'DELETE'),
      data: (Boolean(Math.abs(n)) ? (n == -1 ? id : {'user': id}) : {})
    });
  }
};

var user = {
  dq: function(allow, id) {
    var opts = {
      url: apiurlify(allow ? 'allow' : 'disqualify'),
      method: 'POST',
      data: {'userID': id}
    };
    return $.ajax(opts);
  }
}

$(function(){
  $('span[data-ecEdit]').click(function() {
    if (confirm('Are you sure you wish to change the election commissioner on this election?')) {
      $('div#changeECmodal').modal('toggle');
      $('input[name="changeECid"]').val($(this).attr('data-ecEdit'));
    }
  });

  $('button#changeEC').click(function() {
    var ecUser = {
      id: $('select[name="ecUsers"]').val(),
      name: $('select[name="ecUsers"] option:selected').text()
    }, elecID = $('input[name="changeECid"]').val();
    ec.change(elecID, ecUser.id).done(function(d) {
      if (d.success) {
        alert('User "' + ecUser.name + '" is now the election commissioner of this election.');
        $("span[data-ecedit='" + elecID + "']").parent().contents().filter(function(){
          return this.nodeType == 3;
        })[0].nodeValue = ecUser.name + ' ';
      } else {
        alert('Error: ' + d.message);
      }
      $('div#changeECmodal').modal('toggle');
    });
  });

  $('span[data-ecEdit]').parent().parent().hover(function() {
    $(this).find('span[data-ecEdit]').toggleClass('hidden');
  });

  $('input[data-ec]').change(function() {
    var addEC = $(this).prop('checked'),
        msg = 'Are you sure you wish to ' + (addEC ? 'add' : 'remove') + ' this user as an election commissioner?',
        dataCol = $(this).parent().next().next(),
        id = $(this).attr('data-ec'),
        cb = function(d) {
          var userData = JSON.parse(dataCol.text() || {}), msg;
          if (typeof d == 'object' && d.success) {
            msg = 'User with ID ' + id + ' is no' + (addEC ? 'w' : ' longer') + ' an election commissioner.';
          } else {
            msg = d.message;
          }
          userData['isEC'] = addEC
          alert(msg);
          dataCol.fadeOut(500, function() {
            $(this).text(JSON.stringify(userData));
          }).fadeIn(500);
        };
    if (confirm(msg)) {
      if ($(this).prop('checked')) {
        ec.approve(id).done(cb);
      } else {
        ec.remove(id).done(cb);
      }
    } else {
      $(this).prop('checked', !$(this).prop('checked'));
    }
  });

  $('input[data-dq]').change(function() {
    var allow = !$(this).prop('checked'),
        msg = 'Are you sure you wish to ' + (!allow ? 'disqualify' : 're-allow') + ' this user from casting votes?',
        dataCol = $(this).parent().next(),
        id = $(this).attr('data-dq');
    if (confirm(msg)) {
      user.dq(allow, id).done(function(d) {
        var msg = 'User with ID ' + id + ' is now ' + (!allow ? 'banned from voting across all elections.' : 're-allowed to vote in all elections.'),
            userData = JSON.parse(dataCol.text()|| {});
        if (typeof d == 'object' && d.success) {
          alert(msg);
        } else if (typeof d == 'object' && !d.success) {
          alert('Error: ' + d.message);
        }
        userData["banned"] = !allow;
        dataCol.fadeOut(500, function() {
          $(this).text(JSON.stringify(userData));
        }).fadeIn(500);
      });
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
