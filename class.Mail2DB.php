<?php
/**
 * Class Mail2DB
 * @author Igor Moiseev
 */

class Mail2DB extends Sys
{
    /**
     * Login datas for local pop account
     * @var string
     * @access private
     */
    private $loc_pop = array(
        "host" => "{localhost:110/pop3}",
        "user" => "user",
        "pass" => "pass");

    /**
     * Is init var
     * @var boolean
     * @access private
     */
    private $isInit = false;

    /**
     * Lock file for script to prevent multiple execution
     * @var sting
     * @access protected
     */
    private $lock_file = "/var/lock/mail2db.lock";

    /**
     * List of valid formats for system Mail2Fax of Faxfacile from "mail2fax_subtype"
     * "PDF", "OCTET_STREAM", "MSWORD"
     *
     * @var array
     * @access private
     */
    private $formats = array();

    /**
     * The resource of the Pop connection
     * @var resource
     * @access private
     */
    private $mbox = null;

    /**
     * The number of messages in the mailbox
     * @var integer
     * @access private
     */
    private $num_msg = 0;

    /**
     * An array of recieved attachements to be inserted into "faxserver.mail2fax_attachements"
     * @access private
     * @var array
     */
    private $attachs = array();

    /**
     * An array of recieved destination per fax to be inserted into "faxserver.mail2fax_destinatari"
     * @access private
     * @var array
     */
    private $dests = array();

    /**
     * Indirizzo mail del mittente
     * @var string
     * @access private
     */
    private $from_email = '';

    /**
     * The id of email log record buffer var
     * @var integer
     * @access private
     */
    private $email_log_id = 0;

    /**
     * The log id for the message in the mail2fax_log, buffer
     * @var integer
     * @access private
     */
    private $id_log = 0;

    /**
     * An array of recieved emails to be inserted into "faxserver.mail2fax_emails"
     * @access private
     * @var array
     */
    private $emails = array();

    /**
     * Destructor of the class Mail2DB
     * @param void
     * @return boolean
     * @access public
     */
    public function __destruct()
    {
        if($this->isInit) {
            $this->logger->log("Mail2DB::__destruct() Mail2DB exited", PEAR_LOG_NOTICE);
        }

        if(is_file($this->lock_file)) {
            unlink($this->lock_file);
        }

        if(is_resource($this->mbox)){
            imap_errors();
            imap_close($this->mbox);
        }

        parent::__destruct();

        return true;

    } // END FUNCTION __destruct()

    /**
     * Contructor of the class Mail2DB
     *
     * @param void
     * @return void
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
    } // END FUNCTION  __construct()

    /**
     * Manages the class Mail2DB
     *
     * @param void
     * @return boolean
     * @access public
     */
    public function manage()
    {
        if( parent::manage(array("log_table" => "mail2fax_log")) && $this->init()) {
            return $this->parse_emails();
        }

        return false;

    } // END FUNCTION manage()

    /**
     * Opens connection to mailbox via pop
     *
     * @param void
     * @return boolean
     * @access public
     */
    private function init()
    {
        if($this->isInit)
        {
            return true;
        }
        else
        {
            $this->isInit = true;

            if(is_file($this->lock_file))
            {
                $this->logger->log("Mail2DB::init() Found lock file $this->lock_file, script make long sleep now", PEAR_LOG_ERR);
                $this->sendEmail(
                $this->logger_email,
                array(
               'From'   => $this->admin_email,
               'To'     => $this->logger_email,
               'Subject'=> 'FAX FACILE: Errore sull\'avvio Mail2DB'),
	            "Found lock file $this->lock_file, script long sleeps now."
                );
                die();
                return false;
            }

            $this->logger->log("Mail2DB::init() Mail2DB started", PEAR_LOG_NOTICE);
            touch($this->lock_file);

            return true;
        }

        $this->logger->log("Mail2DB::init() Undefined error", PEAR_LOG_ERROR);
        return false;
    } // END FUNCTION init()

    /**
     * Initilizes the imap connection
     *
     * @return boolean
     * @access private
     */
    private function init_imap()
    {
        $this->mbox = imap_open($this->loc_pop["host"], $this->loc_pop["user"], $this->loc_pop["pass"]);

        if(false === $this->mbox)
        {
            $this->logger->log("Mail2DB::init() Cannot connect to mailbox: " . imap_last_error(), PEAR_LOG_ERR);
            return false;
        }
        else
        {
            $this->num_msg = imap_num_msg($this->mbox);
            if($this->num_msg >= $this->warn_limit_email) {
                $this->sendEmail(
                $this->logger_email,
                array(
               'From'   => $this->admin_email,
               'To'     => $this->logger_email,
               'Subject'=> 'FAX FACILE: Warning class Mail2DB'),
	            "Superato numero massimo $this->warn_limit_email del'email in attesa per classe Mail2DB"
                );
            }

            return true;
        }
    } // END FUNCTION init_imap()

