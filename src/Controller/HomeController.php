<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Обработчик главной страницы
     *
     * @Route("/", name="home")
     *
     * @param BookRepository $bookRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @return Response
     */
    public function index(BookRepository $bookRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // Получаем список всех книг с сортировкой по дате прочтения
        $books = $bookRepository->findBy(
            [],
            ['readDate' => 'DESC'],
        );

        // Получаем разбивку по страницам для всех книг
        $pagination = $paginator->paginate(
            $books,
            $request->query->getInt('page', 1),
            12
        );

        // Передаем в шаблон список книг на странице
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'books' => $pagination,
        ]);
    }
}
