<?php

/**
 * ACL strom
 *
 * ACL role i resource jsou nastaveny z application.ini s prefixem acl
 */
class My_Acl extends Zend_Acl
{

    /**
     *
     * @param Zend_Config $config
     */
    public function __construct(Zend_Config $config)
    {
        $roles = $config->acl->roles;
        $resources = $config->acl->resources;

        $this->_addRoles($roles);
        $this->_addResources($resources);
    }

    /**
     * Nacte uzivatelske role
     *
     * @param Zend_Config $roles
     */
    private function _addRoles($roles)
    {
        if (empty($roles))
        {
            throw new Exception('Acl roles must be specified in application.ini');
        }

        foreach ($roles as $name => $parents)
        {
            if (!$this->hasRole($name))
            {
                if (empty($parents))
                {
                    $parents = null;
                }
                else
                {
                    $parents = explode(',', $parents);
                }

                $this->addRole(new Zend_Acl_Role($name), $parents);
            }
        }
    }

    /**
     * Nacte resource
     *
     * @param Zend_Config $resources
     */
    private function _addResources($resources)
    {
        if (empty($resources))
        {
            throw new Exception('Acl resources must be specified in application.ini');
        }

        foreach ($resources as $permissions => $controllers)
        {
            foreach ($controllers as $controller => $actions)
            {

                if ($controller == 'all')
                {
                    $controller = null;
                }
                else
                {
                    if (!$this->has($controller))
                    {
                        $this->add(new Zend_Acl_Resource($controller));
                    }
                }

                if (!($actions instanceof Zend_Config))
                {
                    $actions = array('all' => $actions);
                }

                foreach ($actions as $action => $role)
                {
                    if ($action == 'all')
                    {
                        $action = null;
                    }
                    if ($permissions == 'allow')
                    {
                        $this->allow($role, $controller, $action);
                    }
                    if ($permissions == 'deny')
                    {
                        $this->deny($role, $controller, $action);
                    }
                }
            }
        }
    }

}