    /**
     * Parsing emails and register them to arrays
     *
     * @return boolean
     * @param void
     * @access private
     */
    private function parse_emails()
    {
        $to_index = array();

        if($this->init_imap())
        {
            if( $this->num_msg>0 )
            {
                for ($i = 1; ($i <= $this->num_msg && $i <= $this->limit_call_email); $i++)
                {
                    $this->dests = array();
                    $this->attachs = array();
                    $this->email_log_id = 0;
                    $this->id_log = 0;

                    $structure = imap_fetchstructure($this->mbox, $i , FT_UID);
                    $header    = imap_headerinfo($this->mbox, $i);
                    imap_delete($this->mbox, $i);

                    if(isset($header->message_id) && $this->check_message_id($header->message_id) && $this->log_email($i, $header, $structure) && $this->validate_header($header) && $this->validate_structure($structure, $i))
                    {
                        if( !isset($to_index[$header->message_id]) ) {
                            $to_index[$header->message_id] = 0;

                        } else {
                            $to_index[$header->message_id]++;
                            continue;
                        }

                        $this->from_email = $header->from[0]->mailbox . '@' . $header->from[0]->host;
                        $this->emails[$i] = array(
                         "id"              => 0,                               // email id from "mail2fax_emails.id"
                         "id_cliente"      => 0,                               // id of client from "mail2fax_emails.id_cliente"
                         "user_id"         => 0,                               // user id from "mail2fax_emails.user_id"
                         "state_mail2db"   => 99,                              // state of email ellaboration, "99: block on email ellaboration"
                         "email_log_id"    => $this->email_log_id,             // log id from "mail2fax_emails.email_log_id"
                         "message_id"      => $header->message_id,             // unique id of message from remote server
                         "multiple_attach" => false,                           // message with multiple attachements
                         "multiple_email"  => false,                           // message with multiple addresses
                         "ins_date"        => '0000-00-00 00:00:00',           // insertion into "mail2fax_emails" date
                         "from_number"     => '0',                             // from the phone number
                         "from_mailbox"    => $header->from[0]->mailbox,       // mailbox name
                         "from_host"       => $header->from[0]->host,          // host of the user
                         "from_personal"   => $header->from[0]->personal,      // user real name
                         "from_email"      => $this->from_email,               // email of user
                         "subject"         => $header->Subject,                // pin
                         "retry"           => 0,                               // number of retries
                         "callerid"        => '',                              // callerid
                        );

                        if(count($this->dests)>1) {
                            $this->emails[$i]['multiple_email'] = true;
                        }

                        if(count($this->attachs)>1) {
                            $this->emails[$i]['multiple_attach'] = true;
                        }

                        // MAIN procedures of validation and insertion
                        if($this->insert_log() && $this->insert_email($i) && $this->process_email($i)) {
                            $this->emails[$i]['state_mail2db'] = 0;
                        } // END IF ($this->insert_email)

                        $this->update_email_status($i);
                        $this->update_log($i);

                        usleep(5000000); // sleep 0.5 sec

                    } // END IF ($this->validate_header($header) && $this->validate_structure($structure))
                } // END FOR ($i = 1; $i <= imap_num_msg($this->mbox); $i++)
            } // END IF ( $this->num_msg>0 )

            imap_expunge($this->mbox);
            imap_errors();

            return imap_close($this->mbox);

        } // END IF ($this->init_imap())

        return false;

    } // END FUNCTION parse_emails()


    /**
     * Checks whether the message_id is already present in the DB
     *
     * @var $mess_id message id to check
     * @access private
     * @return boolean
     */
    private function check_message_id($mess_id)
    {
        $result = $this->mysqli_query_params(
            "SELECT message_id FROM mail2fax_emails WHERE message_id = $1",
        array($mess_id)
        );

        if(false !== $result)
        {
            if(mysqli_num_rows($result) == 0)
            {
                return true;
            }
            elseif(mysqli_num_rows($result) == 1)
            {
                //$this->logger->log("Mail2DB::check_message_id() WARNING: the message_id: $mess_id is already present in the DB" , PEAR_LOG_WARNING);
                return false;
            }
        }

        $this->logger->log("Mail2DB::check_message_id() WARNING: DB error" , PEAR_LOG_WARNING);
        return false;
    } // END FUNCTION check_message_id

