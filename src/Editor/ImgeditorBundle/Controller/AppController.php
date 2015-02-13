<?php

namespace Editor\ImgeditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Editor\ImgeditorBundle\Entity\Project;
use Editor\ImgeditorBundle\Form\ProjectType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Editor\ImgeditorBundle\Entity\Action;
use Imagick;

class AppController extends Controller {

    /**
     * Pojasnianie zdjęcia - test
     * 
     */
    public function brightnessAction() {    
        
        $id_action = '52c717d9f2f6f';
        
        $data = array(
          'id_action'       => $id_action,
          'url_action'      => $this->get('router')->generate('editor_imgeditor_brightness', array('id_action' => $id_action))            
        );
        
        print_r($data);        
        
        
       
        return $this->render('EditorImgeditorBundle:App:brightness.html.twig', array('data' => json_encode($data)));
    }

    

}
