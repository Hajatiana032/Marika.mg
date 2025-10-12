<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, FileUploader $fileUploader, SluggerInterface $slugger): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setSlug($slugger->slug($user->getUsername())->lower());

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('marika@email.com', 'Marika.mg'))
                    ->to((string) $user->getEmail())
                    ->subject('Veuillez confirmer votre adresse email')
                    ->context(['user' => $user])
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('success', ['color' => 'white', 'message' => 'Votre compte a été créé avec succès. Veuillez confirmer votre adresse email pour pouvoir vous connecter.']);
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'current_menu' => 'register',
        ]);
    }

    #[Route('/vérification/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_home');
        }

        $user = $userRepository->find($id);
        if (null === $user) {
            return $this->redirectToRoute('app_home');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
            /** @var User $user */
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('danger', [
                'color' => 'light',
                'message' => "Le lien de vérification a expiré. Veuillez demander un nouveau lien en cliquant <a href=\"{$this->generateUrl('app_resend_verification_email', ['id' =>$user->getId()])}\" class=\"link-emerald-500\">ici</a>."
            ]);

            return $this->redirectToRoute('app_login');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', ['color' => 'white', 'message' => 'Votre adresse email a été vérifié, vous pouvez maintenant vous connecter.']);

        return $this->redirectToRoute('app_login');
    }

    #[Route('/nouveau_lien/{id}', name: 'app_resend_verification_email')]
    public function resend(User $user): Response
    {
        if ($user->isVerified()) {
            $this->addFlash('info', [
                'color' => 'info',
                'message' => 'Votre adresse email est déjà vérifiée.'
            ]);
            return $this->redirectToRoute('app_login');
        }

        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('marika@email.com', 'Marika.mg'))
                ->to((string) $user->getEmail())
                ->subject('Veuillez confirmer votre adresse email')
                ->context(['user' => $user])
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        $this->addFlash('success', [
            'color' => 'white',
            'message' => 'Un nouveau lien de vérification a été envoyé à votre adresse email.'
        ]);

        return $this->redirectToRoute('app_login');
    }
}
