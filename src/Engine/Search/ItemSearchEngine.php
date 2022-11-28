<?php declare(strict_types=1);

namespace App\Engine\Search;

use Doctrine\ORM\Query;
use App\Repository\ItemRepository;
use App\Service\API\Items\ItemsService;
use Symfony\Component\Security\Core\User\UserInterface;

final class ItemSearchEngine extends AbstractSearchEngine
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
        private readonly ItemsService $itemsService
    )
    {
        parent::__construct(
            $this->itemsService
        );
    }

    public function search(
        array $query,
        ?UserInterface $user = null
    ): array
    {
        $phrase = array_key_exists('search', $query) ? $query['search'] : null;

        if ($this->isPhraseNullOrHasNoCharacters($phrase)) return $this->itemsService->prepareData($this->itemRepository->findBy(['user' => $user]));

        $phraseWithParameter = ($this->phraseContainsColon($phrase)) ? explode(':', $phrase) : '';

        if ($this->isParameterLongerThanZero($phraseWithParameter[1])) {
            $parameters = $this->preparePhraseParameter($phraseWithParameter[1]);
            $items = $this->buildQuery($phraseWithParameter[0], $user)->execute();

            return $this->findItemByParameter($items, $parameters);
        }

        return $this->itemsService->prepareData($this->buildQuery($phrase, $user)->execute());
    }

    private function buildQuery(
        string $phrase,
        ?UserInterface $user = null,
        array $context = [],
    ): Query
    {
        $query = $this->itemRepository->createQueryBuilder('i')
                ->where('MATCH (i.name) AGAINST (:phrase IN BOOLEAN MODE) > 0')
                ->setParameter('phrase', (count(explode(' ', $phrase)) === 1) ? '\'' . $phrase . '*\'' : '"' . $phrase . '"')
        ;

        if (is_null($user))
            $query
                ->andWhere('i.user = :user')
                ->setParameter('user', $user)
        ;

        return $query->getQuery();
    }

    private function phraseContainsColon(
        string $haystack
    ): bool
    {
        return strpos($haystack, ':') > 0;
    }

    private function isParameterLongerThanZero(
        string $parameter
    ): bool
    {
        return strlen($parameter) > 0;
    }

    private function isPhraseNullOrHasNoCharacters(
        ?string $phrase
    ): bool
    {
        return is_null($phrase) || strlen($phrase) === 0;
    }


}
