<?php

namespace Editor\ImgeditorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Editor\ImgeditorBundle\Entity\Action;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Common\Util\Debug;
use Imagick;

/**
 * Projekt
 * @ORM\Entity(repositoryClass="Editor\ImgeditorBundle\Entity\ProjectRepository")
 * @ORM\Table(name="project") 
 * @ORM\HasLifecycleCallbacks
 */
class Project {

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
     * @ORM\Column(name="id_session", type="string")
     */
    private $idSession;

    /**
     * @var integer
     * @ORM\Column(name="action_position_current", type="integer", nullable=false)
     */
    private $actionPositionCurrent;
    
    /**
     * Ile razy można jeszcze użyć przycisku "Wstecz"
     * 
     * @var integer
     * @ORM\Column(name="count_avalible_history_back", type="integer", nullable=false)
     */
    private $countAvalibleHistoryBack;
    
    
    public function setCountAvalibleHistoryBack($integer){
        $this->countAvalibleHistoryBack = $integer;
        return $this;
    }
    
    public function getCountAvalibleHistoryBack(){
       return $this->countAvalibleHistoryBack;
    }
    
    
    
    

    /**
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Action", mappedBy="project", cascade={"remove"})
     * @ORM\OrderBy({"position"="DESC"})
     */
    private $actions;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    

    public function __construct() {
        $this->actions = new ArrayCollection();
    }

    public function addAction(Action $action) {
        $this->actions[] = $action;
        $action->setProject($this);
        return $this;
    }
    
    
    /**
     * Zwraca aktualną akcję
     * @return Editor\ImgeditorBundle\Entity\Action
     */    
    public function getCurrentAction(){        
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('position', $this->actionPositionCurrent));        
        $result =  $this->actions->matching($criteria);
        return $result[0];        
    }
    
    /**
     * Czy przycisk "Cofnij" jest aktywny
     * @return boolean
     */
    public function isPreviousButtonActive(){          
        return ($this->countAvalibleHistoryBack > 0);
    }
    
    
    /**
     * Czy przycisk "Do przodu" jest aktywny
     * @return boolean
     */    
    public function isNextButtonActive(){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('position', $this->actionPositionCurrent + 1));
        $result = $this->actions->matching($criteria);
        return ($result[0] !== null);
    }
    

    /**
     * Zwraca unikalne id rekordu
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Ustawia id sesji
     *
     * @param string $string id sesji z php
     * @return Project
     */
    public function setIdSession($string) {
        $this->idSession = $string;
        return $this;
    }

    /**
     * Zwraca id sesji
     *
     * @return string 
     */
    public function getIdSession() {
        return $this->idSession;
    }

    public function setActionPositionCurrent($integer) {
        $this->actionPositionCurrent = $integer;
        return $this;
    }

    public function getActionPositionCurrent() {
        return $this->actionPositionCurrent;
    }

    /**
     * Zwraca datę utworzenia projektu
     *
     * @return \DateTime 
     */
    public function getCreated() {
        return $this->created;
    }

    public function setUpdated(\DateTime $datetime) {
        $this->updated = $datetime;
        return $this;
    }

    public function getUpdated() {
        return $this->updated;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setDefaultUpdated() {       
        $this->updated = new \DateTime();
       
    }

    /**
     * Ustawia datę utworzenia projektu
     * 
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created) {
        $this->created = $created;
    }

    /**
     * @ORM\PrePersist
     */
    public function setDefaultCreated() {        
        $this->created = new \DateTime();        
    }
   

    public function getPosition() {
        return $this->position;
    }
    
    public function getActions(){
        return $this->actions;
    }

}