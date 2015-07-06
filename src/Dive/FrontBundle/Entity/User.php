<?php
namespace Dive\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
/**
 * Dive\FrontBundle\Entity\User
 *
 * @ORM\Table(name="User")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 * @ORM\Entity(repositoryClass="Dive\FrontBundle\Entity\UserRepository")
 */

class User implements AdvancedUserInterface, \Serializable, \JsonSerializable
{

    use ORMBehaviors\Timestampable\Timestampable;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(type="string", length=128)
     * @Assert\NotBlank()
     * @Assert\Length(min = "5")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $organisation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
    * @Assert\NotBlank()
     * @Assert\Length(min = "3")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hash;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="Collection", mappedBy="owner", cascade={"remove"})
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $collections;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="owner", cascade="detach")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $comments;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     *
     */
    private $userRoles;


    public function __construct()
    {
        $this->isActive = false;
        $this->salt = $this->makeSalt();
        $this->hash = $this->makeHash();
        $this->userRoles = new ArrayCollection();
        $this->createdAt= new \DateTime();
        $this->updatedAt= new \DateTime();
    }

    public function isPasswordLegal()
    {
        return strtolower($this->username) != strtolower($this->password);
    }

    public function makeHash(){
        return sha1(uniqid(null, true));
    }

    public function makeSalt(){
        return  md5(uniqid(null, true));
    }

    public function __toString(){
        return $this->getUsername();
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    public function jsonSerialize() {
        return array(
            'id'=>$this->id,
            'username'=>$this->getUsername()
            );
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
            $this->email,
            $this->collections
            ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
            $this->email
            ) = unserialize($serialized);
    }


    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->isActive;
    }


    public function getRoles()
    {
        return $this->getUserRoles()->toArray();
    }


    public function getUserRoles()
    {
        return $this->userRoles;
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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Add roles
     *
     * @param \Dive\FrontBundle\Entity\Role $roles
     * @return User
     */
    public function addUserRole(\Dive\FrontBundle\Entity\Role $roles)
    {
        $this->userRoles[] = $roles;

        return $this;
    }

    /**
     * Remove roles
     *
     * @param \Dive\FrontBundle\Entity\Role $roles
     */
    public function removeUserRole(\Dive\FrontBundle\Entity\Role $roles)
    {
        $this->userRoles->removeElement($roles);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return User
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }


    /**
     * Create a random readable string
     */
    static public function createRandomPassword($len = 8) {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = '';
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $len; $i++) {
            $pass .= $alphabet[rand(0, $alphaLength)];
        }
        return $pass;
    }

     /**
     *  Check a password string 
     */
     static public function checkString($s){
        if (ctype_lower($s) || ctype_upper($s) || ctype_alpha($s)){
            return false;
        }
        return true;
    }

   /**
     * Set organisation
     *
     * @param string $organisation
     * @return User
     */
   public function setOrganisation($organisation)
   {
    $this->organisation = $organisation;

    return $this;
}

    /**
     * Get organisation
     *
     * @return string 
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Add collections
     *
     * @param \Dive\FrontBundle\Entity\Collection $collections
     * @return User
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
     * @return User
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

}
