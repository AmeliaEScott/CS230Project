function addEC() {
  var id = document.getElementById('ecUserId').value;
  console.log("registering id: " + id);
  var url = document.location.pathname;
  url += (url.endsWith('/') ? "" : "/") + "api/commissioners";
  $.post(url, {'user':id}, function(d){
    if (typeof d == 'object' && d.success) {
      alert("User with ID " + id + " is now an election commissioner.");
    } else if (typeof d == 'object' && !d.success) {
      alert(d.message);
    }
  });
  return false;
}
