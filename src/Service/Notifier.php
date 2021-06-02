<?php

namespace App\Service;

use App\Entity\Server;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class Notifier
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $adminEmail;


    /**
     * @param MailerInterface $mailer
     * @param Environment     $twig
     * @param string          $adminEmail     -> voir config/services.yaml
     */
    public function __construct(MailerInterface $mailer, Environment $twig, string $adminEmail)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->adminEmail = $adminEmail;
    }

    public function notifyContact(array $contact): void
    {
        $html = $this->twig->render('email/contact.html.twig', $contact);

        $email = new Email();
        $email
            ->sender(new Address($contact['email'], $contact['name']))
            ->to(new Address($this->adminEmail, 'SeaCloud'))
            ->subject($contact['subject'])
            ->html($html);

        $this->mailer->send($email);
    }

    public function notifyReady(Server $server): void
    {
        $html = $this->twig->render('email/ready.html.twig', [
            'server' => $server,
        ]);

        $email = new Email();
        $email
            ->sender(new Address($this->adminEmail, 'SeaCloud'))
            ->to($server->getUser()->getEmail())
            ->subject('Votre serveur est prêt !')
            ->html($html);

        $this->mailer->send($email);
    }
}
