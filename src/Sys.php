<?php
/**
 * Class Sys
 * @author Igor Moiseev
 * @version 2011.12
 *
 *   Copyright (C) 2011 Igor Moiseev
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class Sys
{
  /**
   * The limit for emails
   * @var integer
   * @access private
   */
  protected $limit_emails = 300;

  /**
   * Limit for active channels in the callweaver
   * @var integer
   * @access private
   */
  protected $max_channels = 150;

  /**
   * Number of active channels
   * @var integer
   * @access private
   */
  protected $active_channels = 100;

  /**
   * Number of free channels
   * @var integer
   * @access private
   */
  protected $free_channels = 0;

  /**
   * Is init var
   * @var boolean
   * @access private
   */
  private $isInit = false;

  /**
   * Version
   */
  protected $version = "0.1";

  /**
   * @var string
   * @access protected
   */
  protected $admin_email = "admin@example.com";

  /**
   * L'email dei messaggi log
   * @var string
   * @access protected
   */
  protected $logger_email = "logger";

  /**
   * Limits the number of destinatari per every email
   * @var integer
   * @access protected
   */
  protected $warn_limit_destinatari = 40;

  /**
   * Limits the number of attachments for every email
   * @var integer
   * @access protected
   */
  protected $warn_limit_attachs = 10;

  /**
   * Limits the number of email in the email box before sending warning via email
   * @var integer
   * @access protected
   */
  protected $warn_limit_email = 300;

  /**
   * Limits the number of calls for script execution
   * @var integer
   * @access protected
   */
  protected $limit_call_email = 100;

  /**
   * Pear::Mail resource
   * @var resource
   * @access protected
   */
  protected $rmail = null;

  /**
   * Logger resourse
   *
   *  PEAR_LOG_EMERG      emerg()     System is unusable
   *  PEAR_LOG_ALERT      alert()     Immediate action required
   *  PEAR_LOG_CRIT       crit()      Critical conditions
   *  PEAR_LOG_ERR        err()       Error conditions
   *  PEAR_LOG_WARNING    warning()   Warning conditions
   *  PEAR_LOG_NOTICE     notice()    Normal but significant
   *  PEAR_LOG_INFO       info()      Informational
   *  PEAR_LOG_DEBUG      debug()     Debug-level messages
   *
   * @var resource
   * @access protected
   */
  protected $logger = null;

  /**
   * Conatins the array of messages to be written to log table
   * @var array
   * @access protected
   */
  protected $messages = array();

  /**
   * Conatins the db connection description
   * @var array
   * @access protected
   */
  protected $dsn = array();

  /**
   * Connection resource
   * @var PEAR::resource
   * @static static connection resource
   * @access protected
   */
  protected $dbconn = null;

  /**
   * MDB2 type object
   * @var MDB2 resorse
   * @access protected
   */
  protected $mdb2 = null;

  /**
   * Array of option for the class MDB2
   * @var array|false
   */
  protected $options = array();

  /**
   * Sys class options customization
   */
  protected $sys_options = array();

  /**
   * Destructor of the class Sys
   * @param void
   * @return boolean
   * @access public
   */
  public function __destruct()
  {
    return $this->close();
  } // END FUNCTION __destruct()

  /**
   * Contructor of the class Sys
   *
   * @param void
   * @return void
   * @access public
   */
  public function __construct()
  {
    $this->dsn = array (
     'phptype'  => 'mysqli',
     'hostspec' => 'localhost',
     'username' => 'email2db',
     'password' => 'pass',
     'database' => 'email2db',
    );

      // for MDB2
    $this->options = array (
      'persistent' => false,
      'ssl' => false,
      );

  } // END FUNCTION  __construct()

  /**
   * Manages the class Sys
   *
   * @param opts array
   * @return boolean
   * @access public
   */
  public function manage($opts)
  {
    if(isset($opts['log_table'])) {
      $this->sys_options['log_table'] = $opts['log_table'];
    }

    return $this->init();
  } // END FUNCTION manage()

  /**
   * Opens connection to db or die on fail
   *
   * @param void
   * @return boolean
   * @access public
   */
  private function init()
  {
    if($this->isInit)
    {
      return $this->isConn();
    }
    else
    {
      $this->isInit = true;

      setlocale(LC_ALL, 'it_IT.utf8');

      setlocale(LC_ALL, 'it_IT.utf8');

      $this->dbconn = mysqli_init();
      if (!$this->dbconn)
      {
        die('mysqli_init failed');
        return false;
      }

      if (!mysqli_real_connect($this->dbconn, $this->dsn['hostspec'], $this->dsn['username'], $this->dsn['password'], $this->dsn['database']))
      {
        die('Could not connect: ' . mysqli_connect_error());
        return false;
      }

          //$res_char = mysqli_set_charset($this->dbconn, 'utf8');
      if (!mysqli_set_charset($this->dbconn, 'utf8')) {
        printf("Error loading character set utf8: %s\n", $mysqli_error());
      } else {
        /* Print current character set */
        $this->charset = mysqli_character_set_name($this->dbconn);
              //printf ("Current character set is %s\n",$this->charset);
      }

          // connection using MDB2 driver with reusing standard mydqli_connection
      $this->mdb2 = MDB2::factory($this->dsn, $this->options);

      if (PEAR::isError($this->mdb2))
      {
        die( $this->mdb2->getMessage() );
        return false;
      }
      $this->mdb2->setOption('persistent', true);
      $this->mdb2->opened_persistent = true;
      $this->mdb2->connection = $this->dbconn;
      $this->mdb2->connect();
          // loading the Function module
      $this->mdb2->loadModule('Function');

      if($this->isConn())
      {
              // log into db, MYSQL prb.
        $this->conf = array( 'db' => $this->mdb2, 'sequence' => $this->sys_options['log_table'] . '_id' );
        $this->logger = &Log::singleton('mdb2', 'email2db.' . $this->sys_options['log_table'], 'ident', $this->conf);

        set_error_handler( array($this,'errorHandler') );
              //trigger_error('This is an information log message.', E_USER_NOTICE);

        assert_options(ASSERT_CALLBACK, array($this, 'assertCallback'));
              //assert(false);

        set_exception_handler( array($this, 'exceptionHandler') );
              //throw new Exception('Uncaught Exception');

        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'errorPearHandler') );
              //PEAR::raiseError('This is an information log message.', PEAR_LOG_ERR);
      }

      $params['sendmail_path'] = '/usr/lib/sendmail';

          // Create the mail object using the Mail::factory method
      $this->rmail = Mail::factory('sendmail', $params);

      $this->logger->log("Sys::init() Init daemon email2db", PEAR_LOG_NOTICE );

      return true;
    }
  } // END FUNCTION init()

  /**
   * Closes connection with a db
   *
   * @param void
   * @return boolean
   * @access protected
   */
  private function close()
  {
    if(is_resource($this->dbconn) && $this->isConn()){
      return mysqli_close($this->dbconn);
    }
    $this->dbconn = null;
    return true;
  } // END FUNCTION close()

  /**
   * Returns the single value of the array mysqli_query_params__parameters
   *
   * @param $at the position of the parameter inside the array mysqli_query_params__parameters
   * @return mixed
   */
  final public function mysqli_query_params__callback( $at )
  {
    return $this->mysqli_query_params__parameters[ $at[1]-1 ];
  } // END FUNCTION mysqli_query_params__callback

  /**
   * Parameterised query implementation for MySQL (similar PostgreSQL's PHP function pg_query_params)
   * Example: mysqli_query_params( "SELECT * FROM my_table WHERE col1=$1 AND col2=$2", array( 42, "It's ok" ), $dbconn );
   *
   * @param $query, $parameters, $datadase
   * @return mixed(resorce, false)
   * @access public
   */
  protected function mysqli_query_params( $query, $parameters=array(), $database=false )
  {
    if( !is_array($parameters) ){
      $this->logger->log("Sys::mysqli_query_params(): expects the second parameter be the array type", PEAR_LOG_ERR );
      return false;
    } else {
      if($this->is_assoc($parameters)){
        $parameters = array_values($parameters);
      }
    }

      // Escape parameters as required & build parameters for callback function
    foreach( $parameters as $k=>$v )
    {
      $parameters[$k] = ( is_int( $v ) ? $v : ( NULL===$v ? 'NULL' : "'".mysqli_real_escape_string( $this->dbconn, $v )."'" ) );
    }
    $this->mysqli_query_params__parameters = $parameters;

      // Call using mysqli_query
    if( false === $database )
    {
      $query = preg_replace_callback( '/\$([0-9]+)/', array($this, 'mysqli_query_params__callback'), $query );
      $result = mysqli_query( $this->dbconn, $query );

          //echo $query . "\n\n";

      if( false === $result )
      {
        $err_msg = mysqli_error($this->dbconn);
        $this->logger->log("Sys::mysqli_query_params() [DB error]: " . $err_msg, PEAR_LOG_ERR );
        $this->logger->log("Sys::mysqli_query_params() [DB error query]:\n" .  $query, PEAR_LOG_INFO);

        $this->sendEmail(
          $this->logger_email,
          array(
           'From'   => $this->admin_email,
           'To'     => $this->logger_email,
           'Subject'=> 'Email2DB: DB error'),
          "[DB error]: " . $err_msg . "\n" .
          "[DB error query]:\n" . $query
          );

        return false;
      } else {
        return $result;
      }
    }
    else
    {
      $query = preg_replace_callback( '/\$([0-9]+)/', array($this, 'mysqli_query_params__callback'), $query );
      $result = mysqli_query( $this->dbconn, $query, $database );

      if( false === $result )
      {
        $err_msg = mysqli_error($this->dbconn);
        $this->logger->log("Sys::mysqli_query_params() [DB error]: " . $err_msg, PEAR_LOG_ERR );
        $this->logger->log("Sys::mysqli_query_params() [DB error query]:\n" .  $query, PEAR_LOG_INFO);

        $this->sendEmail(
          $this->logger_email,
          array(
           'From'   => $this->admin_email,
           'To'     => $this->logger_email,
           'Subject'=> 'Email2DB: DB error'),
          "[DB error]: " . $err_msg . "\n" .
          "[DB error query]:\n" . $query
          );

        return false;
      } else {
        return $result;
      }
    }

    $this->logger->log("Sys::mysqli_query_params() Undefined error", PEAR_LOG_ERR );
    return false;

  } // END FUNCTION mysqli_query_params

  /**
   * Returns the multidim array as a query result
   *
   * @param $result Resource after mysqli_query
   * @return array
   */
  protected function mysqli_fetch_all($result)
  {
    $res = array();

    while ($row = mysqli_fetch_assoc($result)) {
      $res[] = $row;
    }

    return $res;

  } // END FUNCTION mysqli_fetch_all()

  /**
   * Pings db
   *
   * @see http://it2.php.net/mysqli_ping
   * @param void
   * @return boolean
   * @access public
   */
  protected function isConn()
  {
    if(!is_resource($this->dbconn))
    {
      return mysqli_ping($this->dbconn);
    }

    $this->logger->log("Sys::isConn() no connection to db", PEAR_LOG_INFO );
    return false;
  } // END FUNCTION isConn()

  /**
   * Registeres the messages in the $logger array
   *
   * @param $mess
   * @param $prior
   * @return void
   */
  final protected function logging($mess, $prior = PEAR_LOG_INFO)
  {
    if($mess !== '')
    {
      $this->messages[] = array('message'=>$mess, 'priority'=>prior);
    }
  } // END FUNCTION logging

  /**
   * PHP's default error handler can be overridden using the set_error_handler() function.
   * The custom error handling function can use a global Log instance to log the PHP errors.
   *
   * Note: Fatal PHP errors cannot be handled by a custom error handler at this time.
   *
   * @param $code
   * @param $message
   * @param $file
   * @param $line
   * @return void
   */
  final public function errorHandler($code, $message, $file, $line)
  {
      // if error has been supressed with an @
    if (error_reporting() == 0) {
      return;
    }

    $prefix = "[PHP info] ";

      // Map the PHP error to a Log priority.
    switch ($code)
    {
      case E_WARNING:
      case E_USER_WARNING:
      $priority = PEAR_LOG_WARNING;
      $prefix = "[PHP warning] ";
      break;

      case E_NOTICE:
      case E_USER_NOTICE:
              //$priority = PEAR_LOG_NOTICE;
      $priority = PEAR_LOG_WARNING;
      $prefix = "[PHP notice] ";
      break;

      case E_ERROR:
      case E_USER_ERROR:
      $priority = PEAR_LOG_ERR;
      $prefix = "[PHP error] ";
      break;

      default:
      $priority = PEAR_LOG_INFO;
      return true;
    }
    $this->sendEmail(
      $this->logger_email,
      array(
        'From'   => $this->admin_email,
        'To'     => $this->logger_email,
        'Subject'=> 'Email2DB: ' . $prefix),
      $prefix . $message . ' in ' . $file . ' at line ' . $line
      );

    $this->logger->log($prefix . $message . ' in ' . $file . ' at line ' . $line, $priority);

  } // END FUNCTION errorHandler()

  /**
   * PHP allows user-defined assert() callback handlers.
   * The assertion callback is configured using the assert_options() function.
   *
   * @param $file
   * @param $line
   * @param $message
   * @return void
   */
  final public function assertCallback($file, $line, $message)
  {
    $this->logger->log($message . ' in ' . $file . ' at line ' . $line, PEAR_LOG_ALERT);
  } // END FUNCTION assertCallback()

  /**
   * PHP 5 and later support the concept of exceptions.
   * A custom exception handler can be assigned using the set_exception_handler() function.
   *
   * @param $exception
   * @return void
   */
  final public function exceptionHandler($exception)
  {
    $this->logger->log($exception->getMessage(), PEAR_LOG_ALERT);
  } // END FUNCTION exceptionHandler()

  /**
   * The Log package can be used with PEAR::setErrorHandling()'s PEAR_ERROR_CALLBACK
   * mechanism by writing an error handling function that uses a global Log instance.
   *
   * @param $error
   * @return void
   */
  final public function errorPearHandler($error)
  {
    $message = $error->getMessage();

    if (!empty($error->backtrace[1]['file'])) {
      $message .= ' (' . $error->backtrace[1]['file'];
        if (!empty($error->backtrace[1]['line'])) {
          $message .= ' at line ' . $error->backtrace[1]['line'];
        }
        $message .= ')';
}

$this->logger->log($message, $error->code);
  } // END FUNCTION errorPearHandler()

  /**
   * Sends email
   *
   * @param $recipients
   * @param $headers
   * @param $body
   * @return boolean
   * @access protected
   */
  protected function sendEmail($recipients, $headers, $body)
  {
      // server header signature
    $body =
    "Email2DB ver. $this->version" . "\n" .
    "-------------------"  . "\n" .
    "\n" . $body;

      // server footer signature
    $body .= "\n\nwww.my.net, " .  date(DATE_RFC822) . "\n";

    $msg =
    "Sys::sendEmail() Mail response" .
    "\nTo: " . $headers['To'] .
    "\nFrom: " . $headers['From'] .
    "\nSubject: " . $headers['Subject'] .
    "\nBody\n" .
    $body .
    "\nEndBody";

    $this->logger->log($msg, PEAR_LOG_INFO);

    $headers["Content-Type"] = "text/plain; charset=UTF-8";

    $result = $this->rmail->send($recipients, $headers, $body);

    if( !PEAR::isError($result) ) {
      return true;

    } else {
      $this->logger->log("Sys::sendEmail() [Pear error] Sys::sendEmail() " . $result->getMessage() , PEAR_LOG_ERR );
      return false;
    }

    $this->logger->log("Sys::sendEmail() Undefined error", PEAR_LOG_ERR );

    return false;
  } // END FUNCTION sendEmail()

  /**
   * Test for associative array
   *
   * @param $array
   * @return boolean
   * @access public
   */
  protected function is_assoc($array)
  {
    return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
  } // END FUNCTION is_assoc

  /**
   * PHP validate email
   * http://www.webtoolkit.info/
   *
   * @param $email
   * @return boolean
   * @access protected
   */
  protected function isValidEmail($email)
  {
    return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email);
  } // END FUNCTION isValidEmail()


  /**
   * Executes the shell command and gets stdout and stderr streams
   *
   * @see http://www.php.net/manual/en/function.proc-open.php
   * @param string $cmd - command to be executed
   * @param string $cwd - folder
   * @param array $env - options
   * @return array()
   */
  protected function shexec($cmd, $cwd = './', $env = array())
  {
    $return_value = array(
       "exit"   => 1,       // exit 0 on ok
       "stdout" => "",      // output of the command
       "stderr" => "",      // errors during execution
       );

    $descriptorspec = array(
      0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
      1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
      2 => array("pipe", "w")   // stderr is a pipe
      );

    $process = proc_open(escapeshellcmd($cmd), $descriptorspec, $pipes, $cwd, $env);
      // $pipes now looks like this:
      // 0 => writeable handle connected to child stdin
      // 1 => readable handle connected to child stdout
      // 2 => readable handle connected to child stderr

    if (false === is_resource($process))
    {
      $this->logger->log("Sys::shexec() Error on proc_open, shell command \"$cmd\"" , PEAR_LOG_ERR );
    }
    else
    {
      $return_value['stdout'] = stream_get_contents($pipes[1]);
      $return_value['stderr'] = stream_get_contents($pipes[2]);

      fclose($pipes[0]);
      fclose($pipes[1]);
      fclose($pipes[2]);

          // It is important that you close any pipes before calling
          // proc_close in order to avoid a deadlock
      $return_value['exit'] = proc_close($process);
    }

    if(trim($return_value['stderr']) !== "") {
      $this->logger->log("Sys::shexec() \n\"$cmd\"\nERROR:\n" . $return_value['stderr'], PEAR_LOG_WARNING );
    }

    if(trim($return_value['stdout']) !== "") {
          //$this->logger->log("Sys::shexec() \n\"$cmd\"\nOUTPUT:\n" . $return_value['stdout'], PEAR_LOG_NOTICE );
    }

    return $return_value;

  } // END FUNCTION shexec()

  /**
   * Pulisce nomi dei file e salva nel db
   *
   * @param void
   * @return boolean
   * @access private
   */
  protected function urlize($string)
  {
      // sostituiamo tutti i caratteri speciali it "àìèòù" con "aieou".
      // ATTENZIONE! Codifica del editor PHP dev'essere UTF8
      // Lista caratteri speciali IT: http://italian.about.com/library/blaccentchart.htm
    $new_string = preg_replace(array('/à|á|À|À/', '/ì|í|Ì|Ì/', '/è|é|È|É/', '/ò|ó|Ò|Ó/', '/ù|ú|Ù|Ú/'), array('a', 'i', 'e', 'o', 'u'), $string);
      // sostituiamo tutti i caratteri che non sono alfanumerici con uno spazio
    $new_string = ereg_replace("[^A-Za-z0-9]", " ", $new_string);

      // sostituiamo qualsiasi numero con spazi consecutivi con un "-" della stringa trimmata e abbassata
    $new_string = ereg_replace("[ ]+", "-", strtolower(trim($new_string)) );

    return $new_string;

  } // END FUNCTION urlize();

  /**
   * Return the database current time
   * @return date
   */
  protected function get_db_currtime()
  {
    $now = null;

    $result = $this->mysqli_query("SELECT now() AS currtime");

    if(false !== $result)
    {
      if(mysqli_num_rows($result) == 1)
      {
        $res = $this->mysqli_fetch_all($result);
        return $res[0]['currtime'];
      }
    }
  } // END FUCNTION get_db_currtime()

  /**
   * Excel support functions
   */
  protected function xlsBOF()
  {
    return pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
  }

  protected function xlsEOF()
  {
    return pack("ss", 0x0A, 0x00);
  }

  protected function xlsWriteNumber( $Row, $Col, $Value )
  {
    return pack("sssss", 0x203, 14, $Row, $Col, 0x0) . pack("d", $Value);
  }

  protected function xlsWriteLabel( $Row, $Col, $Value )
  {
    $L = strlen( $Value );
    return pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L) . $Value;
  }

  /**
    * Return human readable sizes
    *
    * @author      Aidan Lister <aidan@php.net>
    * @version     1.3.0
    * @link        http://aidanlister.com/2004/04/human-readable-file-sizes/
    * @param       int     $size        size in bytes
    * @param       string  $max         maximum unit
    * @param       string  $system      'si' for SI, 'bi' for binary prefixes
    * @param       string  $retstring   return string format
    */
   function size_readable($size, $max = null, $system = 'si', $retstring = '%01.2f %s')
   {
      // Pick units
    $systems['si']['prefix'] = array('B', 'K', 'MB', 'GB', 'TB', 'PB');
    $systems['si']['size']   = 1000;
    $systems['bi']['prefix'] = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
    $systems['bi']['size']  = 1024;
    $sys = isset($systems[$system]) ? $systems[$system] : $systems['si'];

      // Max unit to display
    $depth = count($sys['prefix']) - 1;
    if ($max && false !== $d = array_search($max, $sys['prefix'])) {
      $depth = $d;
    }

      // Loop
    $i = 0;
    while ($size >= $sys['size'] && $i < $depth) {
      $size /= $sys['size'];
      $i++;
    }

    return sprintf($retstring, $size, $sys['prefix'][$i]);
  }
} // END CLASS Sys
