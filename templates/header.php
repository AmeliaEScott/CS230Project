<div class="jumbotron" id="header">
  <div class="container text-center">
    <h1>VoteÜ</h1>
    <h4>A vote
      <em>for you</em>.
      <br>
      <small>Proud to provide to Morgantown U</small>
    </h4>
  </div>
</div>
<nav class="navbar navbar-default">
  <div class="container">
    <ul class="navbar-nav nav navbar-left">
      <li class="active">
        <a href="/">Home</a>
      </li>
      <li>
        <a href="/campaigns">Campaigns</a>
      </li>
      <li>
        <a href="/results">Results</a>
      </li>
      <li>
        <a href="/about">About</a>
      </li>
    </ul>
    <ul class="navbar-nav nav navbar-right">
      <li id="loginLink">
        <a href="javascript:getSession()">Login</a>
      </li>
      <li id="userMenu" class="hidden dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{userId}} <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="/my/votes">My Votes</a></li>
          <li><a href="#">Something else here</a></li>
          <!-- everything between this is for privileged roles only (e.g. election commisioner, provost, etc.) -->
          <li role="separator" class="divider"></li>
          <li><a href="#">Review Results</a></li>
          <!-- end privileged roles links -->
          <li role="separator" class="divider"></li>
          <li>
            <a href="/my/settings">Settings</a>
          </li>
          <li><a href="javascript:clearSession()">Logout</a></li>
        </ul>
      </li>
    </ul>
  </div>
</nav>
