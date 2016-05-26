<?php

namespace BBC\CliftonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use BBC\CliftonBundle\ApsMapper\MapperInterface;

abstract class BaseApsController extends Controller
{
    protected function json($data)
    {
        return new JsonResponse($data);
    }

    protected function queryParamToInt(Request $request, string $param, int $default, int $min = null, int $max = null)
    {
        $options = ['default' => $default];

        if (!is_null($min)) {
            $options['min_range'] = $min;
        }

        if (!is_null($max)) {
            $options['max_range'] = $max;
        }

        return (int) $request->query->filter(
            $param,
            null,
            FILTER_VALIDATE_INT,
            [ 'options' => $options ]
        );
    }

    protected function mapSingleApsObject(MapperInterface $apsMapper, $domainEntity)
    {
        if (is_null($domainEntity)) {
            return null;
        }

        return $apsMapper->getApsObject($domainEntity);
    }

    protected function mapManyApsObjects(MapperInterface $apsMapper, $domainEntities)
    {
        $apsObjects = [];
        foreach ($domainEntities as $domainEntity) {
            $apsObjects[] = $this->mapSingleApsObject($apsMapper, $domainEntity);
        }
        return $apsObjects;
    }
}
