<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\UserAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * Обработчик страницы регистрации
     *
     * @Route("/register", name="app_register")
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        UserAuthenticator $authenticator
    ) {
        // Если пользователь уже авторизован, то запрещаем доступ к этой странице
        if (null != $this->getUser()) {
            return new RedirectResponse($this->generateUrl('home'));
        }
        // Создаем нового пользователя (заготовку) и передаем его на форму
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Если форма заполнена правильно
        if ($form->isSubmitted() && $form->isValid()) {
            // Устанавливаем пользователю ХЭШ по значению его пароля
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            // Устанавливаем пользователю роль пользователя
            $user->setRoles(['ROLE_USER']);

            // Добавляем пользователя в БД и применяем изменения
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main'
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
