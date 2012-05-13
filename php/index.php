<?php
define('CMD_FORWARD', 'FORWARD');
define('CMD_REVERSE', 'REVERSE');
define('CMD_REVERSE_CMD', 'BACKWARD');
define('CMD_WHEELS', 'WHEELS');
define('CMD_CENTER', 'CENTER');
define("CMD_LEFT", "LEFT");
define('CMD_RIGHT', 'RIGHT');
define('PARAM_CENTER', 1);
define('PARAM_LEFT', 2);
define('PARAM_RIGHT', 3);
define('CMD_GOTO', 'GOTO');

define('BASEPATH', dirname(__FILE__) . '/..');
define('SCRIPTPATH', BASEPATH . '/scripts/');
define('BINPATH', BASEPATH . '/bin/');

define('CONST_MAXNUM', 255);
define('SHELL_FINDPORT', 'find /dev -name "tty.usbmodem*" 2>/dev/null');
define('SHELL_JAVACMD', "java -Djava.library.path=" . '"' . BINPATH . '"' . ' -jar ' . BINPATH . 'RemoteCarRXTX.jar');
//define('SHELL_JAVACMD', 'java -jar ' . BINPATH . 'RemoteCarRXTX.jar');

define('TRUE_POLARITY', false);

$code = '';


if(isset($_POST['code']) && $_POST['code'] != '') {
  $rawcode = $_POST['code'];
  $code = processCode($rawcode);
}

function findPort() {
  $cmd = SHELL_FINDPORT;
  $port = `$cmd`;
  return $port;
}

function processCode($code) {
  $finalcode = array();
  $prettycode = array();
  $code = trim(strtoupper($code));
  $code = preg_replace('/ {2,}/',' ', $code);
  $lines = explode("\n",$code);
  foreach($lines as $line) {
    $line = trim($line);
    list($cmd, $param1, $param2) = explode(' ', $line);
    // figure out polarity
    switch($cmd) {
      case CMD_REVERSE:
      case CMD_REVERSE_CMD:
        $cmd = (TRUE_POLARITY)?CMD_REVERSE_CMD:CMD_FORWARD;
        break;
      case CMD_FORWARD:
        $cmd = (TRUE_POLARITY)?CMD_FORWARD:CMD_REVERSE_CMD;
        break;
      case CMD_CENTER:
        $cmd = CMD_WHEELS;
        $param1 = PARAM_CENTER;
        $param2 = '';
        break;
      case CMD_LEFT:
        $cmd = CMD_WHEELS;
        $param1 = (TRUE_POLARITY)?PARAM_LEFT:PARAM_RIGHT;
        $param2 = '';
        break;
      case CMD_RIGHT:
        $cmd = CMD_WHEELS;
        $param1 = (TRUE_POLARITY)?PARAM_RIGHT:PARAM_LEFT;
        $param2 = '';
        break;
    }
    switch($cmd) {
      case CMD_REVERSE:
      case CMD_REVERSE_CMD:
      case CMD_FORWARD:
        if(validNum($param1) && !validNum($param2)) {
          $number = intval($param1);
          if($number <= CONST_MAXNUM) {
            $finalcode[] = "$cmd $param1";
          } else {
            $multiple = floor($number / CONST_MAXNUM);
            for($x = 0; $x < $multiple; $x++) {
              $finalcode[] = "$cmd " . CONST_MAXNUM;
            }
            $finalcode[] = "$cmd " . ($number % CONST_MAXNUM);
          }
        }
        $prettycode[] = "$cmd $param1";
        break;
      case CMD_CENTER:
      case CMD_LEFT:
      case CMD_RIGHT:
      case CMD_WHEELS:
        if(validNum($param1) && !validNum($param2)) {
          switch($param1) {
            case ($param1 == PARAM_CENTER):
            case ($param1 == PARAM_LEFT):
            case ($param1 == PARAM_RIGHT):
              $finalcode[] = "$cmd $param1";
              $prettycode[] = "$cmd $param1";
              break;
          }
        }
        break;
    }
  }
  $finalcode = implode($finalcode,"\n");
  $finalcode = str_replace("",'',$finalcode);
  $prettycode = implode($prettycode,"\n");
  $prettycode = str_replace("",'',$prettycode);
  if($finalcode != '') {
    $filename = createFileName();
    $fullFilename = SCRIPTPATH . $filename;
    file_put_contents($fullFilename, $finalcode);

    $port = findPort();
    $cmd = SHELL_JAVACMD . " $fullFilename $port";
//print $cmd;
    $status = `$cmd`;
//print "\n<br /><br />\n\n";
//print $status;
  }
  return $prettycode;

}

function createFileName() {
  $done = false;
  while(!$done) {
    $filename = getFileName();
    if(!file_exists(SCRIPTPATH . $filename)) {
      $done = true;
    }
  }
  return $filename;
}

function getFileName() {
  list($usec, $sec) = explode(" ", microtime());
  $stuff = '';
  for($x = 0; $x < 6; $x++) {
    $stuff .= rand(0,9);
  }
  $filename = "$sec-$stuff.txt";
  return $filename;
}
function validNum($num) {
  $status =  (($num == "0") ||($num != '' && intval($num) != 0));
  return $status;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Turtle Party Wagon (tm)</title>
<link rel="stylesheet" href="/styles/reset.css" type="text/css">
<link rel="stylesheet" href="/styles/screen.css" type="text/css">
</head>
<body>
  <div class="container">
  <h1>Turtle Party Wagon&trade;</h1>
    <div class="content">
      <form method="post" action="/">
        <fieldset>
          <legend>Program</legend>
          <div class="colone">
            <textarea name="code"></textarea>
          </div>
          <div class="coltwo">
            <div class="preview">
<h3>Executed Code:</h3>
<pre>
<?php echo $code; ?>
</pre>
            </div>
          </div>
        </fieldset>
        <input type="submit" name="submit" value="Run" />
      </form>
    </div>
  </div>
</body>
</html>
