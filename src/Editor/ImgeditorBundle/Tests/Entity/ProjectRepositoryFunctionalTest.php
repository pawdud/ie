<?php

namespace Editor\ImgeditorBundle\Tests\Entity;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * 
 *
 * @author pablo
 */
class ProjectRepositoryFunctionalTest extends KernelTestCase {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * {@inheritDoc}
     */
    public function setUp() {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
                ->get('doctrine')
                ->getManager()
        ;
    }

    public function testCreate() {
       $repo = $this->em->getRepository('EditorImgeditorBundle:Project');
       $this->assertTrue(false);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown() {
        parent::tearDown();
        $this->em->close();
    }

}