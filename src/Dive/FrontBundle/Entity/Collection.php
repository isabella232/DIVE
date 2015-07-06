<?php

namespace Dive\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

/**
 * Collection
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Dive\FrontBundle\Entity\CollectionRepository")
 */
class Collection implements \jsonSerializable
{
    use ORMBehaviors\Timestampable\Timestampable;

    const ENTITY_COLLECTION = 0;
    const PROGRESS_COLLECTION = 1;


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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=4096)
     */
    private $description = '';

    /**
     * @var boolean
     *
     * @ORM\Column(name="public", type="boolean")
     */
    private $public = true;


    /**
     * @var integer
     *
     * @ORM\Column(name="content", type="integer")
     */
    private $content = self::ENTITY_COLLECTION;


    /**
     * @ORM\ManyToMany(targetEntity="DiveEntity", inversedBy="collections")
     *
     */
    private $entities;


    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="collections")
     */
    private $owner;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->entities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function jsonSerialize() {
        $result = array(
            'id'=>$this->id,
            'title'=>$this->title,
            'description'=>$this->description,
            'public'=>$this->public,
            'content'=>$this->content,
            'entities'=>array(),
            'owner'=>$this->owner->getId()
            );
        foreach($this->entities as $e){
            $result['entities'][] = $e->jsonSerialize();
        }
        return $result;
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
     * Set title
     *
     * @param string $title
     * @return Collection
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Set description
     *
     * @param string $description
     * @return Collection
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set public
     *
     * @param boolean $public
     * @return Collection
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return boolean 
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set content
     *
     * @param int $content
     * @return Collection
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return int
     */
    public function getContent()
    {
        return $this->content;
    }


        /**
     * Add entities
     *
     * @param \Vivira\PortalBundle\Entity\Attribute $entities
     * @return Feature
     */
        public function addEntity(\Dive\FrontBundle\Entity\DiveEntity $entity)
        {
            $this->entities[] = $entity;

            return $this;
        }

    /**
     * Remove entities
     *
     * @param \Vivira\PortalBundle\Entity\Attribute $entities
     */
    public function removeEntity(\Dive\FrontBundle\Entity\DiveEntity $entity)
    {
        $this->entities->removeElement($entity);
    }

    /**
     * Get entities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Set owner
     *
     * @param \Dive\FrontBundle\Entity\User $owner
     * @return Collection
     */
    public function setOwner(\Dive\FrontBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \Dive\FrontBundle\Entity\User 
     */
    public function getOwner()
    {
        return $this->owner;
    }
    
}
