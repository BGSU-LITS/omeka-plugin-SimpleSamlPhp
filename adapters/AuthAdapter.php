<?php
/**
 * Omeka SimpleSAMLphp Plugin: Auth Adapter
 *
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2022 BGSU University Libraries
 * @license MIT
 */

/**
 * Omeka SimpleSAMLphp Plugin: Auth Adapter Class
 *
 * @package SimpleSAMLphp
 */
class SimpleSamlPhp_AuthAdapter implements Zend_Auth_Adapter_Interface
{
    /**
     * @var \SimpleSAML\Auth\Simple SSP authenticator.
     */
    protected $_auth;

    /**
     * Class constructor.
     *
     * @param string $path Path to SSP installation.
     * @param string $authSource SSP authentication source to use.
     */
    public function __construct($path, $authSource)
    {
        require_once(
            rtrim($path, DIRECTORY_SEPARATOR) .
            DIRECTORY_SEPARATOR . 'lib' .
            DIRECTORY_SEPARATOR . '_autoload.php'
        );

        $this->_auth = new \SimpleSAML\Auth\Simple($authSource);
    }

    /**
     * Performs an authentication attempt.
     *
     * @return Zend_Auth_Result The result of the authentication.
     */
    public function authenticate()
    {
        $attribute = 'uid';
        $format = '%s@bgsu.edu';
        $email = true;

        $attributes = $this->_auth->getAttributes();

        if (!empty($attributes[$attribute])) {
            foreach ($attributes[$attribute] as $value) {
                if ($format) {
                    $value = sprintf($format, $value);
                }

                if ($email) {
                    $user = get_db()->getTable('User')->findByEmail($value);
                } else {
                    $user = get_db()->getTable('User')->findBySql(
                        'username = ?',
                        array($value),
                        true
                    );
                }

                if ($user && $user->active) {
                    return new Zend_Auth_Result(
                        Zend_Auth_Result::SUCCESS,
                        $user->id
                    );
                }
            }

            return new Zend_Auth_Result(
                Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
                $value,
                array(__(
                    '%s matching "%s" not found.',
                    $email ? 'Email' : 'Username',
                    $value
                ))
            );
        }

        return new Zend_Auth_Result(
            Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS,
            null
        );
    }

    /**
     * Get URL to log into SSP.
     *
     * @param string $returnTo URL the user should be returned to after auth.
     * @return string URL to log into SSP.
     */
    public function getLoginUrl($returnTo)
    {
        return $this->_auth->getLoginURL($returnTo);
    }

    /**
     * Performs a user logout.
     *
     * @param array $params The URL to redirect to after logout.
     */
    public function logout($params)
    {
        // Check if SSP is authenticated, and if so logout.
        if ($this->_auth->isAuthenticated()) {
            $this->_auth->logout($params);
        }
   }
}
