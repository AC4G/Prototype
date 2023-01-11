<?php declare(strict_types=1);

namespace App\Form\Registration;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

final class RegistrationFormType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    )
    {
        $builder
            ->add('nickname', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'r-nickname',
                    'placeholder' => 'Enter nickname..'
                ],
                'label' => false
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'required' => true,
                'attr' => [
                    'class' => 'r-email',
                    'placeholder' => 'Enter your Email..'
                ],
                'label' => false
            ])
            ->add('password',  RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 10,
                    ]),
                ],
                'first_options'  => [
                    'label' => false,
                    'attr' => [
                        'class' => 'r-f-password',
                        'placeholder' => 'Enter password..'
                    ]
                ],
                'second_options' => [
                    'label' => false,
                    'attr' => [
                        'class' => 'r-s-password',
                        'placeholder' => 'Repeat password..'
                    ]
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'r-agree-terms'
                ],
                'label' => false
            ])
            ->add('register', SubmitType::class, [
                'attr' => [
                    'class' => 'r-submit'
                ]
            ])
        ;
    }

    public function configureOptions(
        OptionsResolver $resolver
    )
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => 'token',
            'csrf_token_id' => 'user_item',
        ]);
    }


}
