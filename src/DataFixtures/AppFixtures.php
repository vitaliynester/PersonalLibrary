<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    // Сущность для преобразования "сырого" пароля в захэшированный вид
    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * UserFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder (сущность для преобразования паролей в правильный вид для БД)
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->passwordEncoder = $encoder;
    }

    /**
     * Метод для загрузки фикстуры (подготовленных данных) в базу данных
     * @param ObjectManager $manager (менеджер доктрины для взаимодействия с БД)
     */
    public function load(ObjectManager $manager)
    {
        // Создание первого пользователя
        $firstUser = new User();
        $firstUser->setLastName('Иванов');
        $firstUser->setFirstName('Иван');
        $firstUser->setPatronymic('Иванович');
        $firstUser->setEmail('ivanov@mail.ru');
        $firstUser->setPassword($this->passwordEncoder->encodePassword($firstUser, 'ivanov1231'));
        $firstUser->setRoles(['ROLE_USER']);

        // Создание второго пользователя
        $secondUser = new User();
        $secondUser->setLastName('Петров');
        $secondUser->setFirstName('Петр');
        $secondUser->setEmail('petrov@mail.ru');
        $secondUser->setPassword($this->passwordEncoder->encodePassword($secondUser, 'petrRr111'));
        $secondUser->setRoles(['ROLE_USER']);

        // Добавляем записи в БД
        $manager->persist($firstUser);
        $manager->persist($secondUser);

        // Коммитим изменения в БД
        $manager->flush();
    }
}
