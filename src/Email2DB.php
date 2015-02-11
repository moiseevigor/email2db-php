<?php
/**
 * Class Email2DB
 * Email2DB parses the email schema into the relational schema.
 *
 * @author Igor Moiseev
 * @see https://github.com/moiseevigor/Email2DB
 *
 *   Copyright (C) 2015 Igor Moiseev
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

class Email2DB extends eXorus\PhpMimeMailParser\Parser
{
    /**
     * @var eXorus\PhpMimeMailParser\Parser
     */
    private $Parser;

    /**
     * Constructor
     *
     * @return null
     */
    public function __construct()
    {
    }

    /**
     * Retrieve a specific Email Header, NULL if does not exist
     *
     * @override \Parser->getHeader()
     * @return String
     * @param $name String Header name
     */
    public function getHeader($name)
    {
        $header = parent::getHeader($name);
        return (false === $header)? null : $header;
    }

    /**
     * Retrieve Headers array
     *
     * @return Mixed or False if not found
     * @return String
     */
    public function getHeaders()
    {
        if (isset($this->parts[1])) {
            $headers = $this->getPart('headers', $this->parts[1]);
            return (is_array($headers)) ? $headers : null;
        } else {
            throw new \Exception(
                'setPath() or setText() or setStream() must be called before retrieving email headers.'
            );
        }

        return null;
    }

    /**
     * Retrieve a specified MIME part
     *
     * @override \Parser->getPart()
     * @return String or Array
     * @param $type String, $parts Array
     */
    public function getPart($type, $parts)
    {
        return (isset($parts[$type])) ? $parts[$type] : null;
    }

    /**
     * Parse method
     *
     * @return null
     */
    public function parseEmail($file)
    {
        if(is_file($file)) {
            //There are three input methods of the mime mail to be parsed
            //specify a file path to the mime mail :
            $this->setPath($file);

            // Or specify a php file resource (stream) to the mime mail :
            $this->setStream(fopen($file, "r"));

            if($this->saveEmail()) {

            }

            var_dump($this->getHeaders());
            die();

            // We can get all the necessary data

            $text = $this->getMessageBody('text');
            $html = $this->getMessageBody('html');
            $htmlEmbedded = $this->getMessageBody('htmlEmbedded'); //HTML Body included data

            // and the attachments also
            $attach_dir = '/tmp/';
            $this->saveAttachments($attach_dir);


            return true;
        }

        return false;
    }

    /**
     * Parse method
     *
     * @return bool
     */
    public function saveEmail()
    {
        global $entityManager;

        $email = new Email();

        // Get neccesary headers
        $fromPersonal = imap_rfc822_parse_adrlist($this->getHeader('from'), '')[0];
        $toPersonal = imap_rfc822_parse_adrlist($this->getHeader('to'), '')[0];
        $ccPersonal = imap_rfc822_parse_adrlist($this->getHeader('cc'), '')[0];
        $replyToPersonal = imap_rfc822_parse_adrlist($this->getHeader('reply-to'), '')[0];
        $originalRecipientPersonal = imap_rfc822_parse_adrlist($this->getHeader('original-recipient'), '')[0];

        $email->setToEmail($toPersonal->mailbox . '@' . $toPersonal->host);

        if(isset($toPersonal->personal))
            $email->setToName($toPersonal->personal);

        $email->setFromEmail($fromPersonal->mailbox . '@' . $fromPersonal->host);

        if(isset($fromPersonal->personal))
            $email->setFromName($fromPersonal->personal);

        $email->setCc($ccPersonal->mailbox . '@' . $ccPersonal->host);
        $email->setReplyTo($replyToPersonal->mailbox . '@' . $replyToPersonal->host);
        $email->setOriginalRecipient($originalRecipientPersonal->mailbox . '@' . $originalRecipientPersonal->host);


        $email->setSubject($this->getHeader('subject'));
        $email->setMessageId($this->getHeader('message-id'));
        $email->setReceivedAt(new DateTime($this->getHeader('date')));
        $email->setCreatedAt(new DateTime('now'));

        $entityManager->persist($email);
        $entityManager->flush();

        echo "Created Email with ID " . $email->getId() . "\n";

        return true;
    }

    /**
     * Parse method
     *
     * @return bool
     */
    public function saveHeader($file)
    {
    }

    /**
     * Parse method
     *
     * @return bool
     */
    public function saveBody($file)
    {
    }

    /**
     * Parse method
     *
     * @return bool
     */
    public function saveAttachement($file)
    {
    }
}