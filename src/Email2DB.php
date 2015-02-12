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

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class Email2DB extends eXorus\PhpMimeMailParser\Parser
{
    /**
     * Constructor
     *
     * @return null
     */
    public function __construct($config)
    {
        $this->config = $config;
        return $this->init();
    }

    /**
     * Init ORM
     *
     * @return null
     */
    public function init()
    {
        $paths = array("db/schema");
        $isDevMode = true;

        $configDoctrine = Setup::createYAMLMetadataConfiguration($paths, $isDevMode);
        $this->entityManager = EntityManager::create($this->config['db'], $configDoctrine);

        /*
        $entityManager->getConnection()
          ->getConfiguration()
          ->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
        */

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

            if($email = $this->saveEmail()) {
                var_dump($email->getId());
                if($this->saveHeader($email) && $this->saveBody($email) && $this->saveAttachment($email)) {
                    $this->entityManager->flush();
                }
            }
        }

        return false;
    }

    /**
     * Saves Email object
     *
     * @return bool
     */
    public function saveEmail()
    {
        $email = new Email();

        // Get neccesary headers
        $fromPersonal = $this->parseAddrlist($this->getHeader('from'));
        $email->setFromEmail($fromPersonal['email']);
        $email->setFromName($fromPersonal['name']);

        $toPersonal = $this->parseAddrlist($this->getHeader('to'));
        $email->setToEmail($toPersonal['email']);
        $email->setToName($toPersonal['name']);

        $ccPersonal = $this->parseAddrlist($this->getHeader('cc'));
        $email->setCc($ccPersonal['email']);

        $replyToPersonal = $this->parseAddrlist($this->getHeader('reply-to'));
        $email->setReplyTo($replyToPersonal['email']);

        $originalRecipientPersonal = $this->parseAddrlist($this->getHeader('original-recipient'));
        $email->setOriginalRecipient($originalRecipientPersonal['email']);

        $email->setSubject($this->getHeader('subject'));
        $email->setMessageId($this->getHeader('message-id'));

        $email->setReceivedAt(new DateTime($this->getHeader('date')));
        $email->setCreatedAt(new DateTime('now'));


        try {

            $this->entityManager->persist($email);
            $this->entityManager->flush();
            return $email;

        } catch (Exception $e) {
            // TODO log error
            //var_dump($e->getMessage());
            $this->init();
            return false;
        }

        return false;
    }

    /**
     * Parse method
     *
     * @return bool
     */
    public function saveHeader($email)
    {
        $headers = $this->getHeaders();

        if(!is_null($headers) && is_array($headers) && count($headers)>0) {
            foreach ($headers as $name => $value) {
                $header = new Header();
                $header->setName($name);
                $header->setEmail($email);

                if(is_string($value)) {
                    $header->setValue($value);
                } elseif (is_array($value)) {
                    $header->setValue(json_encode($value));
                }

                try {

                    $this->entityManager->persist($header);

                } catch (Exception $e) {
                    // TODO log error
                    //var_dump($e->getMessage());
                    $this->init();
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Parse method
     *
     * @return bool
     */
    public function saveBody($email)
    {
        $body = new Body();

        $body->setEmail($email);
        $body->setContentPlain($this->getMessageBody('text'));
        $body->setContentHtml($this->getMessageBody('html'));

        try {

            $this->entityManager->persist($body);

            return true;

        } catch (Exception $e) {
            // TODO log error
            //var_dump($e->getMessage());
            $this->init();
            return false;
        }

        return false;
    }

    /**
     * Parse method
     *
     * @return bool
     */
    public function saveAttachment($email)
    {
        $attachments = $this->getAttachments();

        foreach ($attachments as $key => $attach) {
            $attachment = new Attachment();
            $attachment->setContentType($attach->getContentType());
            $attachment->setFilename($attach->getFilename());

            $content = $attach->getContent();
            $attachment->setContent($content);

            try {

                $this->entityManager->persist($attachment);

            } catch (Exception $e) {
                // TODO log error
                //var_dump($e->getMessage());
                $this->init();
                return false;
            }
        }

        return true;
    }

    /**
     * Parses the rfc822 address list "Person Name" <email@example.com>
     *
     * @return Array(email address, person name)
     */
    public function parseAddrlist($addrString)
    {
        $personal = imap_rfc822_parse_adrlist($addrString, '');

        if(!is_null($personal) && is_array($personal) && count($personal)>0) {
            $personal = $personal[0];
            return array(
                'email' => isset($personal->mailbox)? $personal->mailbox . '@' . $personal->host : null,
                'name' => isset($personal->personal)? $personal->personal : null
            );
        }

        return array(
            'email' => null,
            'name' => null
        );
    }
}