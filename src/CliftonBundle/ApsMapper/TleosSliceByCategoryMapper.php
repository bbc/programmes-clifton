<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Category;

class TleosSliceByCategoryMapper implements MapperInterface
{
    use Traits\CategoryItemTrait;
    use Traits\ProgrammeUtilitiesTrait;

    public function getApsObject(
        $programmes,
        Category $category = null,
        string $slice = '',
        array $subCategories = null
    ) {
        $output = [
            'category_slice' => [
                'slice'      => $slice,
                'category'   => $this->mapCategoryItem($category, true, $subCategories),
                'programmes' => $this->getProgrammeItems($programmes, $slice),
            ],
        ];

        return (object) $output;
    }

    private function getProgrammeItems($programmes, $slice)
    {
        $programmesOutput = [];
        foreach ($programmes as $programme) {
            $programmesOutput[] = (object) [
                'type'           => $this->getProgrammeType($programme),
                'pid'            => $programme->getPid(),
                'title'          => is_numeric($programme->getTitle()) ? (int) $programme->getTitle() : $programme->getTitle(),
                'image'          => ['pid' => $programme->getImage()->getPid()],
                'is_available'   => $programme->isStreamable(),
            ];
        }

        return $programmesOutput;
    }
}
