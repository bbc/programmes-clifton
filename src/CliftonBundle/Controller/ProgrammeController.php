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

    /**
     * @ApiDoc()
     * Helpful feed to see what data we have in the database
     */
    public function allAction(
        Request $request
    ): Response {
        $programmesService = $this->get('clifton.programmes_service');
        $findByPidProgrammeMapper = $this->get('clifton.find_by_pid_programme_mapper');
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);

        $programmeResult = $programmesService->findAll($limit, $page);
        $results = [];
        foreach ($programmeResult as $programme) {
            $results[] = $this->mapSingleApsObject(
                $findByPidProgrammeMapper,
                $programme
            );
        }

        return $this->jsonResponse($results);
    }
}
