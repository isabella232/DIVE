<?php

namespace Dive\APIBundle\Entity;

class DataEntity implements \JsonSerializable
{
    /* Unique entity identifier */
    private $uid = '';
    /* Entity type */
    private $type = '';
    /* Title */
    private $title = '';
    /* Description */
    private $description = '';
    /* Dates */
    private $date = array(
        'start'=>false,
        'end'=>false
        );
    /* Depicted by */
    private $depictedBy = array(
        'placeholder'=>'',
        'source'=>''
        );
    /* Sources */
    private $sources = array();
    /* Related event if any */
    private $event = ''; //related event
    /* DBPedia type */
    private $dbpedia = array();

    /* CONSTRUCTOR */
    public function __construct()
    {
    }


    /* GETTERS / SETTERS */

    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    public function getUid()
    {
        return $this->uid;
    }


    public function setType($type, $removeNamespace = false)
    {
        if ($removeNamespace){
            // remove namespace
            $pos = strrpos($type, "/");
            if ($pos) { $type = substr($type,$pos+1); }
        }
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDateStart($date)
    {
        $this->date['start'] = $date;
        return $this;
    }

    public function getDateStart()
    {
        return $this->date['start'];
    }


    public function setDateEnd($date)
    {
        $this->date['end'] = $date;
        return $this;
    }

    public function getDateEnd()
    {
        return $this->date['end'];
    }

    public function setDepictedBy($depictedBy)
    {
        $this->depictedBy = $depictedBy;
        return $this;
    }

    public function getDepictedBy()
    {
        return $this->depictedBy;
    }

    public function setDepictedBySource($source){
        $this->depictedBy['source'] = $source;
        return $this;
    }

    public function getDepictedBySource()
    {
        return $this->depictedBy['source'];
    }


    public function setDepictedByPlaceholder($placeholder){
        $this->depictedBy['placeholder'] = $placeholder;
        return $this;
    }

    public function getDepictedByPlaceholder()
    {
        return $this->depictedBy['placeholder'];
    }

    public function setSources($sources)
    {
        $this->sources = $sources;
        return $this;
    }

    public function getSources()
    {
        return $this->sources;
    }

    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setDBPedia($dbpedia)
    {
        $this->dbpedia = $dbpedia;
        return $this;
    }

    public function getDBPedia()
    {
        return $this->dbpedia;
    }

    /* JSON Serialize */

    public function jsonSerialize() {
        return array(
            'uid' => $this->getUid(),
            'type' => $this->getType(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'date' => $this->getDate(),
            'depicted_by' => $this->getDepictedBy(),
            'event' => $this->getEvent(),
            'dbpedia' => $this->getDBPedia(),
            );
    }
}