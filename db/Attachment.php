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
    private $content_type;

    /**
     * @var string
     */
    private $content_id;

    /**
     * @var string
     */
    private $content_disposition;

    /**
     * @var integer
     */
    private $size_of;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $hash_content;

    /**
     * @var string
     */
    private $content;

    /**
     * @var \Email
     */
    private $email;


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
        $this->content_type = $contentType;

        return $this;
    }

    /**
     * Get contentType
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * Set contentId
     *
     * @param string $contentId
     *
     * @return Attachment
     */
    public function setContentId($contentId)
    {
        $this->content_id = $contentId;

        return $this;
    }

    /**
     * Get contentId
     *
     * @return string
     */
    public function getContentId()
    {
        return $this->content_id;
    }

    /**
     * Set contentDisposition
     *
     * @param string $contentDisposition
     *
     * @return Attachment
     */
    public function setContentDisposition($contentDisposition)
    {
        $this->content_disposition = $contentDisposition;

        return $this;
    }

    /**
     * Get contentDisposition
     *
     * @return string
     */
    public function getContentDisposition()
    {
        return $this->content_disposition;
    }

    /**
     * Set sizeOf
     *
     * @param integer $sizeOf
     *
     * @return Attachment
     */
    public function setSizeOf($sizeOf)
    {
        $this->size_of = $sizeOf;

        return $this;
    }

    /**
     * Get sizeOf
     *
     * @return integer
     */
    public function getSizeOf()
    {
        return $this->size_of;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return Attachment
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set hashContent
     *
     * @param string $hashContent
     *
     * @return Attachment
     */
    public function setHashContent($hashContent)
    {
        $this->hash_content = $hashContent;

        return $this;
    }

    /**
     * Get hashContent
     *
     * @return string
     */
    public function getHashContent()
    {
        return $this->hash_content;
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
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
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
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return \Email
     */
    public function getEmail()
    {
        return $this->email;
    }
}

