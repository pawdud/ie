<?php
namespace Editor\ImgeditorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Editor\ImgeditorBundle\Entity\Project;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Akcja
 * @ORM\Entity(repositoryClass="Editor\ImgeditorBundle\Entity\ActionRepository")
 * @ORM\Table(name="action")
 * @ORM\HasLifecycleCallbacks
 */
class Action {

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
     * @ORM\Column(name="position", type="string", nullable=true)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", nullable=false)
     */
    private $image;
    
    /**
     * @var string
     *
     * @ORM\Column(name="web_path", type="string", nullable=false)
     */
    private $web_path;
    
    
    

    /**
     * @var string
     *
     * @ORM\Column(name="json_data", type="string", length=1000, nullable=true)
     */
    private $json_data;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="actions", cascade={"persist"})
     * @ORM\JoinColumn(name="id_project", referencedColumnName="id")
     */
    private $project;
   

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }
  

    /**
     * Set position
     *
     * @param integer $position
     * @return Action
     */
    public function setPosition($position) {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition() {
        return $this->position;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Action
     */
    public function setImage($image) {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage() {
        return $this->image;
    }
    
    /**
     * Set web path
     *
     * @param string $web_path
     * @return Action
     */
    public function setWebPath($string) {
        $this->web_path = $string;
        return $this;
    }

    /**
     * Get web path
     *
     * @return string 
     */
    public function getWebPath() {
        return $this->web_path;
    }
    
    
    public function getFile(){
        return new File($this->image);
    }
    

    /**
     * Set json_data
     *
     * @param string $jsonData
     * @return Action
     */
    public function setJsonData($jsonData = null) {
        $this->json_data = $jsonData;

        return $this;
    }

    /**
     * Get json_data
     *
     * @return string 
     */
    public function getJsonData() {
        return $this->json_data;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Action
     */
    public function setUpdated($updated) {
        $this->updated = $updated;
        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated() {
        return $this->updated;
    }

    /**
     * Set project
     *
     * @param $project
     * @return Action
     */
    public function setProject(\Editor\ImgeditorBundle\Entity\Project $project) {
        $this->project = $project;
        return $this;
    }

    /**
     * Get project
     *
     * @return \Editor\ImgeditorBundle\Entity\Project 
     */
    public function getProject() {
        return $this->project;
    }
    
    /**
     * @ORM\PostRemove
     */
    public function onPostRemove(){
        if(is_file($this->web_path)){
            unlink($this->web_path);
        }
    }
}
