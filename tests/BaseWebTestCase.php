<?php

namespace Tests\BBC\CliftonBundle;

use Liip\FunctionalTestBundle\Test\WebTestCase;

abstract class BaseWebTestCase extends WebTestCase
{
    const FIXTURES_PATH = 'Tests\BBC\CliftonBundle\DataFixtures\ORM\\';

    public function assertResponseStatusCode($client, $expectedCode)
    {
        $actualCode = $client->getResponse()->getStatusCode();
        $this->assertEquals($expectedCode, $actualCode, sprintf(
            'Failed asserting that the response status code "%s" matches expected "%s"',
            $actualCode,
            $expectedCode
        ));
    }

    public function assertRedirectTo($client, $code, $expectedLocation)
    {
        $this->assertResponseStatusCode($client, $code);
        $this->assertEquals($expectedLocation, $client->getResponse()->headers->get('location'));
    }

    /**
     * Taken from Symfony/Bundle/FrameworkBundle/Test/KernelTestCase (this
     * class's grandparent), as the method in
     * Liip\FunctionalTestBundle\Test\WebTestCase (this class's parent) is
     * currently using the KERNEL_DIR param that was deprecated in Symfony 3.4.
     *
     * Delete me once Liip\FunctionalTestBundle has been updated to support
     * Symfony 3.4 without any deprecation notices.
     *
     * @return string The Kernel class name
     *
     * @throws \RuntimeException
     * @throws \LogicException
     */
    protected static function getKernelClass()
    {
        if (!isset($_SERVER['KERNEL_CLASS']) && !isset($_ENV['KERNEL_CLASS'])) {
            throw new \LogicException(sprintf('You must set the KERNEL_CLASS environment variable to the fully-qualified class name of your Kernel in phpunit.xml / phpunit.xml.dist or override the %1$s::createKernel() or %1$s::getKernelClass() method.', static::class));
        }
        if (!class_exists($class = $_ENV['KERNEL_CLASS'] ?? $_SERVER['KERNEL_CLASS'])) {
            throw new \RuntimeException(sprintf('Class "%s" doesn\'t exist or cannot be autoloaded. Check that the KERNEL_CLASS value in phpunit.xml matches the fully-qualified class name of your Kernel or override the %s::createKernel() method.', $class, static::class));
        }
        return $class;
    }

    protected function loadFixtures(array $fixtureNames, $omName = null, $registryName = 'doctrine', $purgeMode = null)
    {
        $classNames = array();
        foreach ($fixtureNames as $fixtureName) {
            $className = self::FIXTURES_PATH . $fixtureName;
            array_push($classNames, $className);
        }
        parent::loadFixtures($classNames, $omName, $registryName, $purgeMode);
    }

    protected function getDecodedJsonContent($client)
    {
        $content = $client->getResponse()->getContent();

        $decodedContent = json_decode($content, true);
        $this->assertNotNull($decodedContent, 'Expected response content to be valid JSON but it was"' . $content . '"');

        return $decodedContent;
    }
}
