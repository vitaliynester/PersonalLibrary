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
     * @Route("/", name="home")
     * @param BookRepository $bookRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(BookRepository $bookRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $books = $bookRepository->findBy(
            [],
            ['readDate' => 'DESC'],
        );
        $pagination = $paginator->paginate(
            $books,
            $request->query->getInt('page', 1),
            12
        );
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'books' => $pagination,
        ]);
    }
}
