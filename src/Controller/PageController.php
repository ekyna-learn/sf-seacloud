<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\SignUpType;
use App\Service\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

class PageController extends AbstractController
{
    /**
     * Home action.
     *
     * @Route("/", name="page_home")
     */
    public function home(): Response
    {
        return $this->render('page/home.html.twig');
    }

    /**
     * About action.
     *
     * @Route("/about", name="page_about")
     */
    public function about(): Response
    {
        return $this->render('page/about.html.twig');
    }

    /**
     * Services action.
     *
     * @Route("/services", name="page_services")
     */
    public function services(): Response
    {
        return $this->render('page/services.html.twig');
    }

    /**
     * Contact action.
     *
     * @Route("/contact", name="page_contact")
     */
    public function contact(Request $request, Notifier $notifier): Response
    {
        $form = $this->createContactForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notifier->notifyContact($form->getData());

            $this->addFlash('success', 'You email has been sent.');

            return $this->redirectToRoute('page_contact');
        }

        return $this->render('page/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates the contact form.
     */
    private function createContactForm(): FormInterface
    {
        return $this
            ->createFormBuilder(null, [
                'attr' => [
                    'class' => 'contact-form',
                ],
            ])
            ->add('name', Type\TextType::class, [
                'label' => false,
                'attr'  => [
                    'placeholder' => 'Your Name',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('email', Type\EmailType::class, [
                'label' => false,
                'attr'  => [
                    'placeholder' => 'Your Email',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('subject', Type\TextType::class, [
                'label' => false,
                'attr'  => [
                    'placeholder' => 'Subject',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('message', Type\TextareaType::class, [
                'label' => false,
                'attr'  => [
                    'placeholder' => 'Message',
                    'rows'        => 5,
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->getForm();
    }

    /**
     * Signup action.
     *
     * @Route("/sign-up", name="page_signup")
     */
    public function signUp(Request $request, EntityManagerInterface $manager): Response
    {
        $user = new User();

        $form = $this->createForm(SignUpType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Your account has been created ! You can now sign in.');

            return $this->redirectToRoute('security_signin');
        }

        return $this->render('page/sign-up.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
