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

class Email2DB
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
        $this->Parser = new eXorus\PhpMimeMailParser\Parser();
    }

    /**
     * Parse method
     *
     * @return null
     */
    public function parseEmail($file)
    {
        global $entityManager;

        if(is_file($file)) {
            //There are three input methods of the mime mail to be parsed
            //specify a file path to the mime mail :
            $this->Parser->setPath($file);

            // Or specify a php file resource (stream) to the mime mail :
            $this->Parser->setStream(fopen($file, "r"));

            // We can get all the necessary data
            $subject = $this->Parser->getHeader('subject');
            $message_id = $this->Parser->getHeader('message-id');
            $fromPersonal = imap_rfc822_parse_adrlist($this->Parser->getHeader('from'), '')[0];
            $toPersonal = imap_rfc822_parse_adrlist($this->Parser->getHeader('to'), '')[0];

            $text = $this->Parser->getMessageBody('text');
            $html = $this->Parser->getMessageBody('html');
            $htmlEmbedded = $this->Parser->getMessageBody('htmlEmbedded'); //HTML Body included data

            // and the attachments also
            $attach_dir = '/tmp/';
            $this->Parser->saveAttachments($attach_dir);

            $email = new Email();
            $email->setToEmail($toPersonal->mailbox . '@' . $toPersonal->host);

            if(isset($toPersonal->personal))
                $email->setToName($toPersonal->personal);

            $email->setFromEmail($fromPersonal->mailbox . '@' . $fromPersonal->host);

            if(isset($fromPersonal->personal))
                $email->setFromName($fromPersonal->personal);

            $email->setSubject($subject);
            $email->setMessageId($message_id);
            $email->setReceivedAt(new DateTime($this->Parser->getHeader('date')));
            $email->setCreatedAt(new DateTime('now'));



            $entityManager->persist($email);
            $entityManager->flush();


            echo "Created Email with ID " . $email->getId() . "\n";

            return true;
        }

        return false;
    }

}