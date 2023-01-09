<?php declare(strict_types=1);

namespace App\Form\ResetPassword;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmailFormType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    )
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'required' => true,
                'attr' => [
                    'class' => 'r-pwd-email',
                    'placeholder' => 'Enter your Email...'
                ],
                'label' => false
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'r-pwd-submit'
                ]
            ])
        ;
    }

    public function configureOptions(
        OptionsResolver $resolver
    )
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => 'token',
            'csrf_token_id' => 'user_item',
        ]);
    }
}