    /**
     * Process single email with valid structure
     *
     * @var $i and index of email to process
     * @access private
     * @return boolean
     */
    private function process_email($i)
    {
        $this->emails[$i]['state_mail2db'] = 2;

        if( $this->validate_user($i, $this->emails[$i]['from_email'], $this->emails[$i]['subject']) )
        {
            if( $this->validate_dests($i) && $this->validate_attachs($i) )
            {
                if($this->insert_attach($i) && $this->insert_dest($i))
                {
                    return true;
                }
            }
        } // END IF ELSE (!$this->validate_user())

        return false;

    } // END FUNCTION process_email()

    /**
     * Logs recieved emails into "faxserver.mail2fax_log_email"
     *
     * @param object $header
     * @param object $structure
     * @return boolean
     * @access private
     */
    private function log_email($i, $header, $structure)
    {
        $email = imap_fetchheader($this->mbox, $i, FT_INTERNAL) . imap_body($this->mbox, $i, FT_INTERNAL);

        $result = $this->mysqli_query_params(
         "INSERT INTO mail2fax_log_email
            (
               header,
               structure,
               email
            )
         VALUES ($1, $2, $3 )",
        array( var_export($header,true), var_export($structure,true), $email) );

        if(false !== $result)
        {
            $this->email_log_id = mysqli_insert_id($this->dbconn);

            return true;
        }

        $this->logger->log("Mail2DB::log_email() WARNING: Error logging email" , PEAR_LOG_WARNING);
        return false;

    } // END FUNCTION log_email()

    /**
     * Inserts the recieved email into "faxserver.mail2fax_emails"
     * @param void
     * @return boolean
     */
    private function insert_email($i)
    {
        $this->emails[$i]['state_mail2db'] = 1;

        $result = $this->mysqli_query_params(
         "INSERT INTO mail2fax_emails
            (
               email_log_id,
               message_id,
               multiple_attach,
               multiple_email,
               from_mailbox,
               from_host,
               from_personal,
               from_email,
               subject
            )
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9 )",
        array(
        $this->emails[$i]['email_log_id'],
        $this->emails[$i]['message_id'],
        $this->emails[$i]['multiple_attach'],
        $this->emails[$i]['multiple_email'],
        $this->emails[$i]['from_mailbox'],
        $this->emails[$i]['from_host'],
        $this->emails[$i]['from_personal'],
        $this->emails[$i]['from_email'],
        $this->emails[$i]['subject'])
        );

        if(false !== $result)
        {
            $this->emails[$i]['id'] = mysqli_insert_id($this->dbconn);
            $res = $this->mysqli_query_params("SELECT * FROM mail2fax_emails WHERE id = $1", array($this->emails[$i]['id']));

            if(false !== $res)
            {
                $email = $this->mysqli_fetch_all($res);

                if(is_null($email[0]['user_id']))
                {
                    $this->logger->log("Mail2DB::insert_email() NOTICE: User error: $this->from_email (mail2fax_log_email.id: $this->email_log_id)" , PEAR_LOG_WARNING);
                    $this->sendEmail(
                    $this->logger_email,
                    array(
                  'From'   => $this->admin_email,
                  'To'     => $this->logger_email,
                  'Subject'=> "FAX FACILE: Errore Mail2DB::insert_email() NOTICE: User error: $this->from_email (mail2fax_log_email.id: $this->email_log_id)"),
                  "Mail2DB::insert_email() NOTICE: User error: $this->from_email (mail2fax_log_email.id: $this->email_log_id). It seems that the user doesnot exist in DB."
                    );

                    if( count($this->dests)>0 && ctype_digit(implode('',$this->dests)) )
                    {
                        $testo =
                        "Numero fax del/i destinatario/i: " . implode(', ', $this->dests) . "\n" .
                        "Email del mittente: " .            $this->emails[$i]['from_email'] . "\n" .
                        "Data invio fax: " .                date( "d/m/Y" ) . "\n" .
                        "Ora invio fax: " .                 date( "H:i:s" ) . "\n" .
                        "\n" .
                        "La mail da Lei inviata non è stata elaborata per l'invio poiché non è autorizzata all'invio.\n" .
                        "La preghiamo di consultare la guida e configurare le necessarie opzioni da pannello web:\n" .
                        "http://www.faxfacile.net/docs/FAX_FACILE_GUIDA_rev_1.pdf" . "\n";

                        $this->sendEmail(
                        $this->from_email,
                        array(
                        'From'   => $this->admin_email,
                        'To'     => $this->from_email,
                        'Subject'=> 'FAX FACILE: invio mail2fax non accettato. Fax non inviato.'),
                        $testo);
                    }

                    $this->emails[$i]['state_mail2db'] = 3;

                    return false;
                }
                else
                {
                    $this->emails[$i]['user_id'] = (int)$email[0]['user_id'];
                    $this->emails[$i]['ins_date'] = $email[0]['ins_date'];
                    return true;
                }
            }
        }

