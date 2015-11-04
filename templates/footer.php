<footer class="container text-center">
    A product of The Team Formerly Known as Prince <img src="https://upload.wikimedia.org/wikipedia/en/a/af/Prince_logo.svg" height="16px">
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="https://rawgit.com/js-cookie/js-cookie/master/src/js.cookie.js"></script>
<script src="https://rawgit.com/btmills/geopattern/master/js/geopattern.min.js"></script>
<script defer type="text/javascript">
  var sessid;
  $(function() {
    $("#header").geopattern("VoteÜ", {
      color: "#77C4D3"
    });
    if(typeof Cookies.get("sessid") != "undefined") {
      sessid = Cookies.get("sessid");
      toggleUserMenu();
    }
  });

  function getSession() {
    //this is only for demonstration purposes
    sessid = prompt("Please enter your session ID.");
    if (sessid != '') {
      Cookies.set("sessid", sessid);
      toggleUserMenu();
    } else {
      alert("Error logging in user with session \"" + sessid + "\".");
    }
  }

  function toggleUserMenu() {
    var t = "{{userId}}";
    $("#loginLink").toggleClass("hidden");
    $("#userMenu").toggleClass("hidden");
    if($("a.dropdown-toggle").text().includes(t)) {
      $("a.dropdown-toggle").html(function() {
        return $(this).html().replace("{{userId}}", sessid);
      });
    }
  }
</script>
</body>

</html>
