<?php

namespace App\Controller;

use App\Entity\Server;
use App\Form\Type\ProfileType;
use App\Form\Type\ServerType;
use App\Repository\ServerRepository;
use App\Service\Generator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/account", name="account_")
 */
class AccountController extends AbstractController
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
     * @var Generator
     */
    private $generator;


    public function __construct(ServerRepository $repository, EntityManagerInterface $manager, Generator $generator)
    {
        $this->repository = $repository;
        $this->manager = $manager;
        $this->generator = $generator;
    }

    /**
     * @Route("/", name="dashboard")
     */
    public function dashboard(): Response
    {
        $servers = $this->repository->findBy([
            'user' => $this->getUser(),
        ]);

        return $this->render('account/dashboard.html.twig', [
            'servers' => $servers,
        ]);
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profile(Request $request): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($user);
            $this->manager->flush();

            return $this->redirectToRoute('account_dashboard');
        }

        return $this->render('account/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * New server action.
     *
     * @Route("/new-server", name="new_server")
     */
    public function serverNew(Request $request): Response
    {
        $server = new Server();
        /** @noinspection PhpParamsInspection */
        $server->setUser($this->getUser());

        $form = $this->createForm(ServerType::class, $server);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->generator->generateName($server);
            $this->generator->generateIp($server);

            $this->manager->persist($server);
            $this->manager->flush();

            return $this->redirectToRoute('account_dashboard');
        }

        return $this->render('account/new-server.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Server detail action.
     *
     * @Route("/{id}", name="server_detail", requirements={"id": "\d+"})
     */
    public function serverDetail(int $id): Response
    {
        $server = $this->findServer($id);

        return $this->render('account/server-detail.html.twig', [
            'server' => $server,
        ]);
    }

    /**
     * Server reboot action.
     *
     * @Route("/{id}/reboot", name="server_reboot", requirements={"id": "\d+"})
     */
    public function serverReboot(int $id): Response
    {
        $server = $this->findServer($id);

        $server->setState(Server::STATE_STOPPED);

        $this->manager->persist($server);
        $this->manager->flush();

        $this->addFlash('warning', sprintf('Server %s has been restarted.', $server->getName()));

        return $this->redirectToRoute('account_server_detail', [
            'id' => $server->getId(),
        ]);
    }

    /**
     * Server reset action.
     *
     * @Route("/{id}/reset", name="server_reset", requirements={"id": "\d+"})
     */
    public function serverReset(int $id): Response
    {
        $server = $this->findServer($id);

        $server->setState(Server::STATE_PENDING);

        $this->manager->persist($server);
        $this->manager->flush();

        $this->addFlash('warning', sprintf('Server %s has been reset.', $server->getName()));

        return $this->redirectToRoute('account_server_detail', [
            'id' => $server->getId(),
        ]);
    }

    /**
     * Server reset action.
     *
     * @Route("/{id}/delete", name="server_delete", requirements={"id": "\d+"}, methods={"POST"})
     */
    public function serverDelete(Request $request): Response
    {
        $server = $this->findServer(
            $request->attributes->get('id')
        );

        if (1 !== $request->request->getInt('delete')) {
            return $this->redirectToRoute('account_server_detail', [
                'id' => $server->getId(),
            ]);
        }

        $this->manager->remove($server);
        $this->manager->flush();

        $this->addFlash('warning', sprintf('Server %s has been deleted.', $server->getName()));

        return $this->redirectToRoute('account_dashboard');
    }

    /**
     * Finds the server by its id.
     */
    private function findServer(int $id): Server
    {
        $server = $this->repository->findOneBy([
            'id'   => $id,
            'user' => $this->getUser(),
        ]);

        if (!$server) {
            throw $this->createNotFoundException('Server not found');
        }

        return $server;
    }
}
