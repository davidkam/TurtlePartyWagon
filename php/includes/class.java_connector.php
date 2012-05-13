<?php
require_once('../docs/config.php');
require_once('class.command.php');

class javaConnector {
  const SHELLCMD_JAVA = 'java -Djava.library.path="[BINPATH]" -jar [BINPATH]RemoteCarRXTX.jar';
  const SHELLCMD_FINDPORT = 'find /dev -name "tty.usbmodem*" 2>/dev/null';

  // input string
  private $_input;

  // name of file that gets passed to Java
  private $_filename = '';

  // code that gets saved to file and passed to Java
  private $_finalcode = array(); 

  // sanitized code that user entered in
  private $_prettycode = array(); 

  // status of result
  private $_status = '';

  // Constructor
  public function javaConnector($input = '') {
    $this->_input = $input;
  }

  // set the filename
  protected function setFilename() {
    $done = false;
    while(!$done) {
      $filename = $this->generateFilename();
      if(!file_exists(SCRIPTPATH . $filename)) {
        $done = true;
      }
    }
    $this->_filename = $filename;
  }

  // generate a random filename based on microtime and random string
  protected function generateFilename() {
    list($usec, $sec) = explode(" ", microtime());
    $stuff = '';
    for($x = 0; $x < 6; $x++) {
      $stuff .= rand(0,9);
    }
    $filename = "$sec-$stuff.txt";
    return $filename;
  }

  public function processCode() {
    if($this->_input != '') {
      $this->sanitizeInput();
      $this->parseInput();
      $this->execute();
    }
  }

  protected function getCmd() {
    $cmd = str_replace('[BINPATH]',BINPATH,self::SHELLCMD_JAVA);
    return $cmd;
  }

  function findPort() {
    $cmd = SHELLCMD_FINDPORT;
    $port = `$cmd`;
    return $port;
  }

  protected function parseInput() {
    $lines = explode("\n",$this->_input);
    foreach($lines as $line) {
      $command = new command($line);
      $command->parseLine();
      if($finalline = $command->getFinalline()) {
        if(is_array($finalline)) {
          foreach($finalline as $key=>$value) {
            $this->_finalcode[] = $value;
          }
        } else {
          $this->_finalcode[] = $finalline;
        }
      }
      if($prettyline = $command->getPrettyline()) {
        $this->_prettycode[] = $prettyline;
      }
    }
  }

  protected function sanitizeInput() {
    // remove surrounding white space
    $this->_input = trim(strtoupper($this->_input));

    // swap 2+ consecutive spaces with a single space
    $this->_input = preg_replace('/ {2,}/',' ', $this->_input);
  }

  // process the data, write it out, execute java command, set status
  public function execute() {
    $this->_finalcode = $this->sanitizeCode($this->_finalcode);
    $this->_prettycode = $this->sanitizeCode($this->_prettycode);
    if($this->_finalcode != '') {
      $this->setFilename();
      $fullFilename = SCRIPTPATH . $this->_filename;
      file_put_contents($fullFilename, $this->_finalcode);
  
      $port = $this->findPort();
      $cmd = $this->getCmd() . " $fullFilename $port";
      $status = `$cmd`;
      $statusData = explode("\n",$status);
      $junk = array_pop($statusData);
      $lastLine = trim(array_pop($statusData));
      if($lastLine == 'SENDING: STOP') {
        $this->_status = 'Command Successfully Executed.';
      } else {
        $this->_status = 'Error: ' . $lastLine;
      }
    }
  }

  protected function sanitizeCode($code) {
    $code = implode($code,"\n");
    $code = str_replace(chr(13),'',$code);
    return $code;
  }

  public function getFinalcode() {
    return $this->_finalcode;
  }

  public function getPrettycode() {
    return $this->_prettycode;
  }

  public function getStatus() {
    return $this->_status;
  }
}
