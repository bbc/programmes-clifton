<?php

namespace Tests\BBC\CliftonBundle\ApsMapper;

use BBC\CliftonBundle\ApsMapper\CategoryItemMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Format;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use PHPUnit_Framework_TestCase;

class CategoryItemMapperAdditionalHydrationTest extends PHPUnit_Framework_TestCase
{
    public function testMappingBroaderCategory()
    {
        $genre = new Genre('id', 'title', 'urlkey', new Genre('parentId', 'parentTitle', 'parentUrlKey'));

        $expectedOutput = (object) [
            'type' => 'genre',
            'id' => 'id',
            'key' => 'urlkey',
            'title' => 'title',
            'broader' => (object) [
                'type' => 'genre',
                'id' => 'parentId',
                'key' => 'parentUrlKey',
                'title' => 'parentTitle',
                'broader' => (object) [],
                'has_topic_page' => false,
                'sameAs' => null,
            ],
            'has_topic_page' => false,
            'sameAs' => null,
        ];

        $mapper = new CategoryItemMapper();

        $output = $mapper->getApsObject($genre, true);

        $this->assertObjectNotHasAttribute('narrower', $output);
        $this->assertObjectHasAttribute('broader', $output);
        $this->assertObjectHasAttribute('broader', $output->{'broader'});
        $this->assertEquals($expectedOutput, $output);
    }

    public function testMappingEmptySubcategories()
    {
        $genre = new Genre('id', 'title', 'urlkey');

        $subgenres = [];

        $expectedOutput = (object) [
            'type' => 'genre',
            'id' => 'id',
            'key' => 'urlkey',
            'title' => 'title',
            'narrower' => [],
            'has_topic_page' => false,
            'sameAs' => null,
        ];

        $mapper = new CategoryItemMapper();

        $output = $mapper->getApsObject($genre, false, $subgenres);

        $this->assertObjectHasAttribute('narrower', $output);
        $this->assertObjectNotHasAttribute('broader', $output);
        $this->assertEquals(count($output->{'narrower'}), 0);
        $this->assertEquals($expectedOutput, $output);
    }

    public function testMappingSubcategories()
    {
        $genre = new Genre('id', 'title', 'urlkey');

        $subgenres = [
            new Genre('subid1', 'subtitle1', 'suburlkey1'),
            new Genre('subid2', 'subtitle2', 'suburlkey2'),
            new Genre('subid3', 'subtitle3', 'suburlkey3'),
        ];

        $expectedOutput = (object) [
            'type' => 'genre',
            'id' => 'id',
            'key' => 'urlkey',
            'title' => 'title',
            'narrower' => [
                (object) [
                    'type' => 'genre',
                    'id' => 'subid1',
                    'key' => 'suburlkey1',
                    'title' => 'subtitle1',
                    'has_topic_page' => false,
                    'sameAs' => null,
                ],
                (object) [
                    'type' => 'genre',
                    'id' => 'subid2',
                    'key' => 'suburlkey2',
                    'title' => 'subtitle2',
                    'has_topic_page' => false,
                    'sameAs' => null,
                ],
                (object) [
                    'type' => 'genre',
                    'id' => 'subid3',
                    'key' => 'suburlkey3',
                    'title' => 'subtitle3',
                    'has_topic_page' => false,
                    'sameAs' => null,
                ],
            ],
            'has_topic_page' => false,
            'sameAs' => null,
        ];

        $mapper = new CategoryItemMapper();

        $output = $mapper->getApsObject($genre, false, $subgenres);

        $this->assertObjectHasAttribute('narrower', $output);
        $this->assertObjectNotHasAttribute('broader', $output);
        $this->assertEquals(count($output->{'narrower'}), 3);
        $this->assertEquals($expectedOutput, $output);
    }

    public function testNotMappingFormatBroaderCategory()
    {
        $format = new Format('id', 'title', 'urlkey');

        $expectedOutput = (object) [
            'type' => 'format',
            'id' => 'id',
            'key' => 'urlkey',
            'title' => 'title',
            'broader' => (object) [],
            'has_topic_page' => false,
            'sameAs' => null,
        ];

        $mapper = new CategoryItemMapper();

        $output = $mapper->getApsObject($format, true);

        $this->assertObjectNotHasAttribute('narrower', $output);
        $this->assertEquals($expectedOutput, $output);
    }
}
