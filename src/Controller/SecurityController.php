<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Обработчик запроса на авторизацию
     *
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Запрещаем доступ уже авторизованному пользователю
        if (null != $this->getUser()) {
            return new RedirectResponse($this->generateUrl('home'));
        }

        // Получаем ошибки авторизации, если они есть
        $error = $authenticationUtils->getLastAuthenticationError();
        // Получаем фамилию пользователя
        $lastUsername = $authenticationUtils->getLastUsername();

        // Передаем полученные данные в шаблон
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * Обработчик запроса на выход из аккаунта
     *
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
    }
}
