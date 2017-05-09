<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\CategoryItemMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Format;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class CategoryItemMapperTest extends TestCase
{
    public function testMappingGenre()
    {
        $genre = new Genre([0], 'id', 'title', 'urlkey');

        $expectedOutput = (object) [
            'type' => 'genre',
            'id' => 'id',
            'key' => 'urlkey',
            'title' => 'title',
            'has_topic_page' => false,
            'sameAs' => null,
        ];

        $mapper = new CategoryItemMapper();

        $output = $mapper->getApsObject($genre);

        $this->assertObjectNotHasAttribute('narrower', $output);
        $this->assertObjectNotHasAttribute('broader', $output);
        $this->assertEquals($expectedOutput, $output);
    }

    public function testMappingFormat()
    {
        $format = new Format([1], 'id', 'title', 'urlkey');

        $expectedOutput = (object) [
            'type' => 'format',
            'id' => 'id',
            'key' => 'urlkey',
            'title' => 'title',
            'has_topic_page' => false,
            'sameAs' => null,
        ];

        $mapper = new CategoryItemMapper();

        $output = $mapper->getApsObject($format);

        $this->assertObjectNotHasAttribute('narrower', $output);
        $this->assertObjectNotHasAttribute('broader', $output);
        $this->assertEquals($expectedOutput, $output);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidDomainObject()
    {
        $pid = new Pid('p01m5mss');

        $mapper = new CategoryItemMapper();
        $mapper->getApsObject($pid);
    }
}
