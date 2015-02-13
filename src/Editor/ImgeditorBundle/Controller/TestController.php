<?php

namespace Editor\ImgeditorBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Editor\ImgeditorBundle\Entity\Project;
use Editor\ImgeditorBundle\Form\ProjectType;
use Editor\ImgeditorBundle\Effects\Crop;
use Editor\ImgeditorBundle\Utility\ProjectManager;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Editor\ImgeditorBundle\Entity\Action;
use Editor\ImgeditorBundle\Entity\ProjectRepository;
use Doctrine\Common\Util\Debug;

use \Imagick;

class TestController extends BaseController {

        

    public function init() {
        parent::init();
        $this->PM = $this->getProjectManager();
    }
    
    public function deleteAction(){     
        
        $repos = array(
           $this->getDoctrine()
                ->getRepository('EditorImgeditorBundle:Action'),
            
            $repoP = $this->getDoctrine()
                ->getRepository('EditorImgeditorBundle:Project')  
            
        );
        
        foreach($repos as $repo){
            $crits = array('position' => array('value' => 1, 'operator' => '>'));
            $repo->delete($crits);
        
            $crits = array('position' => array('value' => 1, 'operator' => '>='));
            $repo->delete($crits);        
       
            $crits = array('position' => array('value' => 1, 'operator' => '='));
            $repo->delete($crits);
        
            $crits = array('position' => array('value' => 1, 'operator' => '<='));
            $repo->delete($crits);
        
            $crits = array('position' => array('value' => 1, 'operator' => '<'));
            $repo->delete($crits);
        
            $crits = array('idSession' => 'xyz');
            $repo->delete($crits);
        }
        
        exit('koniec');
    }
    
    
    
    
    
}