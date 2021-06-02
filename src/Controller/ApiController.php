<?php

namespace App\Controller;

use App\Entity\Server;
use App\Repository\ServerRepository;
use App\Service\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController
{
    /**
     * @var ServerRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var Notifier
     */
    private $notifier;


    public function __construct(ServerRepository $repository, EntityManagerInterface $manager, Notifier $notifier)
    {
        $this->repository = $repository;
        $this->manager = $manager;
        $this->notifier = $notifier;
    }

    /**
     * API Server ready action.
     *
     * @Route("/api/{id}/ready")
     */
    public function __invoke(int $id): Response
    {
        $server = $this->repository->find($id);

        if (!$server) {
            return new Response('Server not found', Response::HTTP_NOT_FOUND);
        }

        $server->setState(Server::STATE_READY);

        $this->manager->persist($server);
        $this->manager->flush();

        $this->notifier->notifyReady($server);

        return new Response(null, Response::HTTP_ACCEPTED);
    }
}
