<?php
/**
 * Omeka SimpleSAMLphp Plugin: Public Login Form
 *
 * Overrides default login form to include login button to SSP.
 *
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2022 BGSU University Libraries
 * @license MIT
 * @package SimpleSAMLphp
 */

queue_js_file('login');
$pageTitle = __('Log In');
echo head(array('bodyclass' => 'login', 'title' => $pageTitle), $header);
?>
<h1><?php echo $pageTitle; ?></h1>

<p id="login-links">
<span id="backtosite"><?php echo link_to_home_page(__('Go to Home Page')); ?></span>  |  <span id="forgotpassword"><?php echo link_to('users', 'forgot-password', __('Lost your password?')); ?></span>
</p>

<?php echo flash(); ?>

<?php if (isset($simpleSamlPhpLoginUrl)): ?>
<?php if ($title = get_option('simple_saml_php_plugin_title')): ?>
<h2><?php echo html_escape(__($title)); ?></h2>
<?php endif; ?>
<div>
<a href="<?php echo html_escape($simpleSamlPhpLoginUrl); ?>" class="button">
<?php if ($button = get_option('simple_saml_php_plugin_button')): ?>
<?php echo html_escape(__($button)); ?>
<?php else: ?>
<?php echo __('SimpleSAMLphp Log In'); ?>
<?php endif; ?>
</a>
</div>
<?php if ($title = get_option('simple_saml_php_default_title')): ?>
<h2><?php echo html_escape(__($title)); ?></h2>
<?php endif; ?>
<?php endif; ?>

<?php echo $this->form->setAction($this->url('users/login')); ?>

<?php echo foot(array(), $footer); ?>
