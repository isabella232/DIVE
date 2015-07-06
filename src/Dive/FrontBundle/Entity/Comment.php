<?php

namespace Dive\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
/**
 * Comment
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Dive\FrontBundle\Entity\CommentRepository")
 */
class Comment implements \jsonSerializable
{
    use ORMBehaviors\Timestampable\Timestampable;
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
     * @ORM\Column(name="body", type="string", length=4096)
     */
    private $body;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="comments")
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="DiveEntity", inversedBy="comments")
     */
    private $entity;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function jsonSerialize() {
        $result = array(
            'id'=>$this->id,
            'body'=>$this->body,
            'owner'=> $this->owner ? $this->owner->jsonSerialize() : array('id'=>0,'username'=>'Anonymous'),
            'entity'=>$this->getEntity()->getId(),
            'entity_uid'=>$this->getEntity()->getUID(),
            'created_at'=>$this->createdAt
            );
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
     * Set body
     *
     * @param string $body
     * @return Comment
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set owner
     *
     * @param \Dive\FrontBundle\Entity\User $owner
     * @return Comment
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

    /**
     * Set entity
     *
     * @param \Dive\FrontBundle\Entity\DiveEntity $entity
     * @return DiveEntity
     */
    public function setEntity(\Dive\FrontBundle\Entity\DiveEntity $entity = null)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Get entity
     *
     * @return \Dive\FrontBundle\Entity\DiveEntity 
     */
    public function getEntity()
    {
        return $this->entity;
    }
 
}
