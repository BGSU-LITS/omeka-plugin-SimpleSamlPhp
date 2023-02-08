<?php
/**
 * Omeka SimpleSAMLphp Plugin
 *
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2022 BGSU University Libraries
 * @license MIT
 */

/**
 * Omeka SimpleSAMLphp Plugin: Plugin Class
 *
 * @package SimpleSAMLphp
 */
class SimpleSamlPhpPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Plugin hooks.
     */
    protected $_hooks = array(
        'install',
        'uninstall',
        'config',
        'config_form',
        'define_routes'
    );

    /**
     * @var array Plugin filters.
     */
    protected $_filters = array(
        'admin_whitelist'
    );

    /**
     * @var array Plugin options.
     */
    protected $_options = array(
        'simple_saml_php_path' => '',
        'simple_saml_php_auth_source' => '',
        'simple_saml_php_required' => false,
        'simple_saml_php_attribute' => 'uid',
        'simple_saml_php_format' => '%s',
        'simple_saml_php_email' => false,
        'simple_saml_php_plugin_title' => '',
        'simple_saml_php_plugin_button' => '',
        'simple_saml_php_default_title' => '',
        'simple_saml_php_logout_url' => '',
    );

    /**
     * Hook to plugin installation.
     *
     * Installs the options for the plugin.
     */
    public function hookInstall()
    {
        $this->_installOptions();
    }

    /**
     * Hook to plugin uninstallation.
     *
     * Uninstalls the options for the plugin.
     */
    public function hookUninstall()
    {
        $this->_uninstallOptions();
    }

    /**
     * Hook to plugin configuration form submission.
     *
     * Sets options submitted by the configuration form.
     */
    public function hookConfig($args)
    {
        $path = '';

        if (isset($args['post']['simple_saml_php_path'])) {
            $path = $args['post']['simple_saml_php_path'];
        }

        $authSource = '';

        if (isset($args['post']['simple_saml_php_auth_source'])) {
            $authSource = $args['post']['simple_saml_php_auth_source'];
        }

        if ($path !== '') {
            $file = rtrim($path, DIRECTORY_SEPARATOR) .
                DIRECTORY_SEPARATOR . 'lib' .
                DIRECTORY_SEPARATOR . '_autoload.php';

            if (file_exists($file)) {
                require_once $file;
            }

            if (
                !class_exists('\SimpleSAML\SessionHandler') ||
                !class_exists('\SimpleSAML\Auth\Simple')
            ) {
                throw new Omeka_Validate_Exception(
                    'A path to a valid SimpleSAMLphp installation must be'.
                    ' specified to use this plugin.'
                );
            }

            $sh = \SimpleSAML\SessionHandler::getSessionHandler();

            if ($sh instanceof \SimpleSAML\SessionHandlerPHP) {
                throw new Omeka_Validate_Exception(
                    'SimpleSAMLphp cannot use the phpsession datastore when' .
                    ' using this plugin.'
                );
            }

            if ($authSource !== '') {
                try {
                    $auth = new \SimpleSAML\Auth\Simple($authSource);
                    $auth->getAuthSource();
                } catch (\SimpleSAML\Error\AuthSource $exception) {
                    throw new Omeka_Validate_Exception(
                        'A valid SimpleSAMLphp authentication source'.
                        ' ID must be specified to use this plugin.'
                    );
                }
            }
        }

        set_option('simple_saml_php_path', $path);
        set_option('simple_saml_php_auth_source', $authSource);

        foreach (array_keys($this->_options) as $option) {
            if (in_array($option, array(
                'simple_saml_php_path',
                'simple_saml_php_auth_source'
            ))) {
                continue;
            }

            if (isset($args['post'][$option])) {
                set_option($option, $args['post'][$option]);
            } else {
                delete_option($option);
            }
        }
    }

    /**
     * Hook to output plugin configuration form.
     *
     * Include form from config_form.php file.
     */
    public function hookConfigForm()
    {
        include 'config_form.php';
    }

    /**
     * Hook to define routes.
     *
     * Overrides the add, login and logout actions of the UsersController to
     * our customized SimpleSamlPhp_UsersController versions.
     */
    public function hookDefineRoutes($args)
    {
        $router = $args['router'];

        $router->addRoute(
            'simple_saml_php_add',
            new Zend_Controller_Router_Route(
                'users/add',
                array(
                    'module' => 'simple-saml-php',
                    'controller' => 'users',
                    'action' => 'add'
                )
            )
        );

        $router->addRoute(
            'simple_saml_php_login',
            new Zend_Controller_Router_Route(
                'users/login',
                array(
                    'module' => 'simple-saml-php',
                    'controller' => 'users',
                    'action' => 'login'
                )
            )
        );

        $router->addRoute(
            'simple_saml_php_logout',
            new Zend_Controller_Router_Route(
                'users/logout',
                array(
                    'module' => 'simple-saml-php',
                    'controller' => 'users',
                    'action' => 'logout'
                )
            )
        );
    }

    /**
     * Filter the admin interface whitelist.
     *
     * Allows our custom login action to be accessed without logging in.
     */
    public function filterAdminWhitelist($whitelist)
    {
        $whitelist[] = array(
            'module' => 'simple-saml-php',
            'controller' => 'users',
            'action' => 'login'
        );

        return $whitelist;
    }
}
