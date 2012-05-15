<?php
require_once('config.php');
require_once(INCLUDEPATH . 'class.command.php');
require_once(INCLUDEPATH . 'class.java_connector.php');

if(isset($_POST['code']) && $_POST['code'] != '') {
  $code = new javaConnector($_POST['code']);
  $code->processCode();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Turtle Party Wagon (tm)</title>
<link rel="icon" href="/images/favicon.ico">
<link rel="stylesheet" href="/styles/reset.css" type="text/css">
<link rel="stylesheet" href="/styles/screen.css" type="text/css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script src="/scripts/nrd.js"></script>
</head>
<body>
  <div class="container">
  <h1>Turtle Party Wagon&trade;</h1>
    <div class="content">
      <form method="post" action="/">
        <fieldset>
          <legend>Program</legend>
          <div class="colone">
            <textarea class="code" name="code"></textarea>
          </div>
          <div class="coltwo">
            <div class="preview">
<?php if(is_object($code) && $code->getStatus() != ''): ?>
              <h3>Executed Code:</h3>
              <div class="copy"><a href="#" class="copylink">(copy)</a></div>
              <div class="rerun"><a href="#" class="rerunlink">(rerun)</a></div>
              <div class="codeblock">
                <pre class="code-exec"><?php echo $code->getPrettycode(); ?></pre>
              </div>
<?php endif; ?>
            </div>
          </div>
        </fieldset>
        <input type="submit" name="whee" value="Run" />
      </form>
    </div>
  </div>
</body>
</html>
