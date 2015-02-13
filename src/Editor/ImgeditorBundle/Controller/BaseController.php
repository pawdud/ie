<?php

namespace Editor\ImgeditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Description of BaseController
 *
 * @author pablo
 */
class BaseController extends Controller {

    public function setContainer(ContainerInterface $container = null) {
        parent::setContainer($container);
        $this->init();        
    }    
    
    public function init(){
        
    }
    
    /**
     * 
     * 
     * @return Editor\ImgeditorBundle\Utility\ProjectManager $projectMnager
     */
    public function getProjectManager(){
        return $this->get('ie.project_manager');        
    }
}