<?php

namespace Tests\BBC\CliftonBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Doctrine\Common\Persistence\ObjectManager;

abstract class BaseFixture extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * A utility function to enable rapid creation of multiple fixtures
     *
     * Takes a an array that provides a reference name, factory data and a user
     * the entity should be created by and callable that descibes how to
     * transform the factory data into an entity to be persisted to the DB
     *
     * @param  array    $entityDataProvider
     * @param  callable $entityFactoryCallable
     * @return null
     */
    protected function loadEntitiesFromProvider(ObjectManager $manager, array $entityDataProvider, callable $entityFactoryCallable)
    {
        $defaultUser = 'fixture.generator@example.com';

        foreach ($entityDataProvider as $entityConfig) {
            $user = array_key_exists(2, $entityConfig) && $entityConfig[2] ? $entityConfig[2] : $defaultUser;
            $this->setBlameableUser($user);

            $entity = call_user_func_array($entityFactoryCallable, $entityConfig[1]);

            if (!is_null($entityConfig[0])) {
                $this->addReference($entityConfig[0], $entity);
            }
            $manager->persist($entity);
        }

        $manager->flush();
    }
}
