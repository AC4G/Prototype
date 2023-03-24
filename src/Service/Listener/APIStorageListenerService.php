<?php declare(strict_types=1);

namespace App\Service\Listener;

use App\Repository\StorageRepository;
use App\Service\Response\API\CustomResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class APIStorageListenerService
{
    public function __construct(
        private readonly StorageRepository $storageRepository,
        private readonly CustomResponse $customResponse
    )
    {
    }

    public function validateJWTAndParameterForStorageController(
        RequestEvent $event,
        array $accessToken,
        array $params
    ): void
    {
        if (!is_null($accessToken['user']['id'])) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Access token is not based on client credentials grant!'. 400));

            return;
        }

        $projectId = $accessToken['project']['id'];

        if (is_null($this->storageRepository->getStorageByProjectIdAndKeyFromCache($projectId, $params['key'])) && !$event->getRequest()->isMethod('POST')) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('No storage with key %s found!', $params['key']), 404));

            return;
        }

        if (!is_null($this->storageRepository->getStorageByProjectIdAndKeyFromCache($projectId, $params['key'])) && $event->getRequest()->isMethod('POST')) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('Storage with key %s already exists!', $params['key']), 400));

            return;
        }

        if (!($event->getRequest()->isMethod('GET') || $event->getRequest()->isMethod('DELETE'))) {
            $payload = json_decode($event->getRequest()->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Invalid Json!', 406));

                return;
            }

            if (!array_key_exists('value', $payload)) {
                $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Value is required!', 400));

                return;
            }
        }

        $event->getRequest()->attributes->set('projectId', $accessToken['project']['id']);
    }
}
