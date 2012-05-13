<?php
require_once('../docs/config.php');

class command {
  const CMD_FORWARD = 'FORWARD';
  const CMD_BACKWARD = 'BACKWARD';
  const CMD_BACKWARD_ALIAS = 'REVERSE';
  const CMD_WHEELS = 'WHEELS';
  const CMD_CENTER = 'CENTER';
  const CMD_LEFT = 'LEFT';
  const CMD_RIGHT = 'RIGHT';
  const CMD_GOTO = 'GOTO';

  const PARAM_CENTER = 1;
  const PARAM_LEFT = 2;
  const PARAM_RIGHT = 3;

  const CONST_MAXNUM = 255;

  // original line
  private $_line = '';

  // final line
  private $_finalline = array();

  // pretty line
  private $_prettyline = '';

  // command
  private $_cmd = '';

  // original command
  private $_ocmd = '';

  // first param
  private $_param1 = '';

  // original first param
  private $_oparam1 = '';

  // second param
  private $_param2 = '';

  // original second param
  private $_oparam2 = '';

  // constructor
  public function command($line = '') {
    $this->_line = trim($line);
  }

  public function parseLine() {
    $this->setLineVars();
    $this->checkPolarity();
    $this->parseCommand();
  }

  protected function setLineVars() {
    list($this->_cmd, $this->_param1, $this->_param2) = explode(' ', $this->_line);
    $this->_ocmd = $this->_cmd;
    $this->_oparam1 = $this->_param1;
    $this->_oparam2 = $this->_param2;
  }

  // set the vars based on polarity
  protected function checkPolarity() {
    switch($this->_cmd) {
      case self::CMD_BACKWARD:
      case self::CMD_BACKWARD_ALIAS:
        $this->_cmd = (TRUE_POLARITY)?self::CMD_BACKWARD:self::CMD_FORWARD;
        break;
      case self::CMD_FORWARD:
        $this->_cmd = (TRUE_POLARITY)?self::CMD_FORWARD:self::CMD_BACKWARD;
        break;
      case self::CMD_CENTER:
        $this->_cmd = self::CMD_WHEELS;
        $this->_param1 = self::PARAM_CENTER;
        $this->_param2 = '';
        break;
      case self::CMD_LEFT:
        $this->_cmd = self::CMD_WHEELS;
        $this->_param1 = (TRUE_POLARITY)?self::PARAM_LEFT:self::PARAM_RIGHT;
        $this->_param2 = '';
        break;
      case self::CMD_RIGHT:
        $this->_cmd = self::CMD_WHEELS;
        $this->_param1 = (TRUE_POLARITY)?self::PARAM_RIGHT:self::PARAM_LEFT;
        $this->_param2 = '';
        break;
    }
  }

  // parse the command and generate the compiled command
  protected function parseCommand() {
    switch($this->_cmd) {
      case self::CMD_BACKWARD:
      case self::CMD_BACKWARD_ALIAS:
      case self::CMD_FORWARD:
        if($this->validNum($this->_param1) && !$this->validNum($this->_param2)) {
          $number = intval($this->_param1);
          if($number <= self::CONST_MAXNUM) {
            $this->_finalline[] = $this->_cmd . ' ' . $this->_param1;
          } else {
            $multiple = floor($number / self::CONST_MAXNUM);
            for($x = 0; $x < $multiple; $x++) {
              $this->_finalline[] = $this->_cmd . ' ' . self::CONST_MAXNUM;
            }
            $this->_finalline[] = $this->_cmd . ' ' . ($number % self::CONST_MAXNUM);
          }
        }
        $this->_prettyline = $this->_ocmd . ' ' . $this->_oparam1;
        break;
      case self::CMD_CENTER:
      case self::CMD_LEFT:
      case self::CMD_RIGHT:
      case self::CMD_WHEELS:
        if($this->validNum($this->_param1) && !$this->validNum($this->_param2)) {
          switch($this->_param1) {
            case ($this->_param1 == self::PARAM_CENTER):
            case ($this->_param1 == self::PARAM_LEFT):
            case ($this->_param1 == self::PARAM_RIGHT):
              $this->_finalline[] = $this->_cmd . ' ' . $this->_param1;
              $this->_prettyline = $this->_ocmd . ' ' . $this->_oparam1;
              break;
          }
        }
        break;
    }
  }

  protected function validNum($num) {
    $status =  (($num == "0") ||($num != '' && intval($num) != 0));
    return $status;
  }

  public function getFinalline() {
    return $this->_finalline;
  }
  
  public function getPrettyline() {
    return $this->_prettyline;
  }
  
}
