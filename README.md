## Лабораторная работа №6 по PHP

### Задание:

Проект:
Свой мини-MVC проект с функционалом CRUD операций. Можно и нужно использовать отдельные Symfony Components Личная
библиотека книг

1. Сделать в проекте авторизацию
2. Главная страница
    - Выводим список книг в порядке прочтения с указанием названия (неавторизованный режим)
    - У каждой книги кроме описанного выше выводятся ссылки "Редактировать" и "Удалить" (авторизованный режим)
    - Также в начале страницы выводится ссылка "Добавить книгу" (авторизованный режим)
3. Страница "Добавить книгу"
    - Название
    - Автор
    - Обложка (png, jpg)
    - Файл с книгой (до 5мб)
    - Дата прочтения
4. Страница редактирования книги аналогично созданию, но в форме отображаются текущие данные по книге, которые можно
   изменить. Файлы обложки и книги в режиме редактирования можно удалить.

### Ход работы:

Для выполнения данной работы была сформирована следующая структура проекта:

```
├── README.md
├── bin
│   ├── console
│   └── phpunit
├── composer.json
├── composer.lock
├── config
│   ├── bundles.php
│   ├── packages
│   │   ├── cache.yaml
│   │   ├── dev
│   │   │   ├── debug.yaml
│   │   │   ├── monolog.yaml
│   │   │   └── web_profiler.yaml
│   │   ├── doctrine.yaml
│   │   ├── doctrine_migrations.yaml
│   │   ├── framework.yaml
│   │   ├── mailer.yaml
│   │   ├── notifier.yaml
│   │   ├── paginator.yaml
│   │   ├── prod
│   │   │   ├── deprecations.yaml
│   │   │   ├── doctrine.yaml
│   │   │   ├── monolog.yaml
│   │   │   └── routing.yaml
│   │   ├── routing.yaml
│   │   ├── security.yaml
│   │   ├── sensio_framework_extra.yaml
│   │   ├── test
│   │   │   ├── doctrine.yaml
│   │   │   ├── framework.yaml
│   │   │   ├── monolog.yaml
│   │   │   ├── twig.yaml
│   │   │   ├── validator.yaml
│   │   │   └── web_profiler.yaml
│   │   ├── translation.yaml
│   │   ├── twig.yaml
│   │   └── validator.yaml
│   ├── preload.php
│   ├── routes
│   │   ├── annotations.yaml
│   │   └── dev
│   │       ├── framework.yaml
│   │       └── web_profiler.yaml
│   ├── routes.yaml
│   └── services.yaml
├── docker
│   ├── nginx
│   │   ├── Dockerfile
│   │   └── default.conf
│   ├── php-fpm
│   │   ├── Dockerfile
│   │   └── php.ini
│   └── postgres
│       ├── Dockerfile
│       └── db.env
├── docker-compose.yaml
├── migrations
│   └── Version20210503183538.php
├── phpcs.xml.dist
├── phpunit.xml.dist
├── public
│   ├── index.php
│   ├── js
│   │   └── add-files.js
│   └── style
│       ├── style.css
│       ├── style.css.map
│       └── style.scss
├── src
│   ├── Controller
│   │   ├── BookController.php
│   │   ├── HomeController.php
│   │   ├── RegistrationController.php
│   │   └── SecurityController.php
│   ├── DataFixtures
│   │   └── AppFixtures.php
│   ├── Entity
│   │   ├── Book.php
│   │   └── User.php
│   ├── Form
│   │   ├── BookEditFormType.php
│   │   ├── BookType.php
│   │   └── RegistrationFormType.php
│   ├── Kernel.php
│   ├── Repository
│   │   ├── BookRepository.php
│   │   └── UserRepository.php
│   ├── Security
│   │   └── UserAuthenticator.php
│   └── Service
│       ├── BaseFileUploader.php
│       ├── BookFileUploader.php
│       └── CoverFileUploader.php
├── symfony.lock
├── templates
│   ├── base.html.twig
│   ├── book
│   │   ├── _delete_form.html.twig
│   │   ├── _form.html.twig
│   │   ├── edit.html.twig
│   │   ├── index.html.twig
│   │   ├── new.html.twig
│   │   └── show.html.twig
│   ├── components
│   │   ├── book-card.html.twig
│   │   └── navbar.html.twig
│   ├── home
│   │   └── index.html.twig
│   ├── registration
│   │   └── register.html.twig
│   └── security
│       └── login.html.twig
├── tests
│   └── bootstrap.php
└── translations
    ├── KnpPaginatorBundle.en.yaml
    ├── messages.en.yaml
    ├── security.en.yaml
    └── validators.en.yaml

32 каталога, 88 файлов
```

Для запуска проекта необходимо настроить файл `.env`, а конкретнее — указать строку для подключения к БД.

Для запуска данного проекта необходим установленный Docker и docker-compose.

1. Перейдите в каталог `PersonalLibrary` и выполните команду `docker-compose up -d`.
2. После успешного запуска форма будет доступна по [этому](http://localhost:80/) адресу.
3. Подключитесь к контейнеру с сайтом, по умолчанию его название: `personal_lib_site`. Для этого воспользуйтесь
   следующей командой:
   ```bash
   docker exec -it personal_lib_site bash
   ```
4. Примените миграции к БД с помощью следующей команды: `symfony console doctrine:migrations:migrate`
5. (по желанию) Загрузите фикстуры в БД с помощью следующей команды: `symfony console doctrine:fixtures:load`

#### Данные пользователей после применения фикстур
| Фамилия | Имя  | Отчество | **Email**        | **Пароль**   |
| :-----: |:----:| :-------:| :--------------: | :----------: |
| Иванов  | Иван | Иванович | `ivanov@mail.ru` | `ivanov1231` |
| Петров  | Петр |          | `petrov@mail.ru` | `petrRr111`  |