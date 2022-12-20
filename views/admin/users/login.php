<?php
/**
 * Omeka SimpleSAMLphp Plugin: Admin Login Form
 *
 * Overrides default login form to include login link to SSP.
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
<h1>Omeka</h1>

<h2><?php echo link_to_home_page(option('site_title'), array("title" => __('Go to the public site'))); ?></h2>

<?php echo flash(); ?>

<?php if (isset($simpleSamlPhpLoginUrl)): ?>
<?php if ($title = get_option('simple_saml_php_plugin_title')): ?>
<h3><?php echo html_escape(__($title)); ?></h3>
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
<h3><?php echo html_escape(__($title)); ?></h3>
<?php endif; ?>
<?php endif; ?>

<div class="eight columns alpha offset-by-one">
<?php echo $this->form->setAction($this->url('users/login')); ?>
</div>

<p id="forgotpassword">
<?php echo link_to('users', 'forgot-password', __('(Lost your password?)')); ?>
</p>

<?php echo foot(array(), $footer); ?>
