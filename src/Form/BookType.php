<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'required' => true,
                'constraints' => [
                    new Length([
                        'maxMessage' => 'Максимальное название книги 255 символов!',
                        'max' => 255,
                    ]),
                ],
                'label' => 'Name',
                'attr' => [
                    'class' => 'validate',
                ],
            ])
            ->add('author', null, [
                'required' => true,
                'constraints' => [
                    new Length([
                        'maxMessage' => 'Максимальное имя автора 255 символов!',
                        'max' => 255,
                    ]),
                ],
                'label' => 'Author',
                'attr' => [
                    'class' => 'validate',
                ],
            ])
            ->add('coverImageFile', FileType::class, [
                'label' => 'Book cover',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Некорректный тип файла для обложки книги!',
                        'maxSizeMessage' => 'Максимальный размер фото 5 Мб!',
                    ]),
                ],
            ])
            ->add('bookFileFile', FileType::class, [
                'label' => 'Book file',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'text/xml',
                            'text/plain',
                            'text/markdown',
                            'application/vnd.oasis.opendocument.text',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        'mimeTypesMessage' => 'Некорректный тип файла для книги!',
                        'maxSizeMessage' => 'Максимальный размер книги 5 Мб!',
                    ]),
                ],
            ])
            ->add('readDate', DateType::class, [
                'label' => 'Дата прочтения книги',
                'mapped' => true,
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
