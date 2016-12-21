<?php

namespace BBC\CliftonBundle\ApsMapper\Traits;

use InvalidArgumentException;

trait ServiceTrait
{
    private function mapMediumService($medium)
    {
        if ($medium === 'tv') {
            return (object) [
                'key' => 'tv',
                'id' => 'tv',
                'title' => 'BBC TV',
            ];
        } elseif ($medium === 'radio') {
            return (object) [
                'key' => 'radio',
                'id' => 'radio',
                'title' => 'BBC Radio',
            ];
        } elseif (is_null($medium)) {
            return $medium;
        } else {
            throw new InvalidArgumentException(
                sprintf("The service must be either 'tv' or 'radio', instead got '%s'", $medium)
            );
        }
    }
}
