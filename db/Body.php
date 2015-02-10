<?php



/**
 * Body
 */
class Body
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
     * @var integer
     */
    private $size_of;

    /**
     * @var string
     */
    private $charset;

    /**
     * @var string
     */
    private $filename;

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
     * @return Body
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
     * Set sizeOf
     *
     * @param integer $sizeOf
     *
     * @return Body
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
     * Set charset
     *
     * @param string $charset
     *
     * @return Body
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Get charset
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return Body
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
     * Set content
     *
     * @param string $content
     *
     * @return Body
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
     * @return Body
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

