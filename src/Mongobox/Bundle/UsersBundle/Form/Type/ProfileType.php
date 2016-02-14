<?php
namespace Mongobox\Bundle\UsersBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname')
            ->add('lastname')
            ->add(
                'nsfw_mode',
                'checkbox',
                array(
                    'label'    => 'Mode NSFW',
                    'required' => false,
                )
            )
            ->add(
                'avatar',
                'file',
                array(
                    'label'      => 'Avatar',
                    'data_class' => null,
                    'required'   => false
                )
            );
    }

    public function getParent()
    {
        //return 'FOS\UserBundle\Form\Type\RegistrationFormType';

        // Or for Symfony < 2.8
        return 'fos_user_profile';
    }

    public function getBlockPrefix()
    {
        return 'mongobox_user_profile';
    }

    // For Symfony 2.x
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
