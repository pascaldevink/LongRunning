<?php

namespace LongRunning\Tests\Plugin\DoctrineORMPlugin;

use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use LongRunning\Plugin\DoctrineORMPlugin\ResetClosedEntityManagers;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ResetClosedEntityManagersTest extends TestCase
{
    /**
     * @test
     */
    public function it_resets_entity_managers()
    {
        $managers = [
            'default'   => $this->getEntityManager(EntityManager::class),
            'second'    => $this->getEntityManager(EntityManager::class),
        ];

        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->expects($this->once())
            ->method('getManagers')
            ->willReturn($managers);

        foreach (array_keys($managers) as $count => $name) {
            $registry
                ->expects($this->at($count + 1))
                ->method('resetManager')
                ->with($name);
        }

        $logger = $this->createMock(LoggerInterface::class);
        foreach (array_keys($managers) as $count => $name) {
            $logger
                ->expects($this->at($count))
                ->method('debug')
                ->with('Reset closed EntityManager', ['entity_manager' => $name]);
        }

        $cleaner = new ResetClosedEntityManagers($registry, $logger);
        $cleaner->cleanUp();
    }

    /**
     * @test
     */
    public function it_resets_entity_manager_interfase()
    {
        $managers = [
            'default'   => $this->getEntityManager(EntityManagerInterface::class),
            'second'    => $this->getEntityManager(EntityManagerInterface::class),
        ];

        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->expects($this->once())
            ->method('getManagers')
            ->willReturn($managers);

        foreach (array_keys($managers) as $count => $name) {
            $registry
                ->expects($this->at($count + 1))
                ->method('resetManager')
                ->with($name);
        }

        $logger = $this->createMock(LoggerInterface::class);
        foreach (array_keys($managers) as $count => $name) {
            $logger
                ->expects($this->at($count))
                ->method('debug')
                ->with('Reset closed EntityManager', ['entity_manager' => $name]);
        }

        $cleaner = new ResetClosedEntityManagers($registry, $logger);
        $cleaner->cleanUp();
    }

    /**
     * @tests
     */
    public function it_ignores_other_object_mappers()
    {
        $managers = [
            'default'   => $this->getObjectManager(),
        ];

        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->expects($this->once())
            ->method('getManagers')
            ->willReturn($managers);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->never())
            ->method('debug');

        $cleaner = new ResetClosedEntityManagers($registry, $logger);
        $cleaner->cleanUp();
    }

    /**
     * @param string $entityManagerClass
     *
     * @return EntityManager|EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManager($entityManagerClass)
    {
        $manager = $this->getMockBuilder($entityManagerClass)
            ->disableOriginalConstructor()
            ->getMock();

        $manager
            ->expects($this->once())
            ->method('isOpen')
            ->willReturn(false);

        return $manager;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ObjectManager
     */
    private function getObjectManager()
    {
        $manager = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager
            ->expects($this->never())
            ->method('isOpen');

        return $manager;
    }
}
