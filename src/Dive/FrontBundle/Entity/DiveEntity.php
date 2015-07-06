<?php

namespace Dive\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
/**
 * Entity
 *
 * @ORM\Table(indexes={@ORM\Index(name="uid", columns={"uid"})})
 * @ORM\Entity(repositoryClass="Dive\FrontBundle\Entity\DiveEntityRepository")
 */
class DiveEntity
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
     * @var integer
     *
     * @ORM\Column(name="uid", type="string", length=512)
     */
    private $uid;

    /**
     * @ORM\ManyToMany(targetEntity="Collection", mappedBy="entities")
     *
     */
    private $collections;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="entity", cascade="remove")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $comments;


    public function jsonSerialize() {
        return array(
            'id'=>$this->id,
            'uid'=>$this->uid
            );
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
     * Add collections
     *
     * @param \Dive\FrontBundle\Entity\Collection $collections
     * @return DiveEntity
     */
    public function addCollection(\Dive\FrontBundle\Entity\Collection $collections)
    {
        $this->collections[] = $collections;

        return $this;
    }

    /**
     * Remove collections
     *
     * @param \Dive\FrontBundle\Entity\Collection $collections
     */
    public function removeCollection(\Dive\FrontBundle\Entity\Collection $collections)
    {
        $this->collections->removeElement($collections);
    }

    /**
     * Get collections
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCollections()
    {
        return $this->collections;
    }

    /**
     * Add comments
     *
     * @param \Dive\FrontBundle\Entity\Comment $comments
     * @return DiveEntity
     */
    public function addComment(\Dive\FrontBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments
     *
     * @param \Dive\FrontBundle\Entity\Comment $comments
     */
    public function removeComment(\Dive\FrontBundle\Entity\Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->collections = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set uid
     *
     * @param string $uid
     * @return DiveEntity
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * Get uid
     *
     * @return string 
     */
    public function getUid()
    {
        return $this->uid;
    }
}
