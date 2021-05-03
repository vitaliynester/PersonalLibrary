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
 * Контроллер для обработки всех запросов связанных с книгами
 *
 * @Route("/book")
 */
class BookController extends AbstractController
{
    /**
     * Обработка индексного запроса для книг (вывод всех книг)
     *
     * @Route("/", name="book_index", methods={"GET"})
     */
    public function index(BookRepository $bookRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // Находим все книги в порядке прочтения
        $books = $bookRepository->findBy(
            [],
            ['readDate' => 'DESC'],
        );
        // Создаем пагинацию по всем книгам (на одной странице 12 штук)
        $pagination = $paginator->paginate(
            $books,
            $request->query->getInt('page', 1),
            12
        );
        // Передаем в шаблон все книги отсортированные по дате прочтения
        return $this->render('book/index.html.twig', [
            'books' => $pagination,
        ]);
    }

    /**
     * Обработка запроса на получение книг конкретного пользователя
     *
     * @Route("/personal", name="book_personal", methods={"GET"})
     */
    public function personal(
        BookRepository $bookRepository,
        Security $security,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        // Проверяем, что пользователь авторизован
        if (null === $security->getUser()) {
            return new RedirectResponse($this->generateUrl('home'));
        }

        // Если пользователь авторизован, то получаем все книги пользователя
        $books = $bookRepository->findBy(['owner' => $security->getUser(),],['readDate' => 'DESC']);

        // Создаем пагинацию по всем книгам (на одной странице 12 штук)
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
     * Обработчик запроса на создание новой книги
     *
     * @Route("/new", name="book_new", methods={"GET","POST"})
     */
    public function new(
        Request $request,
        Security $security,
        CoverFileUploader $coverFileUploader,
        BookFileUploader $bookFileUploader
    ): Response
    {
        // Проверяем, что пользователь авторизован
        if (null === $security->getUser()) {
            return new RedirectResponse($this->generateUrl('home'));
        }
        // Создаем новую книгу (заготовку)
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        // Проверяем, что форма заполнена верно
        if ($form->isSubmitted() && $form->isValid()) {
            // Получаем файлы с формы
            $coverFile = $form->get('coverImageFile')->getData();
            $bookFile = $form->get('bookFileFile')->getData();

            // Если обложка для книги установлена, то
            if ($coverFile) {
                // Сохраняем обложку книги в папку uploads/cover/
                $coverFileName = $coverFileUploader->upload($coverFile);
                // Устанавливаем название файла для обложки книги
                $book->setCoverImage($coverFileName);
            }

            // Если файл с книгой установлен, то
            if ($bookFile) {
                // Сохраняем книгу в папку uploads/book/
                $bookFileName = $bookFileUploader->upload($bookFile);
                // Устанавливаем название книги для сущности
                $book->setBookFile($bookFileName);
            }

            // Устанавливаем владельца книги пользователя, который создал данную запись
            $book->setOwner($security->getUser());
            // Добавляем полученную сущность в БД
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($book);
            $entityManager->flush();

            // Перенаправляем на страницу с созданной книгой
            return $this->redirectToRoute('book_show', ['id' => $book->getId()]);
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Обработчик запроса на получение конкретной книги
     *
     * @Route("/{id}", name="book_show", methods={"GET"})
     */
    public function show(Book $book): Response
    {
        // Передаем в форму книгу с указанным ID
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    /**
     * Обработчик запроса на редактирование конкретной книги
     *
     * @Route("/{id}/edit", name="book_edit", methods={"GET","POST"})
     */
    public function edit(
        Request $request,
        Book $book,
        Security $security,
        BookFileUploader $bookFileUploader,
        CoverFileUploader $coverFileUploader
    ): Response
    {
        // Проверяем, что создатель данной записи о книге это пользователь, который перешел по ссылке
        if ($book->getOwner() !== $security->getUser()) {
            return $this->redirect($this->generateUrl('home'));
        }
        // Создаем форму и передаем в неё значения из сущности
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        // Если форма заполнена правильно
        if ($form->isSubmitted() && $form->isValid()) {
            // Получаем новый файл обложки книги
            $coverFile = $form->get('coverImageFile')->getData();
            // Получаем новый файл с книгой
            $bookFile = $form->get('bookFileFile')->getData();

            // Если в книге уже была установлена обложка, то
            if ($book->getCoverImage()) {
                // Удаляем её
                $coverFileUploader->remove($book->getCoverImage());
            }
            // Если из формы редактирования была передана новая обложка для книги, то
            if ($coverFile) {
                // Сохраняем данную обложку на устройстве и передаем её название в сущность
                $book->setCoverImage($coverFileUploader->upload($coverFile));
            } else {
                // Если пользователь при редактировании не указал новую обложку, то устанавливаем текущее значение нулом
                $book->setCoverImage(null);
            }

            // Если в книге уже был установлен файл с книгой, то
            if ($book->getBookFile()) {
                // Удаляем её
                $bookFileUploader->remove($book->getBookFile());
            }
            // Если из формы редактирования был передан файл с книгой, то
            if ($bookFile) {
                // Сохраняем книгу на устройстве и передаем её название в сущность книги
                $book->setBookFile($bookFileUploader->upload($bookFile));
            } else {
                // Если пользователь при редактировании не указал новый файл книги, то
                // устанавливаем название книги как нул
                $book->setBookFile(null);
            }

            // Сохраняем изменения сущности в БД
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Обработчик запроса на удаление конкретной книги
     *
     * @Route("/{id}", name="book_delete", methods={"POST"})
     */
    public function delete(
        Request $request,
        Book $book,
        Security $security,
        BookFileUploader $bookFileUploader,
        CoverFileUploader $coverFileUploader
    ): Response
    {
        // Проверяем, что создатель данной записи о книге это пользователь, который перешел по ссылке
        if ($book->getOwner() !== $security->getUser()) {
            return $this->redirect($this->generateUrl('home'));
        }

        if ($this->isCsrfTokenValid('delete' . $book->getId(), $request->request->get('_token'))) {
            // Если был установлен файл книги, то
            if ($book->getBookFile()) {
                // Удаляем её
                $bookFileUploader->remove($book->getBookFile());
            }
            // Если был установлен файл обложки книги, то
            if ($book->getCoverImage()) {
                // Удаляем её
                $coverFileUploader->remove($book->getCoverImage());
            }

            // Удаляем запись из БД и сохраняем
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('book_index');
    }
}
