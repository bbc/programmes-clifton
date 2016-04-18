<?php

namespace Tests\BBC\CliftonBundle\DependencyInjection;

use BBC\CliftonBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use PHPUnit_Framework_TestCase;

class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    public function testConfigTreeBuilder()
    {
        $configuration = new Configuration();

        $expectedTreeBuilder = new TreeBuilder();
        $expectedTreeBuilder->root('clifton');

        $this->assertEquals($expectedTreeBuilder, $configuration->getConfigTreeBuilder());
    }
}
