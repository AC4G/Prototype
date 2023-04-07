<?php declare(strict_types=1);

namespace App\Form\ResetPassword;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

final class CodeFormType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    )
    {
        $builder
            ->add('code', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'required' => false,
                'attr' => [
                    'placeholder' => 'XXXXXX',
                    'autocomplete' => false,
                    'inputmode' => 'numeric',
                    'pattern' => '[0-9]*'
                ],
                'label' => false
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(
        OptionsResolver $resolver
    )
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => 'token',
            'csrf_token_id' => 'reset_password_code',
        ]);
    }
}
