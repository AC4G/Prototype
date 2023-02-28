<?php declare(strict_types=1);

namespace App\Controller\Website;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    public function __construct(

    )
    {
    }

    #[Route('/', name: 'home')]
    public function showHomeTemplate(
        Request $request
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!is_null($request->getSession()->get('redirect'))) {
            $request->getSession()->remove('redirect');
        }

        return $this->render('website/home/index.html.twig', [
            'user' => $user
        ]);
    }


}
