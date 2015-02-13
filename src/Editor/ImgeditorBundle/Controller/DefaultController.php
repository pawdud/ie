<?php

namespace Editor\ImgeditorBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Editor\ImgeditorBundle\Entity\Project;
use Editor\ImgeditorBundle\Form\ProjectType;
use Editor\ImgeditorBundle\Effects\Crop;
use Editor\ImgeditorBundle\Utility\ProjectManager;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Editor\ImgeditorBundle\Entity\Action;
use Editor\ImgeditorBundle\Entity\ProjectRepository;
use Doctrine\Common\Util\Debug;

use \Imagick;

class DefaultController extends BaseController {

    /**
     *
     * @var ProjectManager
     * 
     */
    private $PM;
    
    /**
     * @var Editor\ImgeditorBundle\Entity\Project
     */
    private $project;
    

    public function init() {
        parent::init();
        $this->PM = $this->getProjectManager();
    }

    /**
     * Wybór zdjęcia do załadowania
     */
    public function indexAction(Request $request) {
        $viewName = 'EditorImgeditorBundle:Default:index.html.twig';
        $viewData = array();
        return $this->render($viewName, $viewData);
    }

    /**
     * Tworzenie nowego projektu
     * 
     */
    public function createProjectAction(Request $request) {             
        if($project = $this->PM->projectLoadFromSession()){
            // Dla danej sesji możemy mieć tylko jeden aktywny projekt
            $this->PM->projectRemove($project);
        }        
        $file       = $request->files->get('file'); 
        $project  = $this->PM->projectCreate($file);   
        return $this->redirect($this->generateUrl('pr_edit'));
    }   
    
     /**
     * 
      * Edycja projektu
      * 
     * @param Request $request
     * @param integer $id
     */
    public function editProjectAction(Request $request, $idAction){
        $viewName = 'EditorImgeditorBundle:Default:editProject.html.twig';
        $viewData = array();
        $project = $this->PM->projectLoadFromSession();
        $viewData['project'] = $project;
        $viewData['project']->isNextButtonActive();        
        return $this->render($viewName, $viewData);
    } 
    
    
     /**
     * "Wstecz"
     */
    public function undoAction() {
        $project = $this->PM->projectLoadFromSession();        
        if($project->isPreviousButtonActive()){
            
            $em = $this->getDoctrine()->getEntityManager();
            $project->setActionPositionCurrent($project->getActionPositionCurrent() - 1);
            $project->setCountAvalibleHistoryBack($project->getCountAvalibleHistoryBack() - 1);
            $em->persist($project);
            $em->flush();        
        }
        return $this->redirect($this->generateUrl('pr_edit'));
        
    }

    /**
     * "Do przodu"
     */
    public function redoAction() {
        $project = $this->PM->projectLoadFromSession();        
        if($project->isNextButtonActive()){              
            $em = $this->getDoctrine()->getEntityManager();
            $project->setActionPositionCurrent($project->getActionPositionCurrent() + 1);
            $project->setCountAvalibleHistoryBack($project->getCountAvalibleHistoryBack() + 1);
            $em->persist($project);
            $em->flush();   
        }
        return $this->redirect($this->generateUrl('pr_edit')); 
    }
    
    
    public function rotateAction(Request $request){
        $project = $this->PM->projectLoadFromSession(); 
        $currentActionPathname    = $project->getCurrentAction()->getImage();
        $newActionPathname        = $this->container->getParameter('ie.img_base_dir') .'/' . uniqid() . image_type_to_extension(exif_imagetype($currentActionPathname));
        
        $imagine = new \Imagine\Gd\Imagine();
        $imagine->open($currentActionPathname)
                ->rotate($request->request->get('deg', -90))
                ->save($newActionPathname);        
        
        $this->PM->projectAddAction($project, new File($newActionPathname));
        return $this->redirect($this->generateUrl('pr_edit')); 
    }
    
