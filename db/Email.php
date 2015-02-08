<?php



/**
 * Email
 */
class Email
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $From;

    /**
     * @var string
     */
    private $FromName;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set from
     *
     * @param string $from
     *
     * @return Email
     */
    public function setFrom($from)
    {
        $this->From = $from;

        return $this;
    }

    /**
     * Get from
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->From;
    }

    /**
     * Set fromName
     *
     * @param string $fromName
     *
     * @return Email
     */
    public function setFromName($fromName)
    {
        $this->FromName = $fromName;

        return $this;
    }

    /**
     * Get fromName
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->FromName;
    }
    /**
     * @var string
     */
    private $MessageID;

    /**
     * @var string
     */
    private $To;

    /**
     * @var string
     */
    private $Cc;

    /**
     * @var string
     */
    private $ReplyTo;

    /**
     * @var string
     */
    private $OriginalRecipient;

    /**
     * @var string
     */
    private $Subject;

    /**
     * @var string
     */
    private $Date;

    /**
     * @var string
     */
    private $TextBody;

    /**
     * @var string
     */
    private $HtmlBody;


    /**
     * Set messageID
     *
     * @param string $messageID
     *
     * @return Email
     */
    public function setMessageID($messageID)
    {
        $this->MessageID = $messageID;

        return $this;
    }

    /**
     * Get messageID
     *
     * @return string
     */
    public function getMessageID()
    {
        return $this->MessageID;
    }

    /**
     * Set to
     *
     * @param string $to
     *
     * @return Email
     */
    public function setTo($to)
    {
        $this->To = $to;

        return $this;
    }

    /**
     * Get to
     *
     * @return string
     */
    public function getTo()
    {
        return $this->To;
    }

    /**
     * Set cc
     *
     * @param string $cc
     *
     * @return Email
     */
    public function setCc($cc)
    {
        $this->Cc = $cc;

        return $this;
    }

    /**
     * Get cc
     *
     * @return string
     */
    public function getCc()
    {
        return $this->Cc;
    }

    /**
     * Set replyTo
     *
     * @param string $replyTo
     *
     * @return Email
     */
    public function setReplyTo($replyTo)
    {
        $this->ReplyTo = $replyTo;

        return $this;
    }

    /**
     * Get replyTo
     *
     * @return string
     */
    public function getReplyTo()
    {
        return $this->ReplyTo;
    }

    /**
     * Set originalRecipient
     *
     * @param string $originalRecipient
     *
     * @return Email
     */
    public function setOriginalRecipient($originalRecipient)
    {
        $this->OriginalRecipient = $originalRecipient;

        return $this;
    }

    /**
     * Get originalRecipient
     *
     * @return string
     */
    public function getOriginalRecipient()
    {
        return $this->OriginalRecipient;
    }

    /**
     * Set subject
     *
     * @param string $subject
     *
     * @return Email
     */
    public function setSubject($subject)
    {
        $this->Subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->Subject;
    }

    /**
     * Set date
     *
     * @param string $date
     *
     * @return Email
     */
    public function setDate($date)
    {
        $this->Date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->Date;
    }

    /**
     * Set textBody
     *
     * @param string $textBody
     *
     * @return Email
     */
    public function setTextBody($textBody)
    {
        $this->TextBody = $textBody;

        return $this;
    }

    /**
     * Get textBody
     *
     * @return string
     */
    public function getTextBody()
    {
        return $this->TextBody;
    }

    /**
     * Set htmlBody
     *
     * @param string $htmlBody
     *
     * @return Email
     */
    public function setHtmlBody($htmlBody)
    {
        $this->HtmlBody = $htmlBody;

        return $this;
    }

    /**
     * Get htmlBody
     *
     * @return string
     */
    public function getHtmlBody()
    {
        return $this->HtmlBody;
    }
}
