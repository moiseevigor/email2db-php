<?php



/**
 * Header
 */
class Header
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $EmailID;

    /**
     * @var string
     */
    private $Name;

    /**
     * @var string
     */
    private $Value;

    /**
     * @var \Email
     */
    private $header;


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
     * Set emailID
     *
     * @param integer $emailID
     *
     * @return Header
     */
    public function setEmailID($emailID)
    {
        $this->EmailID = $emailID;

        return $this;
    }

    /**
     * Get emailID
     *
     * @return integer
     */
    public function getEmailID()
    {
        return $this->EmailID;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Header
     */
    public function setName($name)
    {
        $this->Name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Header
     */
    public function setValue($value)
    {
        $this->Value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * Set header
     *
     * @param \Email $header
     *
     * @return Header
     */
    public function setHeader(\Email $header = null)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get header
     *
     * @return \Email
     */
    public function getHeader()
    {
        return $this->header;
    }
    /**
     * @var \Email
     */
    private $Email;


    /**
     * Set email
     *
     * @param \Email $email
     *
     * @return Header
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
}
