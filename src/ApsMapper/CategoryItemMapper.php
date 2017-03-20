<?php

namespace BBC\CliftonBundle\ApsMapper;

class CategoryItemMapper implements MapperInterface
{
    use Traits\CategoryItemTrait;

    public function getApsObject($category, bool $mapBroader = false, array $subcategories = null)
    {
        return $this->mapCategoryItem($category, $mapBroader, $subcategories);
    }
}
