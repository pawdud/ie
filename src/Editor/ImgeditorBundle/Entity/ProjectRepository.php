<?php
namespace Editor\ImgeditorBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Editor\ImgeditorBundle\Entity\BaseRepository;

use Editor\ImgeditorBundle\Entity\ActionRepository;

class ProjectRepository extends BaseRepository {    
    protected static $alias = 'p';
    protected static $entity = 'EditorImgeditorBundle:Project';  
    
    
    public function customWhere($name, $value) {
        $a1 = self::getAlias(); 
        if($name == 'idSession'){
            $this->qb->andWhere("{$a1}.idSession = :idSession");
            $this->qb->setParameter('idSession', $value);
            return true;
        }
    }
    
    
    
//    public function setWhere(array $crit = array()) {
//        $a1 = self::getAlias();    
//        if($this->_i($crit, 'idSession')){
//            $this->qb->andWhere("{$a1}.idSession = :idSession");
//            $this->qb->setParameter('idSession', $crit['idSession']);
//        }        
//        
//        if($this->_i($crit, 'position')){
//            
//            
//            
//        }
//        
//        
//    }
    
    protected function setFromMany(array $crit = array()){
        $a1 = ProjectRepository::getAlias();
        $a2 = ActionRepository::getAlias();
        $this->qb->select($a1, $a2)
        ->innerJoin("{$a1}.actions", $a2);
    }
    
    
    
}