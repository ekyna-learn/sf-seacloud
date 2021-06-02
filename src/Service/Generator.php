<?php


namespace App\Service;

use App\Entity\Server;
use App\Repository\ServerRepository;

use function sprintf;

class Generator
{
    /**
     * @var ServerRepository
     */
    private $repository;

    /**
     * @param ServerRepository $repository
     */
    public function __construct(ServerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Generates the server name.
     *
     * @param Server $server
     */
    public function generateName(Server $server)
    {
        if (!empty($server->getName())) {
            return;
        }

        $base = sprintf(
            '%s-%s',
            $server->getLocation()->getCode(),
            $server->getDistribution()->getCode()
        );

        $count = 0;
        do {
            $count++;
            $name = sprintf('%s-%s', $base, str_pad($count, 2, '0', STR_PAD_LEFT));
        } while(null !== $this->repository->findOneByName($name));

        $server->setName($name);

    }

    /**
     * Generates the server IP.
     *
     * @param Server $server
     */
    public function generateIp(Server $server)
    {
        if (!empty($server->getIp())) {
            return;
        }

        $server->setIp(sprintf(
            '%s.%s.%s.%s',
            rand(1, 255),
            rand(1, 255),
            rand(1, 255),
            rand(1, 255)
        ));
    }
}
