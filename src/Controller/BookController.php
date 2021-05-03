<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use App\Service\BookFileUploader;
use App\Service\CoverFileUploader;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/book")
 */
class BookController extends AbstractController
{
    /**
     * @Route("/", name="book_index", methods={"GET"})
     */
    public function index(BookRepository $bookRepository): Response
    {
        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findBy(
                [],
                ['readDate' => 'DESC'],
            ),
        ]);
    }

    /**
     * @Route("/personal", name="book_personal", methods={"GET"})
     */
    public function personal(
        BookRepository $bookRepository,
        Security $security,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        if (null === $security->getUser()) {
            return new RedirectResponse($this->generateUrl('home'));
        }

        $books = $bookRepository->findBy([
            'owner' => $security->getUser(),
        ]);

        $pagination = $paginator->paginate(
            $books,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('book/index.html.twig', [
            'books' => $pagination,
        ]);
    }

    /**
     * @Route("/new", name="book_new", methods={"GET","POST"})
     */
    public function new(
        Request $request,
        Security $security,
        CoverFileUploader $coverFileUploader,
        BookFileUploader $bookFileUploader
    ): Response {
        if (null === $security->getUser()) {
            return new RedirectResponse($this->generateUrl('home'));
        }
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('coverImageFile')->getData();
            $bookFile = $form->get('bookFileFile')->getData();

            if ($coverFile) {
                $coverFileName = $coverFileUploader->upload($coverFile);
                $book->setCoverImage($coverFileName);
            }

            if ($bookFile) {
                $bookFileName = $bookFileUploader->upload($bookFile);
                $book->setBookFile($bookFileName);
            }

            $book->setOwner($security->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('book_show', ['id' => $book->getId()]);
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="book_show", methods={"GET"})
     */
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="book_edit", methods={"GET","POST"})
     */
    public function edit(
        Request $request,
        Book $book,
        Security $security,
        BookFileUploader $bookFileUploader,
        CoverFileUploader $coverFileUploader
    ): Response {
        if ($book->getOwner() !== $security->getUser()) {
            return $this->redirect($this->generateUrl('home'));
        }
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('coverImageFile')->getData();
            $bookFile = $form->get('bookFileFile')->getData();

            if ($book->getCoverImage()) {
                $coverFileUploader->remove($book->getCoverImage());
            }
            if ($coverFile) {
                $book->setCoverImage($coverFileUploader->upload($coverFile));
            } else {
                $book->setCoverImage(null);
            }

            if ($book->getBookFile()) {
                $bookFileUploader->remove($book->getBookFile());
            }
            if ($bookFile) {
                $book->setBookFile($bookFileUploader->upload($bookFile));
            } else {
                $book->setBookFile(null);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="book_delete", methods={"POST"})
     */
    public function delete(
        Request $request,
        Book $book,
        Security $security,
        BookFileUploader $bookFileUploader,
        CoverFileUploader $coverFileUploader
    ): Response {
        if ($book->getOwner() !== $security->getUser()) {
            return $this->redirect($this->generateUrl('home'));
        }

        if ($this->isCsrfTokenValid('delete' . $book->getId(), $request->request->get('_token'))) {
            if ($book->getBookFile()) {
                $bookFileUploader->remove($book->getBookFile());
            }
            if ($book->getCoverImage()) {
                $coverFileUploader->remove($book->getCoverImage());
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('book_index');
    }
}
