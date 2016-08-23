<?php
namespace BBC\CliftonBundle\ApsMapper\Traits;

trait SegmentUtilitiesTrait
{
    protected function getAsNumberOrString(string $string = null)
    {
        if ($string == null) {
            return "";
        }

        return is_numeric($string) ? (float) $string : $string;
    }
}
