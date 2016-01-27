<?php
namespace Mongobox\Bundle\UsersBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class UsersAdmin extends Admin
{
    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by'    => 'id'
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $user = $this->getSubject();

        // use $thumbnailFieldOptions so we can add other options to the field
        $avatarFieldOptions = array('required' => false, 'data_class' => null);
        if ($user) {
            $avatar = $user->getAvatar();
            if (empty($avatar)) {
                $avatar = $user->getGravatar();
            } else {
                $avatar = '/' . $user->getAvatarWebPath();
            }
            // add a 'help' option containing the preview's img tag
            $avatarFieldOptions['help'] = '<img src="' . $avatar . '" class="admin-preview" />';
        }

        $formMapper
            ->with('General')
            ->add('login')
            ->add('email')
            //->add('plainPassword', 'text', array('required' => false))
            ->end()
            ->with('Profile')
            ->add('avatar', 'file', $avatarFieldOptions)
            ->add(
                'firstname',
                'text',
                array(
                    'required' => false,
                )
            )
            ->add(
                'lastname',
                'text',
                array(
                    'required' => false,
                )
            )
            ->add('nsfw_mode', null, array('required' => false))
            ->end()
            //if ($this->getSubject() && !$this->getSubject()->hasRole('ROLE_SUPER_ADMIN')) {
            //$formMapper
            ->with('Management')
            ->add(
                'groups',
                'sonata_type_model',
                array(
                    'required'     => false,
                    'expanded'     => true,
                    'multiple'     => true,
                    'by_reference' => false
                )
            )
            /*->add('realRoles', 'sonata_security_roles', array(
                'expanded' => true,
                'multiple' => true,
                'required' => false
            ))*/
            //  ->add('locked', null, array('required' => false))
            //  ->add('expired', null, array('required' => false))
            ->add('actif', null, array('required' => false))
            //  ->add('credentialsExpired', null, array('required' => false))
            ->end()
            // ;
            //}
            //->add('adresse')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('email')
            ->add('login')
            ->add('actif');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('avatar', 'string', array('template' => 'MongoboxUsersBundle::Admin/list_avatar.html.twig'))
            ->addIdentifier('email')
            ->add('login')
            ->add('name', 'string', array('template' => 'MongoboxUsersBundle::Admin/User/Fields/name.html.twig'))
            ->add('groups')
            ->add('actif');
    }

    /**
     * @param string $name
     *
     * @return null|string
     */
    public function getTemplate($name)
    {
        if (isset($this->templates[$name])) {
            return $this->templates[$name];
        }

        return null;
    }
}