        $this->logger->log("Mail2DB::insert_email() WARNING: Error ellaboration email (mail2fax_log_email.id: $this->email_log_id)" , PEAR_LOG_WARNING);
        return false;

    } // END FUNCTION insert_emails

    /**
     * Updates the mail2fax_emails.state_db2fax
     *
     * @param integer $i
     * @return boolean
     */
    private function update_email_status($i)
    {
        if($this->emails[$i]['state_mail2db'] == 0 && count($this->dests)>0 )
        {
            $dests = array();
            foreach($this->dests as $d) {
                $dests[] = "'$d'";
            }
            $nums = implode(', ', $dests);

            $result = $this->mysqli_query_params("
                SELECT
                	count(*) AS num_active_fax
            	FROM fetchmail_mail2fax AS fm
            	WHERE
            			fm.mail2fax_dest_num IN ($nums)
            		AND fm.id_mail2fax_cliente = $1
                	AND fm.`status` = '99'
                	AND fm.id_mail2fax_email <> $2
                    AND fm.insert_date > now() - INTERVAL 1 HOUR",
            array($this->emails[$i]['id_cliente'], $this->emails[$i]['id']));

            if(false !== $result)
            {
                $res = $this->mysqli_fetch_all($result);
                $num_active_fax = (int)$res[0]['num_active_fax'];

                if($num_active_fax == 0) {
                    // do_send = last_send + 60 sec + (count-1)*20
                } elseif($num_active_fax>0) {
                    // do_send = last_send + 20 min
                    $secs = 1200 * $num_active_fax;
                    $resQuery=$this->mysqli_query_params("
                            UPDATE mail2fax_emails
                            SET do_send = now() + INTERVAL $secs SECOND
                            WHERE id = $1",
                    array($this->emails[$i]['id']));
                }
            }
        } // END IF $this->emails[$i]['state_mail2db'] == 0

        $resQuery=$this->mysqli_query_params("
            UPDATE mail2fax_emails
            SET state_mail2db = $1
            WHERE id = $2",
        array($this->emails[$i]['state_mail2db'], $this->emails[$i]['id']));

        if(false !== $resQuery) {
            return true;
        }

        return false;

    } // END FUNCTION update_email_status()

    /**
     * Inserts the recived attachments into "faxserver.mail2fax_attachments"
     *
     * @param $i
     * @return boolean
     */
    private function insert_attach($i)
    {
        foreach($this->attachs as $key => $attach)
        {
            $result = $this->mysqli_query_params(
             "INSERT INTO mail2fax_attachments
                (
                   email_id,
                   filename,
                   type,
                   ext,
                   subtype,
                   body
                )
             VALUES ( $1, $2, $3, $4, $5, $6 )",
            array(
            $this->emails[$i]['id'],
            $attach['filename'],
            $attach['type'],
            $attach['ext'],
            $attach['subtype'],
            $attach['body'])
            );

            if(false === $result) {
                $this->emails[$i]['state_mail2db'] = 10;
                $this->logger->log("Mail2DB::insert_attach() WARNING: Error of DB on attachment insert (mail2fax_log_email.id: $this->email_log_id)" , PEAR_LOG_WARNING);
                return false;
            }
        } // END FOREACH

        return true;

    } // END FUNCTION insert_attach

    /**
     * Inserts the recived attachments into "faxserver.mail2fax_destinatari"
     * @param $i
     * @return boolean
     */
    private function insert_dest($i)
    {
        foreach($this->dests as $key => $dest)
        {
            $result = $this->mysqli_query_params(
             "INSERT INTO mail2fax_destinatari
                (
                   mail_id,
                   num
                )
             VALUES ( $1, $2 )",
            array(
            $this->emails[$i]['id'],
            $dest)
            );

            if(false === $result) {
                $this->emails[$i]['state_mail2db'] = 11;
                $this->logger->log("Mail2DB::insert_dests() WARNING: Error of DB on destinatari insert (mail2fax_log_email.id: $this->email_log_id)" , PEAR_LOG_WARNING);
                return false;
            }
        } // END FOREACH

        return true;

    } // END FUNCTION insert_dest

    /**
     * Converts message from email encoding to binary
     *
     *  $coding[0] = "text";
     *  $coding[1] = "multipart";
     *  $coding[2] = "message";
     *  $coding[3] = "application";
     *  $coding[4] = "audio";
     *  $coding[5] = "image";
     *  $coding[6] = "video";
     *  $coding[7] = "other";
     *
     * @return string
     * @access private
     */
    private function getdecodevalue($message,$coding)
    {
        if ($coding == 0)
        {
            $message = imap_8bit($message);
        }
        elseif ($coding == 1)
        {
            $message = imap_8bit($message);
        }
        elseif ($coding == 2)
        {
            $message = imap_binary($message);
        }
        elseif ($coding == 3)
        {
            $message = imap_base64($message);
        }
        elseif ($coding == 4)
        {
            $message = imap_qprint($message);
        }
        elseif ($coding == 5)
        {
            $message = imap_base64($message);
        }

        return $message;

    } // END FUNCTION getdecodevalue

    /**
     * Validation of the email header
     *
     * @param $header
     * @access private
     * @return boolean
     */
    private function validate_header($header)
    {
        $conditions = array(
        1 => null,
        2 => null,
        3 => null,
        4 => null,
        5 => null,
        6 => null,
        );

        if(
        ($conditions[1] = isset($header->message_id)) &&
        ($conditions[2] = (isset($header->from) && is_array($header->from))) &&
        ($conditions[3] = isset($header->from[0]->mailbox)) &&
        ($conditions[4] = isset($header->from[0]->host)) &&
        ($conditions[5] = isset($header->Subject)) &&
        ($conditions[6] = (isset($header->to) &&  is_array($header->to)))
        ) {
            if(!isset($header->from[0]->personal)) {
                $header->from[0]->personal = "Unnamed User";
            }

            foreach($header->to as $to)
            {
                if( isset($to->mailbox) ) {
                    $this->dests[] = $to->mailbox;
                }
            }
            if(count($this->dests)>0) {
                return true;
            } else {
                $this->logger->log("Mail2DB::validate_header() WARNING: No valid destinations found (mail2fax_log_email.id: $this->email_log_id)", PEAR_LOG_WARNING);
                return false;
            }
        } // END IF (conditions ...

        $this->logger->log("Mail2DB::validate_header() WARNING: The header of email (mail2fax_log_email.id: $this->email_log_id) is not compatible with the requested one. Conditions: " . serialize($conditions), PEAR_LOG_WARNING);
        return false;

    } // END FUNCTION validate_header()

    /**
     * Validation of the email structure
     *
     * @param $structure
     * @access private
     * @return boolean
     */
    private function validate_structure($structure, $i, $section = '')
    {
        $conditions = array(
        1 => null,
        2 => null,
        3 => null,
        );

        if(
        ($conditions[1] = is_object($structure)) &&
        ($conditions[2] = (isset($structure->type) && is_int($structure->type)) ) &&
        ($conditions[3] = isset($structure->subtype))
        ) {
            switch(strtoupper($structure->subtype))
            {
                // complex email with boundaries
                case 'MIXED':
                    if(isset($structure->parts) && is_array($structure->parts))
                    {
                        // iteration by parts
                        foreach($structure->parts as $key => $part) {
                            $this->validate_structure($part, $i, $section . "." . ($key+1));
                        }

                        if(count($this->attachs)>0) {
                            return true;
                        } else {
                            $this->logger->log("Mail2DB::validate_structure() The MIXED email without attachments (mail2fax_log_email.id: $this->email_log_id)", PEAR_LOG_NOTICE);
                            return false;
                        }
                    } else {
                        $this->logger->log("Mail2DB::validate_structure() WARNING: The MIXED email without parts (mail2fax_log_email.id: $this->email_log_id)", PEAR_LOG_WARNING);
                        return false;
                    }
                    break;

                    // we obtained uninteresting attachments: {'ALTERNATIVE', 'RELATED'}
                    // take NO action on it
                case 'ALTERNATIVE':
                case 'RELATED':
                    return false;
                    break;

                    // simple email without boundaries changes for different mail progs 'MSWORD', 'OCTET-STREAM', 'PDF' etc
                default:
                    if(isset($structure->disposition) && (strtoupper($structure->disposition) === 'ATTACHMENT' || strtoupper($structure->disposition) === "INLINE") )
                    {
                        if(isset($structure->dparameters) && is_array($structure->dparameters) && isset($structure->parameters) && is_array($structure->parameters))
                        {
                            foreach($structure->parameters as $value)
                            {
                                if(isset($value->attribute) && $value->attribute === 'NAME')
                                {
                                    // for emails with attachemnts only, ex. sent from M$WORD
                                    if($section === '') {
                                        $section = '.1';
                                    }

                                    // attachment index starts from 2
                                    // @see http://tools.ietf.org/html/rfc3501#section-6.4.5
                                    // @see http://www.daniweb.com/forums/thread145270.html#
                                    $body = "";
                                    $body = imap_fetchbody($this->mbox, $i, substr($section, 1), FT_INTERNAL);
                                    $body = $this->getdecodevalue($body, $structure->encoding);
                                    $filename = imap_utf8($value->value);

                                    $this->attachs[] = array(
                                      'filename'  => $filename,
                                      'type'      => $structure->type,
                                      'subtype'   => $structure->subtype,
                                      'ext'       => strtoupper(pathinfo($filename, PATHINFO_EXTENSION)),
                                      'section'   => substr($section, 1),
                                      'body'      => $body,
                                    );

                                } else {
                                    $this->logger->log("Mail2DB::validate_structure() Unsupported attribute '$value->attribute' !== 'NAME' in the email (mail2fax_log_email.id: $this->email_log_id).", PEAR_LOG_INFO);
                                }
                            } // END FOREACH ($structure->parameters as $value)

                            if(count($this->attachs)>0) {
                                return true;
                            }
                        } else {
                            $this->logger->log("Mail2DB::validate_structure() The structure of email (mail2fax_log_email.id: $this->email_log_id) is not compatible with requested one. On is_array(structure->dparameters).", PEAR_LOG_NOTICE);
                            return false;
                        }
                    } else {
                        //$this->logger->log("Mail2DB::validate_structure() The structure of email (mail2fax_log_email.id: $this->email_log_id) is not compatible with requested one. On strtoupper(structure->disposition) === 'INLINE'.", PEAR_LOG_INFO);
                        return false;
                    }
                    break;

            } // END SWITCH (strtoupper($structure->subtype))
        } // END IF ($conditions ...

        $this->logger->log("Mail2DB::validate_structure() WARNING: The structure of email (mail2fax_log_email.id: $this->email_log_id) is not compatible with requested one. Conditions: " . serialize($conditions), PEAR_LOG_WARNING);
        return false;

    } // END FUNCTION validate_structure()

    /**
     * Validates destinatari
     *
     * @param void
     * @access private
     * @return boolean
     */
    private function validate_dests($i)
    {
        $this->emails[$i]['state_mail2db'] = 4;

        if(count($this->dests) > $this->warn_limit_destinatari)
        {
            $this->logger->log("Mail2DB::validate_dests() WARNING: Superato il numero massimo $this->warn_limit_destinatari dei deistinatari per l'utente: $this->from_email, (mail2fax_log_email.id: $this->email_log_id)", PEAR_LOG_NOTICE);
            $this->sendEmail(
            $this->from_email,
            array(
            'From'   => $this->admin_email,
            'To'     => $this->from_email,
            'Subject'=> 'FAX FACILE: Errore numero destinatari. Fax non inviato.'),
            "Superato il numero massimo $this->warn_limit_destinatari dei deistinatari per l'utente: $this->from_email"
            );

            $this->emails[$i]['state_mail2db'] = 5;
            return false;
        }
        elseif(count($this->dests)>0)
        {
            foreach($this->dests as $key_dest => $dest)
            {
                $dest = trim($dest);
                $this->dests[$key_dest] = $dest;

                if(!ctype_digit($dest))
                {
                    $this->logger->log("Mail2DB::validate_dests() Abbiamo ricevuto la richiesta del invio fax sul numero errato $dest dal email: $this->from_email", PEAR_LOG_NOTICE);

                    $this->sendEmail(
                    $this->from_email,
                    array(
                      'From'   => $this->admin_email,
                      'To'     => $this->from_email,
                      'Subject'=> "FAX FACILE: Errore numero fax. Il fax non e' stato inviato."),
                      "Il sistema accetta le richieste del invio fax nel formatto: {numero_a_cui_inviare_fax}@faxfacile.net\n\n" .
                      "Es. chiamate nazionale: 0401111111@faxfacile.net\n" .
                      "Es. chiamate internazionale: 00390401111111@faxfacile.net\n"
                      );
                      $this->emails[$i]['state_mail2db'] = 6;
                      return false;
                }
            } // END FOREACH
            return true;
        } // END ELSEIF (count($this->dests)>0)

        $this->logger->log("Mail2DB::validate_dests() Destinations validation failed (mail2fax_log_email.id: $this->email_log_id)", PEAR_LOG_NOTICE );
        return false;
    } // END FUNCTION validate_dests()

    /**
     * Validates attachments to be compatible with the suppoted types
     *
     * @param void
     * @access private
     * @return boolean
     */
    private function validate_attachs($i)
    {
        $this->emails[$i]['state_mail2db'] = 9;

        $result_formats = $this->mysqli_query_params("SELECT ext, subtype FROM mail2fax_subtype WHERE is_impl = '1'");

        if(false !== $result_formats)
        {
            $formats = $this->mysqli_fetch_all($result_formats);

            foreach($formats as $form){
                $this->formats[] = $form['ext'];
            }

            if(count($this->attachs) > $this->warn_limit_attachs)
            {
                $this->logger->log("Mail2DB::validate_attachs() WARNING: Superato il numero massimo $this->warn_limit_attachs dei allegati per l'utente: $this->from_email (mail2fax_log_email.id: $this->email_log_id)", PEAR_LOG_NOTICE);
                $this->sendEmail(
                $this->from_email,
                array(
               'From'   => $this->admin_email,
               'To'     => $this->from_email,
               'Subject'=> 'FAX FACILE: Errore numero allegati. Fax non inviato'),
	            "Superato il numero massimo $this->warn_limit_attachs dei allegati per l'utente: $this->from_email"
                );

                $this->emails[$i]['state_mail2db'] = 7;
                return false;
            }
            elseif(count($this->attachs)>0)
            {
                foreach($this->attachs as $attach)
                {
                    // TODO IMPLEMENT CHECK EXTENSION on the base mail2fax_subtype
                    // http://php.net/manual/en/function.pathinfo.php

                    if( !in_array(strtoupper($attach['ext']), $this->formats) )
                    {
                        $this->logger->log(
                         "Mail2DB::validate_attachs() Abbiamo ricevuto la richiesta del invio fax (" . $attach['filename'] . ") " .
                         "nel formato non supportato " . $attach['subtype'] .
                         " dal'utente: $this->from_email (mail2fax_log_email.id: $this->email_log_id)", PEAR_LOG_NOTICE);

                        $this->sendEmail(
                        $this->from_email,
                        array(
                         'From'   => $this->admin_email,
                         'To'     => $this->from_email,
                         'Subject'=> 'FAX FACILE: Errore formatto allegato. Fax non inviato.'),
                         "Il sistema accetta documenti per l'invio via fax solo nei formatti: " .
    		             "TIFF, MSG, PDF, PUB, PUBX, XLSX, XLS, RTF, TIF, JPG, JPEG, BMP, CSV, DOC, DOCX, EML, GIF, ICO, J2K, JP2, JPC.\n" .
                         "Il nome del file NON deve contenere caratteri speciali o lettere accentate.\n" .
                         "Allegato: " . $attach['filename']
                        );
                        $this->emails[$i]['state_mail2db'] = 8;
                        return false;
                    }

                    if( strlen($attach['body']) <= 0 )
                    {
                        $this->logger->log("Mail2DB::validate_attachs() We recieved the empty attach or cannot select the section: \"" . $attach['section'] . "\" from email: $this->from_email (mail2fax_log_email.id: $this->email_log_id)", PEAR_LOG_NOTICE);
                        $this->emails[$i]['state_mail2db'] = 8;
                        return false;
                    }

                } // END FORECH ($this->attachs as $attach)

                return true;

            } // END ELSEIF
        } // END IF (false !== $result_formats)

        $this->logger->log("Mail2DB::validate_attachs() Attachements validation failed (mail2fax_log_email.id: $this->email_log_id)", PEAR_LOG_NOTICE );
        return false;
    } // END FUNCTION validate_attachs()

    /**
     * Validates the user in front of the db record
     *
     * @param $i
     * @param $email
     * @param $pin
     * @return boolean
     * @access private
     */
    private function validate_user($i, $email, $pin)
    {
        $this->emails[$i]['state_mail2db'] = 3;

        if( $this->isValidEmail($email) )
        {
            $result = $this->mysqli_query_params(
            "SELECT * FROM email_abilitate WHERE email = $1 AND pin = $2",
            array($email, $pin)
            );

            if(false !== $result)
            {
                if(mysqli_num_rows($result) === 0)
                {
                    $result_email = $this->mysqli_query_params(
                  "SELECT * FROM email_abilitate WHERE email = $1",
                    array($email)
                    );

                    if(false !== $result_email && mysqli_num_rows($result_email) === 1)
                    {
                        $this->sendEmail(
                        $this->from_email,
                        array(
                         'From'   => $this->admin_email,
                         'To'     => $this->from_email,
                         'Subject'=> 'FAX FACILE: Errore autenticazione. Fax non inviato.'),
                         "Errore di autenticazione. Controllare il PIN dell'utente: $this->from_email"
                        );
                    }
                }
                elseif(mysqli_num_rows($result) === 1)
                {
                    $user = $this->mysqli_fetch_all($result);

                    $this->emails[$i]['from_number'] = $user[0]['numerazione'];
                    $this->emails[$i]['id_cliente']  = $user[0]['id_cliente'];

                    $query = 'SELECT retry, callerid FROM numerazioni WHERE idcliente= $1';
                    $result_num = $this->mysqli_query_params($query, array($this->emails[$i]['id_cliente']));

                    if(false !== $result_num)
                    {
                        $res = $this->mysqli_fetch_all($result_num);
                        $this->emails[$i]['retry']    = $res[0]['retry'];
                        $this->emails[$i]['callerid'] = $res[0]['callerid'];

                        $result_ea = $this->mysqli_query_params(
                            "UPDATE email_abilitate SET tot_fax = tot_fax + 1 WHERE email = $1 AND pin = $2",
                        array($email, $pin)
                        );

                        $res_emails = $this->mysqli_query_params(
                          "
                             UPDATE mail2fax_emails
                             SET
                                from_number = $1,
                                id_cliente = $2,
                                retry = $3,
                                callerid = $4
                             WHERE id = $5",
                        array(
                        $this->emails[$i]['from_number'],
                        $this->emails[$i]['id_cliente'],
                        $this->emails[$i]['retry'],
                        $this->emails[$i]['callerid'],
                        $this->emails[$i]['id'],)
                        );

                        if(false !== $res_emails) {
                            return true;
                        }
                    } // END IF (false !== $result_num)
                } // END ELSEIF (mysqli_num_rows($result) === 1)
            } // END IF (false !== $result && mysqli_num_rows($result) === 1)
        } // END IF ( $this->isValidEmail($email) )

        $this->logger->log("Mail2DB::validate_user() Authentication failed (mail2fax_log_email.id: $this->email_log_id)", PEAR_LOG_NOTICE );
        return false;

    } // END FUNCTION validate_user()

    /**
     * Inserts into the mail2fax_log
     *
     * @param integer $i
     * @return boolean
     */
    private function insert_log()
    {
        if( $this->logger->log('Mail2DB::insert_log() Init log', PEAR_LOG_INFO) )
        {
            $this->id_log = mysqli_insert_id($this->dbconn);

            if( $this->id_log>0 ){
                return true;
            }
        }

        return false;

    } // END FUNCTION insert_log()

    /**
     * Updates the mail2fax_log
     *
     * @param integer $i
     * @return boolean
     */
    private function update_log($i)
    {
        if($this->id_log > 0)
        {
            $priority = PEAR_LOG_INFO;
            if($this->emails[$i]['state_mail2db']>0){
                $priority = PEAR_LOG_WARNING;
            }

            $resQuery=$this->mysqli_query_params("
            UPDATE mail2fax_log
            SET
               priority = $2,
               message = $3
            WHERE id = $1",
            array(
            $this->id_log,
            $priority,
            "Mail2DB::update_log() " .
               "id_log_email: " .   $this->emails[$i]['email_log_id'] . "; " .
               "id_email: " .       $this->emails[$i]['id'] .  "; " .
               "from_email: " .     $this->emails[$i]['from_email'] .  "; " .
               "state_mail2db: " .  $this->emails[$i]['state_mail2db'])
            );

            if(false !== $resQuery) {
                return true;
            }
        }

        return false;

    } // END FUNCTION update_log()

} // END CLASS Mail2DB

?>
