<?php

namespace BBC\CliftonBundle\ApsMapper;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Category;

class TleosSliceByCategoryMapper implements MapperInterface
{
    use Traits\CategoryItemTrait;
    use Traits\ProgrammeUtilitiesTrait;
    use Traits\ServiceTrait;

    public function getApsObject(
        $programmes,
        $medium = null,
        Category $category = null,
        string $slice = '',
        array $subCategories = null
    ) {
        $output = [
            'category_slice' => [
                'slice'      => $slice,
                'category'   => $this->mapCategoryItem($category, true, $subCategories),
                'service'    => $this->mapMediumService($medium),
                'programmes' => $this->getProgramItems($programmes, $medium, $slice),
            ],
        ];

        if (is_null($medium)) {
            unset($output['category_slice']['service']);
        }

        return (object) $output;
    }

    private function getProgramItems($programmes, $medium, $slice)
    {
        $programmesOutput = [];
        foreach ($programmes as $programme) {
            $output = [
                'type'           => $this->getProgrammeType($programme),
                'pid'            => $programme->getPid(),
                'title'          => is_numeric($programme->getTitle()) ? (int) $programme->getTitle() : $programme->getTitle(),
                'short_synopsis' => $this->getShortSynopsys($programme, $medium, $slice),
                'image'          => ['pid' => $programme->getImage()->getPid()],
                'is_available'   => $programme->isStreamable(),
            ];

            if (is_null($output['short_synopsis'])) {
                unset($output['short_synopsis']);
            }

            $programmesOutput[] = (object) $output;
        }

        return $programmesOutput;
    }

    private function getShortSynopsys(Programme $programme, $medium, $slice)
    {
        // aps: show_slice.hash.data#27)
        if (in_array($medium, ['tv', 'radio']) && $slice === 'player') {
            return $programme->getShortSynopsis();
        }

        return null;
    }
}
