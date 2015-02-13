<?php
namespace Editor\ImgeditorBundle\Entity;

use Doctrine\ORM\EntityRepository;

use  Editor\ImgeditorBundle\Entity\BaseRepository;
use  Editor\ImgeditorBundle\Entity\ProjectRepository;


class ActionRepository extends BaseRepository {    
    protected static $alias = 'a';
    protected static $entity = 'EditorImgeditorBundle:Action';  
    
    
    public function getMaxPosition(array $crit = array()){   
        $a1 = self::$alias;
        $e1 = self::$entity;                  
        $a2 = ProjectRepository::getAlias();        
        
        $qb = $this->getEntityManager()->createQueryBuilder();        
        $qb->select("MAX({$a1}.position)")
        ->from($e1, $a1)        
        ->join("{$a1}.project", $a2);
        
        if(isset($crit['idProject'])){
            $qb->andWhere("{$a2}.id = :idProject");
            $qb->setParameter('idProject', $crit['idProject']);            
        }   
        
        $result = $qb->getQuery()
                ->getScalarResult();        
        
        return $result[0][1];
    }    
}