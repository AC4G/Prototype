<?php declare(strict_types=1);

namespace App\Controller\Website\Developer;

use App\Entity\User;
use App\Service\PaginationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\OrganisationMemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class DeveloperController extends AbstractController
{
    public function __construct(
        private readonly OrganisationMemberRepository $organisationMemberRepository,
        private readonly PaginationService $paginationService
    )
    {
    }

    #[Route('/developer', name: 'developer')]
    public function developerAction(
        Request $request
    ):Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (is_null($request->query->get('q'))) {
            $organisations = $this->organisationMemberRepository->getOrganisationsByMemberFromCache($user);
        } else {
            $organisations = $this->organisationMemberRepository->getOrganisationsByMemberAndQuery($user, $request->query->get('q'));
        }

        $paginatedOrganisations = $this->paginationService->getDataByPage($organisations, $request->query->all());

        return $this->render('website/developer/index.html.twig', [
            'user' => $this->getUser(),
            'organisations' => $paginatedOrganisations
        ]);
    }


}
