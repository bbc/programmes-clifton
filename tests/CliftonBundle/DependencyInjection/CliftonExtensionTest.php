<?php

namespace Tests\BBC\CliftonBundle\DependencyInjection;

use BBC\CliftonBundle\DependencyInjection\CliftonExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use PHPUnit_Framework_TestCase;

class CliftonExtensionTest extends PHPUnit_Framework_TestCase
{
    public function testLoadWithoutErrors()
    {
        $containerBuilder = new ContainerBuilder();

        $extension = new CliftonExtension();
        $extension->load([], $containerBuilder);
    }
}
