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
            $to = $this->Parser->getHeader('to');
            $from = $this->Parser->getHeader('from');
            $subject = $this->Parser->getHeader('subject');

            $headers = $this->Parser->getHeaders();

var_dump($headers);

            $text = $this->Parser->getMessageBody('text');
            $html = $this->Parser->getMessageBody('html');
            $htmlEmbedded = $this->Parser->getMessageBody('htmlEmbedded'); //HTML Body included data

            // and the attachments also
            $attach_dir = '/tmp/';
            $this->Parser->saveAttachments($attach_dir);

            var_dump($to);
            var_dump($from);
            var_dump($subject);
            var_dump($text);
            var_dump($htmlEmbedded);

            $email = new Email();
            $email->setTo($to);
            $email->setFrom($from);
            $email->setFrom($subject);
            $email->setMessageId($headers['message-id']);



        try {
            $entityManager->persist($email);
            $entityManager->flush();
        } catch (\PDOException $exception) {
            var_dump(PDOException($exception));
        }



            echo "Created Email with ID " . $email->getId() . "\n";

            return true;
        }

        return false;
    }

}