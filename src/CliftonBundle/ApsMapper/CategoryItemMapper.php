<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Format;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use InvalidArgumentException;

class CategoryItemMapper implements MapperInterface
{
    public function getApsObject($category, bool $mapBroader = false, array $subcategories = null)
    {
        /** @var Genre|Format $category */
        $this->assertIsCategory($category);

        $output = [
            'type' => $category instanceof Genre ? 'genre' : 'format',
            'id' => $category->getId(),
            'key' => $category->getUrlKey(),
            'title' => $category->getTitle(),
            'narrower' => empty($subcategories) ? $subcategories : array_map([$this, 'getApsObject'], $subcategories),
            'broader' => (object) [],
            'has_topic_page' => false,
            'sameAs' => null,
        ];

        if (is_null($output['narrower'])) {
            unset($output['narrower']);
        }

        if ($mapBroader) {
            // FORMATS WON'T EVER HAVE PARENTS. GOOD NIGHT.
            if (!($category instanceof Format)) {
                $output['broader'] = !is_null($category->getParent()) ?
                    $this->getApsObject($category->getParent(), true) :
                    (object) [];
            }
        } else {
            unset($output['broader']);
        }

        return (object) $output;
    }

    private function assertIsCategory($item)
    {
        if (!(($item instanceof Format) || ($item instanceof Genre))) {
            throw new InvalidArgumentException(sprintf(
                'Entity should be an instance of "%s or %s". Got "%s"',
                'BBC\\ProgrammesPagesService\\Domain\\Entity\\Format',
                'BBC\\ProgrammesPagesService\\Domain\\Entity\\Genre',
                (is_object($item) ? get_class($item) : gettype($item))
            ));
        }
    }
}