    public function rotateProjectAction(Request $request){
        $project = $this->PM->projectLoadFromSession();
    }
    
    
    public function removeProjectAction(Request $request){
        $project = $this->PM->projectLoadFromSession();
        $this->PM->projectRemove($project);
        return null;
    }
    
    
    /**
     * Kropowanie zdjęcia
     */
    public function cropAction(Request $requst){
        $project        = $this->PM->projectLoadFromSession();
        $action         = $project->getCurrentAction();
        $cropEffect     = new Crop($this->container);
        $file = $cropEffect->perform($action->getFile(), $this->container->getParameter('ie.img_base_dir'), array(
            'x'    => $requst->request->get('x'),
            'y'    => $requst->request->get('y'),
            'w'     => $requst->request->get('w'),
            'h'     => $requst->request->get('h'),
        ) );
        $this->PM->projectAddAction($project, $file);        
        return $this->redirect($this->generateUrl('pr_edit'));
    }
    
    /**
     * Pobieranie obrazka
     */
    public function downloadAction(){
         $project        = $this->PM->projectLoadFromSession();
         $action        = $project->getCurrentAction();
         $file      = $action->getFile();
         $response = new Response(file_get_contents($action->getFile()));
         $filename = uniqid() . '.' . $file->getExtension();         
         $disposition =  $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
         $response->headers->set('Content-Disposition', $disposition);
         
         return $response;
    }
    

    /**
     * 
     * @param type $id_action
     * @return type \Symfony\Component\HttpFoundation\JsonRespons
     */
    public function contrastAction($id_action) {
        //potrzebny "skok" zakres mniej więcej -100 do 100 chociaż to i tak dużo raczej
        // liczby całkowite
        // skok co 2
        // - to większy
        // + to mniejszy
        $level = -10;
        //pobranie danych...
        $dane = $this->getDataFromAction($id_action);
        // zniejszanie/zwiększanie kontrastu

        $img = imagecreatefromjpeg($dane['project']->getUploadDir() . '/' . $dane['image']);
        imagefilter($img, IMG_FILTER_CONTRAST, $level);
        imagejpeg($img, $dane['new_path']);
        imagedestroy($img);

        // 3.
        // zapisanie danych do bazy 
        // jeśli contrast wykona się poprawnie
        $data = $this->saveToAction($dane['position'], $dane['new_img_name'], $dane['project'], $dane['new_path']);
        // 4.
        // Tworzenie odpowiedzi

        return new JsonResponse($data, 200, array('Content-Type: application/json'));
    }

    public function brightnessAction($id_action) {
        // liczby całkowite
        // + to jaśniej 
        // - to ciemniej
        // 0 to bez zmian
        // skoki co 10         
        /* $brightness = 80; */ $brightness = $this->getRequest()->request->get('v');

//        exit($brightness);

        $dane = $this->getDataFromAction($id_action);
        $img = imagecreatefromjpeg($dane['project']->getUploadDir() . '/' . $dane['image']);
        imagefilter($img, IMG_FILTER_BRIGHTNESS, $brightness);
        imagejpeg($img, $dane['new_path']);
        imagedestroy($img);

        $data = $this->saveToAction($dane['position'], $dane['new_img_name'], $dane['project'], $dane['new_path']);

        return new JsonResponse($data, 200, array('Content-Type: application/json'));
    }

    /**
     * 
     * @param type $id_action
     * @return type \Symfony\Component\HttpFoundation\JsonRespons
     */
    public function grayscaleAction($id_action) {
        //pobranie danych...
        $dane = $this->getDataFromAction($id_action);
        // zniejszanie/zwiększanie kontrastu

        $img = imagecreatefromjpeg($dane['project']->getUploadDir() . '/' . $dane['image']);
        imagefilter($img, IMG_FILTER_GRAYSCALE);
        imagejpeg($img, $dane['new_path']);
        imagedestroy($img);

        // 3.
        // zapisanie danych do bazy 
        // jeśli contrast wykona się poprawnie
        $data = $this->saveToAction($dane['position'], $dane['new_img_name'], $dane['project'], $dane['new_path']);
        // 4.
        // Tworzenie odpowiedzi

        return new JsonResponse($data, 200, array('Content-Type: application/json'));
    }

