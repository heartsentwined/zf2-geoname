<?php

namespace Geoname\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Geoname\Entity\Locale
 */
class Locale
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $code
     */
    private $code;

    /**
     * @var boolean $isMain
     */
    private $isMain;

    /**
     * @var Geoname\Entity\Language
     */
    private $language;


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
     * Set code
     *
     * @param string $code
     * @return Locale
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set isMain
     *
     * @param boolean $isMain
     * @return Locale
     */
    public function setIsMain($isMain)
    {
        $this->isMain = $isMain;
    
        return $this;
    }

    /**
     * Get isMain
     *
     * @return boolean 
     */
    public function getIsMain()
    {
        return $this->isMain;
    }

    /**
     * Set language
     *
     * @param Geoname\Entity\Language $language
     * @return Locale
     */
    public function setLanguage(\Geoname\Entity\Language $language = null)
    {
        $this->language = $language;
    
        return $this;
    }

    /**
     * Get language
     *
     * @return Geoname\Entity\Language 
     */
    public function getLanguage()
    {
        return $this->language;
    }
}