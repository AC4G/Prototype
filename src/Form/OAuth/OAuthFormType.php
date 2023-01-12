<?php declare(strict_types=1);

namespace App\Form\OAuth;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

final class OAuthFormType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    )
    {
        $builder
            ->add('authorize', SubmitType::class, [
                'attr' => [
                    'class' => 'authorize-submit'
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
