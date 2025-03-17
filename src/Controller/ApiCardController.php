<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/card', name: 'api_card_')]
#[OA\Tag(name: 'Card', description: 'Routes for all about cards')]
class ApiCardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {
    }
    #[Route('/all', name: 'List all cards', methods: ['GET'])]
    #[OA\Get(description: 'Return all cards in the database')]
    #[OA\Parameter(name: 'setCode', description: 'Filter by set code', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'page', description: 'Page number', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', description: 'Number of cards per page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 100))]
    #[OA\Response(response: 200, description: 'List all cards')]
    public function cardAll(Request $request): Response
    {
        $setCode = $request->query->get('setCode');
        $page = $request->query->get('page', "1");
        $limit = $request->query->get('limit', "100");
        $page = intval($page);
        $limit = intval($limit);

        $queryBuilder = $this->entityManager->getRepository(Card::class)->createQueryBuilder('c');

        if ($setCode) {
            $queryBuilder->where('c.setCode = :setCode')
                        ->setParameter('setCode', $setCode);
        }

        $totalQuery = clone $queryBuilder;
        $totalNumber = $totalQuery->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $cards = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $res = [
            'currentPage' => $page,
            'totalPages' => ceil($totalNumber / $limit),
            'limit' => $limit,
            'cards' => $cards,
        ];

        $this->logger->info('Liste des cartes', ['setCode' => $setCode ?? 'all', 'page' => $page, 'limit' => $limit]);
        return $this->json($res);
    }


    #[Route('/setCode', name: 'Get set Codes', methods: ['GET'])]
    #[OA\Get(description: 'Return all set codes in the database')]
    #[OA\Response(response: 200, description: 'List all set codes')]
    public function cardSetCode(): Response
    {
        $setCodes = $this->entityManager->getRepository(Card::class)
            ->createQueryBuilder('c')
            ->select('c.setCode')
            ->distinct()
            ->getQuery()
            ->getResult();

        $setCodes = array_map(fn($setCode) => $setCode['setCode'], $setCodes);
        $this->logger->info('Liste des codes de set');
        return $this->json($setCodes);
    }

    #[Route('/{uuid}', name: 'Show card', methods: ['GET'])]
    #[OA\Parameter(name: 'uuid', description: 'UUID of the card', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Get(description: 'Get a card by UUID')]
    #[OA\Response(response: 200, description: 'Show card')]
    #[OA\Response(response: 404, description: 'Card not found')]
    public function cardShow(string $uuid): Response
    {
        $card = $this->entityManager->getRepository(Card::class)->findOneBy(['uuid' => $uuid]);
        if (!$card) {
            $this->logger->error('Carte non trouvée', ['uuid' => $uuid]);
            return $this->json(['error' => 'Card not found'], 404);
        }
        $this->logger->info('Carte affichée', ['uuid' => $uuid]);
        return $this->json($card);
    }

    #[Route('/search/{name}', name: 'Search cards', methods: ['GET'])]
    #[OA\Parameter(name: 'name', description: 'Name of the card', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'setCode', description: 'Filter by set code', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Get(description: 'Search cards by name')]
    #[OA\Response(response: 200, description: 'Show cards')]
    #[OA\Response(response: 404, description: 'Cards not found')]
    public function cardSearch(string $name, Request $request): Response
    {
        $setCode = $request->query->get('setCode');
        $queryBuilder = $this->entityManager->getRepository(Card::class)
            ->createQueryBuilder('c')
            ->where('LOWER(c.name) LIKE LOWER(:name)')
            ->setParameter('name', '%' . $name . '%');

        if ($setCode) {
            $queryBuilder->andWhere('c.setCode = :setCode')
                        ->setParameter('setCode', $setCode);
        }

        $cards = $queryBuilder
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        if (empty($cards)) {
            $this->logger->error('Aucune carte trouvée', ['name' => $name, 'setCode' => $setCode ?? 'all']);
            return $this->json(['error' => 'No cards found'], 404);
        }
        $this->logger->info('Cartes trouvées', ['name' => $name, 'setCode' => $setCode ?? 'all']);
        return $this->json($cards);
    }
}
