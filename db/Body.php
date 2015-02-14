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
     * @var integer
     */
    private $size_of;

    /**
     * @var string
     */
    private $content_plain;

    /**
     * @var string
     */
    private $content_html;

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
     * Set contentPlain
     *
     * @param string $contentPlain
     *
     * @return Body
     */
    public function setContentPlain($contentPlain)
    {
        $this->content_plain = $contentPlain;

        return $this;
    }

    /**
     * Get contentPlain
     *
     * @return string
     */
    public function getContentPlain()
    {
        return $this->content_plain;
    }

    /**
     * Set contentHtml
     *
     * @param string $contentHtml
     *
     * @return Body
     */
    public function setContentHtml($contentHtml)
    {
        $this->content_html = $contentHtml;

        return $this;
    }

    /**
     * Get contentHtml
     *
     * @return string
     */
    public function getContentHtml()
    {
        return $this->content_html;
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

