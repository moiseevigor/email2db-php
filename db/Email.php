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
    private $message_id;

    /**
     * @var string
     */
    private $from_email;

    /**
     * @var string
     */
    private $from_name;

    /**
     * @var string
     */
    private $to_email;

    /**
     * @var string
     */
    private $to_name;

    /**
     * @var string
     */
    private $cc;

    /**
     * @var string
     */
    private $reply_to;

    /**
     * @var string
     */
    private $original_recipient;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var \DateTime
     */
    private $received_at;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \Body
     */
    private $parsedBody;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $parsedHeaders;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $parsedAttachments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parsedHeaders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->parsedAttachments = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set messageId
     *
     * @param string $messageId
     *
     * @return Email
     */
    public function setMessageId($messageId)
    {
        $this->message_id = $messageId;

        return $this;
    }

    /**
     * Get messageId
     *
     * @return string
     */
    public function getMessageId()
    {
        return $this->message_id;
    }

    /**
     * Set fromEmail
     *
     * @param string $fromEmail
     *
     * @return Email
     */
    public function setFromEmail($fromEmail)
    {
        $this->from_email = $fromEmail;

        return $this;
    }

    /**
     * Get fromEmail
     *
     * @return string
     */
    public function getFromEmail()
    {
        return $this->from_email;
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
        $this->from_name = $fromName;

        return $this;
    }

    /**
     * Get fromName
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->from_name;
    }

    /**
     * Set toEmail
     *
     * @param string $toEmail
     *
     * @return Email
     */
    public function setToEmail($toEmail)
    {
        $this->to_email = $toEmail;

        return $this;
    }

    /**
     * Get toEmail
     *
     * @return string
     */
    public function getToEmail()
    {
        return $this->to_email;
    }

    /**
     * Set toName
     *
     * @param string $toName
     *
     * @return Email
     */
    public function setToName($toName)
    {
        $this->to_name = $toName;

        return $this;
    }

    /**
     * Get toName
     *
     * @return string
     */
    public function getToName()
    {
        return $this->to_name;
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
        $this->cc = $cc;

        return $this;
    }

    /**
     * Get cc
     *
     * @return string
     */
    public function getCc()
    {
        return $this->cc;
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
        $this->reply_to = $replyTo;

        return $this;
    }

    /**
     * Get replyTo
     *
     * @return string
     */
    public function getReplyTo()
    {
        return $this->reply_to;
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
        $this->original_recipient = $originalRecipient;

        return $this;
    }

    /**
     * Get originalRecipient
     *
     * @return string
     */
    public function getOriginalRecipient()
    {
        return $this->original_recipient;
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
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set receivedAt
     *
     * @param \DateTime $receivedAt
     *
     * @return Email
     */
    public function setReceivedAt($receivedAt)
    {
        $this->received_at = $receivedAt;

        return $this;
    }

    /**
     * Get receivedAt
     *
     * @return \DateTime
     */
    public function getReceivedAt()
    {
        return $this->received_at;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Email
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set parsedBody
     *
     * @param \Body $parsedBody
     *
     * @return Email
     */
    public function setParsedBody(\Body $parsedBody = null)
    {
        $this->parsedBody = $parsedBody;

        return $this;
    }

    /**
     * Get parsedBody
     *
     * @return \Body
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Add parsedHeader
     *
     * @param \Header $parsedHeader
     *
     * @return Email
     */
    public function addParsedHeader(\Header $parsedHeader)
    {
        $this->parsedHeaders[] = $parsedHeader;

        return $this;
    }

    /**
     * Remove parsedHeader
     *
     * @param \Header $parsedHeader
     */
    public function removeParsedHeader(\Header $parsedHeader)
    {
        $this->parsedHeaders->removeElement($parsedHeader);
    }

    /**
     * Get parsedHeaders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParsedHeaders()
    {
        return $this->parsedHeaders;
    }

    /**
     * Add parsedAttachment
     *
     * @param \Attachment $parsedAttachment
     *
     * @return Email
     */
    public function addParsedAttachment(\Attachment $parsedAttachment)
    {
        $this->parsedAttachments[] = $parsedAttachment;

        return $this;
    }

    /**
     * Remove parsedAttachment
     *
     * @param \Attachment $parsedAttachment
     */
    public function removeParsedAttachment(\Attachment $parsedAttachment)
    {
        $this->parsedAttachments->removeElement($parsedAttachment);
    }

    /**
     * Get parsedAttachments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParsedAttachments()
    {
        return $this->parsedAttachments;
    }
}

