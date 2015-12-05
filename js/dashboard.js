function apiurlify(s) {
  var url = document.location.pathname;
  return url + (url.endsWith('/') ? '' : '/') + 'api/' + s;
}
