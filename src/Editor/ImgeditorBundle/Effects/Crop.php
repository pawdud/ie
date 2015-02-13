<?php


namespace Editor\ImgeditorBundle\Effects;

use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Util\Debug;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Base
 *
 * @author pablo
 */
class Crop extends Base {        
    
    
    public function __construct($serviceContainer) {
        parent::construct($serviceContainer);
    }
    
    
    /**     
     * Kropowanie zdjęcia     
     * 
     * @param \Symfony\Component\HttpFoundation\File\File $file
     * @param string $dir folder do którego ma być zapisany zmodyfikowany obrazek
     * @param array $options np. : array(
     *  x => 10 // współrzedna x lewy górny róg
     *  y => 10 // współrzedna  y lewy górny róg
     *  w => 100 // szerokość
     *  h => 100 // wysokość
     * )
     * 
     * @return Symfony\Component\HttpFoundation\File\File
     * 
     * @throw Exception
     */   
    public function applyEffect(File $file, array $options = []){ 
        $filepathRelative = str_replace($this->rootDir, '', $file->getPathname());
        $cnf      = $this->sc->get('liip_imagine.filter.configuration');        
        $cnfCrop  = $cnf->get('ie_crop');
        $cnfCrop['filters']['crop']['start'][0] = $options['x'];
        $cnfCrop['filters']['crop']['start'][1] = $options['y'];
        $cnfCrop['filters']['crop']['size'][0] = $options['w'];
        $cnfCrop['filters']['crop']['size'][1] = $options['h'];
        
        

        
        $cnf->set('ie_crop', $cnfCrop);
        $url =  $this->sc->get('liip_imagine.controller')
                ->filterAction($this->sc->get('request'), $filepathRelative, 'ie_crop')
                ->headers
                ->get('location');
        
        return new File($this->getPathFromUrl($url));
    }    
}