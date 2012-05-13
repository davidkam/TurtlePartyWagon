$(document).ready(function() {
  $('.copylink').bind('click', function() {
    copy()
  });
  $('.rerunlink').bind('click', function() {
    copy();
console.log($('form'));
    $('form').submit();
  });
});

var copy = function() {
  $('.code').html($('.code-exec').html());
}
