<?php


namespace Editor\ImgeditorBundle\Effects;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Base
 *
 * @author pablo
 */
abstract class Base {      
    protected $sc;    
    protected $rootDir;
    
    
    public function construct($serviceContainer){
        $this->sc = $serviceContainer;
        
        $property = (new \ReflectionClass($this->sc->get('liip_imagine.cache.resolver.default')))
                    ->getProperty('webRoot');
        $property->setAccessible(true);
        $this->rootDir = $property->getValue($this->sc->get('liip_imagine.cache.resolver.default')); 
        
        
    } 
    
    
     public function perform(File $file, $targetDir = '', array $options = array()){   
        // Przenoszenie do folderu głownego liip imagine
        $fileCopiedToRootDir      = $this->copyFileToRootDir($file, $options);  
        // Modyfikowanie pliku za pomoca filtru liip imagine
        $fileModified                   = $this->applyEffect($fileCopiedToRootDir, $options);         
        // Przenoszenie pliku do folderu docelowego
        $fileMovedToTargetDir          = $this->moveFileToTargetDir($fileModified, $targetDir);        
        // Czyszczenie pliku który został skopiowany do folderu głownego liip imagine 
        $filesystem = new Filesystem();
        $filesystem->remove($fileCopiedToRootDir->getPathname());       
        
        return $fileMovedToTargetDir;
    }  
    
    
    
    abstract function applyEffect(File $file, array $options = []);
    
    
    
    public function getPathFromUrl($url){        
        $baseUrl    = $this->sc->get('request')->getSchemeAndHttpHost() . dirname($this->sc->get('router')->getContext()->getBaseUrl()) . '/'; 
        $path       = $this->sc->get('kernel')->getRootDir() . '/../' . $this->sc->getParameter('web_dir') .  substr($url, strlen($baseUrl));
        return $path;
    }
    
    
    public function copyFileToRootDir(File $file){
        
        $orignialPathname   = $file->getPathname();
        $targetPathname     = $this->rootDir . '/' . uniqid() . '.' . $file->guessExtension();
        
        $filesystem = new Filesystem();
        $filesystem->copy($orignialPathname, $targetPathname);
        
        $file = new File($targetPathname);
        
        return $file;
    }  
    
    
    public function moveFileToTargetDir(File $file, $targetDir){        
        $file = $file->move($targetDir, $file->getBasename());
        return $file;
    }
    
}