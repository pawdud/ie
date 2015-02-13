<?php
namespace Editor\ImgeditorBundle\Utility;
use Editor\ImgeditorBundle\Entity\ProjectRepository;
use Editor\ImgeditorBundle\Entity\Project;
use Editor\ImgeditorBundle\Entity\Action;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\Common\Util\Debug;

use Editor\ImgeditorBundle\Effects\Resize;


/**
 * Zarządzanie projektem
 * 
 */
class ProjectManager{    
    private $project;
    /*
     * @var Editor\ImgeditorBundle\Entity\ProjectRepository
     */
    private $repProject;
    private $actionCurrent;    
    private $doctrine;    
    private $dir;
    private $webDir;
    private $serviceContainer;
    
    
    public function __construct($dir, $webDir, \Symfony\Component\HttpFoundation\Session\Session $session, $doctrine, $serviceContainer) {        
        $session->start();        
        $this->dir              = $dir;
        $this->webDir           = $webDir;
        $this->session          = $session;
        $this->doctrine         = $doctrine;                 
        $this->repProject = $this->doctrine->getRepository('EditorImgeditorBundle:Project');       
        $this->serviceContainer = $serviceContainer;
    }
    
    /**
     * Tworzenie projektu
     * 
     * @param type $file
     * @return \Editor\ImgeditorBundle\Entity\Project
     */
    public function projectCreate(File $file){  
        $effectResize   = new Resize($this->serviceContainer);
        $fileResized    = $effectResize->perform($file, $this->serviceContainer->getParameter('ie.img_base_dir'));
               
        $em = $this->doctrine->getManager();
        $project = new Project();
        $project->setIdSession($this->session->getId()); 
        $project->setActionPositionCurrent(0);
        $project->setCountAvalibleHistoryBack(-1);
        $em->persist($project);
        $em->flush();       
        
        $this->projectAddAction($project, $fileResized);
        
       return $project;
    }   
    
    
    /**
     * Zapisuje uploadowany plik na dysku w folderze dir
     * 
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return string ścieżka do utworzonego pliku
     * 
     * @throws FileException
     */
    
    private function createFile(File $file){
        $filename       = uniqid() . '.' . $file->getExtension();
        $file->move($this->dir, $filename); 
        return realpath($this->dir . '/' . $filename);
    }
    
    /**
     * Dodaje akcje do projektu
     * @param \Editor\ImgeditorBundle\Entity\Project $project
     */
    public function projectAddAction(Project $project, File $file){
        $em = $this->doctrine->getManager();        
        $repoAction = $this->doctrine->getRepository('EditorImgeditorBundle:Action');
        
        
        $act2del = array();
        
        
        // Usuwanie akcji ktore są przed nami - ktoś klikał przycisk wstecz
        // a następnie zedytował obrazek
        $act2del1 = $repoAction->many(array('position' => array(
                    'value' => $project->getActionPositionCurrent(), 
                    'operator' => '>'
        )));
        
        // Usuwanie akcji ktore znajdują się za nami więcej niz maksymalna
        // liczba o jakie można się cofnąć
        $act2del2 = $repoAction->many(array('position' => array(
            'value' => $project->getActionPositionCurrent() - 2,
            'operator' => '<'
        )));
        
        
        if(is_array($act2del1)){
            $act2del = array_merge($act2del, $act2del1);
        }        
        if(is_array($act2del2)){
            $act2del = array_merge($act2del, $act2del2);
        }
        
        if(count($act2del)){
            foreach($act2del as $act){
                $em->remove($act);
            }
        }        
        $em->flush();
       
        
        $action = new Action();        
        $action->setWebPath($this->webDir . $file->getBasename());
        $action->setImage($file->getPathname());        
        
        $project->setActionPositionCurrent($project->getActionPositionCurrent() + 1);
        $project->setCountAvalibleHistoryBack(min(array(($project->getCountAvalibleHistoryBack() + 1), 3)));
        $action->setPosition($project->getActionPositionCurrent()); 
        $project->addAction($action);        
        
        $em->persist($action);      
        $em->persist($project);
        $em->flush();        
    }
    
    
    /**
     * Wczytywanie projektu na podstawie id sesji
     * 
     * @return \Editor\ImgeditorBundle\Entity\Project $project
     */
    public function projectLoadFromSession(){
        $em = $this->doctrine->getManager();
        $idSession      = $this->session->getId();
        $project        = $this->repProject->one(array('idSession' => $idSession));   
        
        return $project;        
    }
    
    
    
    /**
     * Usuwanie projektu
     * 
     * @param \Editor\ImgeditorBundle\Entity\Project
     */
    public function projectRemove(Project $project){
        $em = $this->doctrine->getManager();
        $em->remove($project);
        $em->flush();
    }    
}