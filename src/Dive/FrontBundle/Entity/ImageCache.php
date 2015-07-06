<?php

namespace Dive\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity
 *
 * @ORM\Entity
 * @ORM\Table(indexes={@ORM\Index(name="keywords_idx", columns={"keywords"})})
 */
class ImageCache
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords", type="string", length=512)
     */
    private $keywords;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=512)
     */
    private $url;


    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=2048)
     */
    private $source;


    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer")
     */
    private $size = 0;

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
     * Set keywords
     *
     * @param string $keywords
     * @return ImageCache
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

     /**
     * Set url
     *
     * @param string $url
     * @return ImageCache
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }


     /**
     * Set source
     *
     * @param string $source
     * @return ImageCache
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }


   /**
     * Set size
     *
     * @param string $size
     * @return ImageCache
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }
}
