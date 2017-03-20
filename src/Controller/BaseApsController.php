<?php

namespace BBC\CliftonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use BBC\CliftonBundle\ApsMapper\MapperInterface;

abstract class BaseApsController extends Controller
{
    protected function json($data, $status = 200, $headers = [], $context = [])
    {
        return new JsonResponse($data, $status, $headers, $context);
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

    protected function mapSingleApsObject(MapperInterface $apsMapper, $domainEntity, ...$additionalArgs)
    {
        if (is_null($domainEntity)) {
            return null;
        }

        return $apsMapper->getApsObject($domainEntity, ...$additionalArgs);
    }

    protected function mapManyApsObjects(MapperInterface $apsMapper, $domainEntities)
    {
        $apsObjects = [];
        foreach ($domainEntities as $domainEntity) {
            $mappedObject = $this->mapSingleApsObject($apsMapper, $domainEntity);

            if (is_array($mappedObject)) {
                foreach ($mappedObject as $item) {
                    $apsObjects[] = $item;
                }
            } else {
                $apsObjects[] = $mappedObject;
            }
        }
        return $apsObjects;
    }
}
