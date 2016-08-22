<?php
    namespace BBC\CliftonBundle\ApsMapper\Traits;

    use BBC\ProgrammesPagesService\Domain\Entity\Segment;

trait SegmentUtilitiesTrait
{
    protected function getSegmentTitle(Segment $segment)
    {

        $title = $segment->getTitle();
        if ($title === null) {
            return "Untitled";
        }

        // Mimics an APS bug: if the title is purely numeric, the output is a number
        // instead of a string. e.g. http://open.live.bbc.co.uk/aps/programmes/b008hskr.json
        return is_numeric($title) ? (float) $title : $title;
    }
}
