<?php



/**
 * Attachment
 */
class Attachment
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $ContentType;

    /**
     * @var string
     */
    private $ContentID;

    /**
     * @var integer
     */
    private $Size;

    /**
     * @var string
     */
    private $Content;

    /**
     * @var \Email
     */
    private $Email;


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
     * Set contentType
     *
     * @param string $contentType
     *
     * @return Attachment
     */
    public function setContentType($contentType)
    {
        $this->ContentType = $contentType;

        return $this;
    }

    /**
     * Get contentType
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->ContentType;
    }

    /**
     * Set contentID
     *
     * @param string $contentID
     *
     * @return Attachment
     */
    public function setContentID($contentID)
    {
        $this->ContentID = $contentID;

        return $this;
    }

    /**
     * Get contentID
     *
     * @return string
     */
    public function getContentID()
    {
        return $this->ContentID;
    }

    /**
     * Set size
     *
     * @param integer $size
     *
     * @return Attachment
     */
    public function setSize($size)
    {
        $this->Size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->Size;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Attachment
     */
    public function setContent($content)
    {
        $this->Content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->Content;
    }

    /**
     * Set email
     *
     * @param \Email $email
     *
     * @return Attachment
     */
    public function setEmail(\Email $email = null)
    {
        $this->Email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return \Email
     */
    public function getEmail()
    {
        return $this->Email;
    }
    /**
     * @var string
     */
    private $Filename;


    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return Attachment
     */
    public function setFilename($filename)
    {
        $this->Filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->Filename;
    }
}
