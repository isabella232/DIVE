<?php

namespace Dive\APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
/**
 * Entity
 *
 * @ORM\Entity
 * @ORM\Table(indexes={@ORM\Index(name="querytype_idx", columns={"querytype","request"})})
 */
class QueryCache
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
     * @ORM\Column(name="querytype", type="string", length=10)
     */
    private $queryType;

    /**
     * @var string
     *
     * @ORM\Column(name="request", type="string", length=40)
     */
    private $request;

        /**
     * @var string
     *
     * @ORM\Column(name="data", type="text")
     */
    private $data;

    /**
     * @var integer
     *
     * @ORM\Column(name="dataset", type="integer")
     */
    private $dataSet;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
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
     * Set querytype
     *
     * @param string $querytype
     * @return QueryCache
     */
    public function setQueryType($queryType)
    {
        $this->queryType = $queryType;

        return $this;
    }

    /**
     * Get querytype
     *
     * @return string
     */
    public function getQueryType()
    {
        return $this->queryType;
    }

     /**
     * Set request
     *
     * @param string $request
     * @return QueryCache
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get request
     *
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
    }

 /**
     * Set data
     *
     * @param string $data
     * @return QueryCache
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set dataSet
     *
     * @param integer $dataSet
     * @return QueryCache
     */
    public function setDataSet($dataSet)
    {
        $this->dataSet = $dataSet;

        return $this;
    }

    /**
     * Get dataSet
     *
     * @return integer
     */
    public function getDataSet()
    {
        return $this->dataSet;
    }
}
