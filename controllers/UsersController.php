<?php
/**
 * Omeka SimpleSAMLphp Plugin: Users Controller
 *
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2022 BGSU University Libraries
 * @license MIT
 */

require_once CONTROLLER_DIR . '/UsersController.php';
require_once dirname(dirname(__FILE__)) .
    DIRECTORY_SEPARATOR . 'adapters' .
    DIRECTORY_SEPARATOR . 'AuthAdapter.php';

/**
 * Omeka SimpleSAMLphp Plugin: Users Controller Class
 *
 * Extends the UsersController class to provide revised versions of various
 * actions needed for SimpleSAMLphp.
 *
 * @package SimpleSAMLphp
 */
class SimpleSamlPhp_UsersController extends UsersController
{
    /**
     * Action to create and process the add user form.
     *
     * Adds a checkbox to specify if the user is active, and prevent the
     * account activation email from being sent if that checkbox is checked.
     */
    public function addAction()
    {
        // Get a new user.
        $user = new User();

        // Create a form for that user.
        $form = $this->_getUserForm($user);

        // Remove the submit button if it is part of the form.
        if (method_exists($form, 'setSubmitButtonText')) {
            $submit = $form->getElement('submit');
            $form->removeElement('submit');
        }

        // Add a checkbox to the form to specify if the user should be active.
        $form->addElement(
            'checkbox',
            'active',
            array(
                'label' =>
                    __('Active?'),
                'description' =>
                    __('Inactive users cannot log in to the site.')
            )
        );

        $form->setHasActiveElement(true);

        // Replace the submit button and set its label if necessary.
        if (!empty($submit)) {
            $form->addElement($submit);
            $form->setSubmitButtonText(__('Add User'));
        }

        // Store the form and user to the view.
        $this->view->form = $form;
        $this->view->user = $user;

        // If the form has not been posted, do not continue.
        if (!$this->getRequest()->isPost()) {
            return;
        }

        // Display error if the form is not valid.
        if (!$form->isValid($_POST)) {
            $this->_helper->flashMessenger(
                __(
                    'There was an invalid entry on the form.'.
                    ' Please try again.'
                ),
                'error'
            );

            return;
        }

        // Set the submitted data to the user, and attempt to save the record.
        $user->setPostData($_POST);

        if ($user->save(false)) {
            // Either the user is already active, or we should attempt to send
            // the activation email. In either case, notify about the outcome.
            if ($user->active || $this->sendActivationEmail($user)) {
                $this->_helper->flashMessenger(
                    __(
                        'The user "%s" was successfully added!',
                        $user->username
                    ),
                    'success'
                );
            } else {
                $this->_helper->flashMessenger(
                    __(
                        'The user "%s" was added, but the activation email'.
                        ' could not be sent.',
                        $user->username
                    )
                );
            }

            // Redirect to the browse users action.
            $this->_helper->redirector('browse');
        } else {
            // Something went wrong saving the user, send an error message.
            $this->_helper->flashMessenger($user->getErrors());
        }
    }

    /**
     * Action to create and process the user login form.
     *
     * Check if user has been authenticated via SimpleSAMLphp. Otherwise,
     * perform the parent's login action.
     */
    public function loginAction()
    {
        // Do not attempt SSP authentication if the user posted a login form.
        if (empty($_POST['submit'])) {
            // Get the SSP adapter if available.
            $adapter = $this->_getAdapter();

            if ($adapter) {
                // Attempt to authenticate with the SSP adapter.
                $result = $this->_auth->authenticate($adapter);

                // If authenticated, redirect the user to their page.
                if ($result->isValid()) {
                    $session = new Zend_Session_Namespace;
                    $this->_helper->redirector->gotoUrl($session->redirect);
                }

                // Show reasons for a failure to authenticate to the user.
                $messages = $result->getMessages();

                if ($messages) {
                    $this->_helper->flashMessenger(
                        implode("\n", $messages),
                        'error'
                    );
                }

                // Store the link to log into SSP in the view.
                $this->view->simpleSamlPhpLoginUrl = $adapter->getLoginUrl(
                    $this->view->serverUrl().
                    $this->view->url()
                );

                // If using SSP is required, redirect to login URL.
                $required = get_option('simple_saml_php_required');

                if ($required) {
                    $this->_helper->redirector->gotoUrl(
                        $this->view->simpleSamlPhpLoginUrl
                    );
                }
            }
        }

        // Perform the normal login form action.
        parent::loginAction();
    }

    /**
     * Action for users to log out.
     *
     * If SSO has been specified, notify that system of the user's log out.
     * Also perform the parent's action.
     */
    public function logoutAction()
    {
        // Clear the user's identity.
        $this->_auth->clearIdentity();

        // Clear the user's session.
        $_SESSION = array();
        Zend_Session::destroy();

        // Check if SSP is authenticated and logout and redirect to main page.
        $adapter = $this->_getAdapter();

        if ($adapter) {
            $logout_url = get_option('simple_saml_php_logout_url');

            if (empty($logout_url)) {
                $logout_url = $this->view->serverUrl(). $this->view->url('/');
            }

            $adapter->logout($logout_url);
        }

        // Otherwise,redirect to main page.
        $this->_helper->redirector->gotoUrl('');
    }

    /**
     * Gets auth adapter if configured correctly.
     *
     * @return SimpleSamlPhp_AuthAdapater|null
     */
    protected function _getAdapter()
    {
        $path = get_option('simple_saml_php_path');
        $authSource = get_option('simple_saml_php_auth_source');

        if ($path && $authSource) {
            $adapter = new SimpleSamlPhp_AuthAdapter($path, $authSource);
            $adapter->attribute = get_option('simple_saml_php_attribute');
            $adapter->format = get_option('simple_saml_php_format');
            $adapter->email = get_option('simple_saml_php_email');

            return $adapter;
        }

        return null;
    }
}
