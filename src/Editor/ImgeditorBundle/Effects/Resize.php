<?php
namespace Editor\ImgeditorBundle\Effects;

use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Util\Debug;
use Symfony\Component\Filesystem\Filesystem;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Zmiana rozmiarów obrazka
 *
 * @author pablo
 */
class Resize extends Base { 
    
    public function __construct($serviceContainer) {
        parent::construct($serviceContainer);
    }
    
    function applyEffect(File $file, array $options = array()){   
        
        
        
        
        $filepathRelative = str_replace($this->rootDir, '', $file->getPathname());
        $url = $this->sc->get('liip_imagine.controller')
                    ->filterAction($this->sc->get('request'), $filepathRelative, 'ie_upload')
                    ->headers
                    ->get('location');
        
        $path = $this->getPathFromUrl($url);
        return new File($path);        
       
    }    
}