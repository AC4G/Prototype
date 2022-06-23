<?php declare(strict_types=1);

namespace App\Form\OAuth;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginFormType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    )
    {
        $builder
            ->add('nickname', TextType::class, [
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('approval', CheckboxType::class, [
                'required' => true,
                'mapped' => false
            ])
            ->add('Submit', SubmitType::class)
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
            ]
        );
    }


}
