$(document).ready(function() {
  $('.copylink').bind('click', function() {
    copy()
  });
  $('.rerunlink').bind('click', function() {
    copy();
    $('form').submit();
  });
});

var copy = function() {
  $('.code').html($('.code-exec').html());
}