    /**
     * 
     * @param type $id_action
     * @return type \Symfony\Component\HttpFoundation\JsonRespons
     */
    public function sharpenAction($id_action) {
        // potrzebny promień wyostrzenia i odchylenie standardowe(sigma)
        // skoki co 1 lub co 0,5
        // typ float

        $radius = 1;
        $sigma = 0;

        //pobranie danych...
        $dane = $this->getDataFromAction($id_action);
        // zwiększanie wyostrzenia

        $obrazek = new Imagick($dane['project']->getUploadDir() . '/' . $dane['image']);
        $obrazek->sharpenimage($radius, $sigma);
        $obrazek->writeimage($dane['new_path']);
        $obrazek->destroy();

        // 3.
        // zapisanie danych do bazy 
        // jeśli contrast wykona się poprawnie
        $data = $this->saveToAction($dane['position'], $dane['new_img_name'], $dane['project'], $dane['new_path']);
        // 4.
        // Tworzenie odpowiedzi

        return new JsonResponse($data, 200, array('Content-Type: application/json'));
    }

    /**
     * 
     * @param type $id_action
     * @return type \Symfony\Component\HttpFoundation\JsonRespons
     */
    public function mirrorAction($id_action) {
        // potrzebny rodzaj:
        // 0 to w pionie
        // 1 to w poziomie

        $flip = 0;

        //pobranie danych...
        $dane = $this->getDataFromAction($id_action);
        // zwiększanie wyostrzenia

        $obrazek = new Imagick($dane['project']->getUploadDir() . '/' . $dane['image']);

        if ($flip === 0) {
            $obrazek->flipimage();
            $obrazek->writeimage($dane['new_path']);
            $obrazek->destroy();
        } elseif ($flip === 1) {
            $obrazek->flopimage();
            $obrazek->writeimage($dane['new_path']);
            $obrazek->destroy();
        }
        // 3.
        // zapisanie danych do bazy 
        // jeśli contrast wykona się poprawnie
        $data = $this->saveToAction($dane['position'], $dane['new_img_name'], $dane['project'], $dane['new_path']);
        // 4.
        // Tworzenie odpowiedzi

        return new JsonResponse($data, 200, array('Content-Type: application/json'));
    }

 

    /**
     * 
     * @param type $id_action
     * @param type $asAction
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function fetchAction($id_action) {
        $action = $this->getDoctrine()->getRepository('EditorImgeditorBundle:Action')->findOneBy(
                array('id_action' => $id_action)
        );

        $src = '/' . $action->getUploadDir() . '/' . $action->getImage();

        $response = array(
            'src' => $src,
            'id_action' => $id_action
        );
        return new JsonResponse($response);
    }

    private function getDataFromAction($id) {
        // pobranie adresu do obrazka, ale lepiej by bylo jakbyś mi go przekazał 
        //będzie znacznie mniej kombinacji
        $action_repo = $this->getDoctrine()->getRepository('EditorImgeditorBundle:Action')->findOneBy(
                array('id_action' => $id));
        // ustalenie projektu do którego ma być przypisane akcja  
        $project = $action_repo->getProject();
        //ustalenie ostatniej pozycji akcji w projekcie i...
        $position = $action_repo->getPosition();
        //... nadanie nowego nr pozycji
        $position++;
        $image = $action_repo->getImage();
        //nowa nazwa pliku po obróceniu
        $new_img_name = uniqid() . '.jpeg';
        // i nowa ścieżka do niego relatywna
        $new_path = $project->getUploadDir() . '/' . $new_img_name;
        //dane zwracane przez funkcje
        $data_from_action = array(
            'image' => $image,
            'new_path' => $new_path,
            'project' => $project,
            'position' => $position,
            'new_img_name' => $new_img_name,
            'new_path' => $new_path
        );

        return $data_from_action;
    }
}
