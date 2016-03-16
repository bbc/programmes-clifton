<?php

namespace BBC\CliftonBundle\Controller;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProgrammeController extends BaseApsController
{
    public function findByPidAction(
        Request $request,
        string $pid
    ): Response {
        // @todo - catch if this is invalid
        $pid = new Pid($pid);

        $programmesService = $this->get('clifton.programmes_service');
        $findByPidProgrammeMapper = $this->get('clifton.find_by_pid_programme_mapper');

        $programme = $programmesService->findByPidFull($pid);

        if (is_null($programme)) {
            throw $this->createNotFoundException(sprintf('The programme with PID "%s" was not found', $pid));
        }

        $descendantsResult = $programmesService->findDescendantsByPid($programme->getPid());

        $apsProgramme = $this->mapSingleApsObject(
            $findByPidProgrammeMapper,
            $programme
        );

        return $this->jsonResponse([
            'programme' => $apsProgramme,
        ]);
    }
}
