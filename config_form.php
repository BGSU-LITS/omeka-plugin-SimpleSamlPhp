<?php
/**
 * Omeka SimpleSAMLphp Plugin: Configuration Form
 *
 * Outputs the configuration form for the config_form hook.
 *
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2022 BGSU University Libraries
 * @license MIT
 * @package SimpleSAMLphp
 */

$sections = array(
    'SimpleSAMLphp' => array(
        array(
            'name' => 'simple_saml_php_path',
            'label' => __('Path'),
            'explanation' => __(
                'Filesystem path to the SimpleSAMLphp installation.'
            )
        ),
        array(
            'name' => 'simple_saml_php_auth_source',
            'label' => __('Auth Source'),
            'explanation' => __(
                'The ID of the SimpleSAMLphp authentication source.'
            )
        ),
        array(
            'name' => 'simple_saml_php_required',
            'label' => __('Required'),
            'checkbox' => true,
            'explanation' => __(
                'If checked, users must authenticate via SimpleSAMLphp.'.
                ' Otherwise, users will also be able to authenticate via the' .
                ' Omeka username and password authentication system.'
            )
        )
    ),
    'User Matching' => array(
        array(
            'name' => 'simple_saml_php_attribute',
            'label' => __('Attribute'),
            'explanation' => __(
                'Attribute provided by SimpleSAMLphp to match with Omeka'.
                ' user data (e.g. uid).'
            )
        ),
        array(
            'name' => 'simple_saml_php_format',
            'label' => __('Attribute Format'),
            'explanation' => __(
                'Format the attribute via sprintf() before matching. Use %s'.
                ' as a placeholder for the attribute value.'
            )
        ),
        array(
            'name' => 'simple_saml_php_email',
            'label' => __('Match Email Address'),
            'checkbox' => true,
            'explanation' => __(
                'If checked, match the Omeka user\'s email address to the'.
                ' formatted attribute. Otherwise, match the username.'
            )
        )
    ),
    'Login Form' => array(
        array(
            'name' => 'simple_saml_php_plugin_title',
            'label' => __('Plugin Title'),
            'explanation' => __(
                'Title to display above the button to log in via the'.
                ' SimpleSAMLphp plugin. Will not be displayed if left blank.'
            )
        ),
        array(
            'name' => 'simple_saml_php_plugin_button',
            'label' => __('Plugin Button'),
            'explanation' => __(
                'Label for the button to log in via the SimpleSAMLphp'.
                ' plugin. Will use "SimpleSAMLphp Log In" if left blank.'
            )
        ),
        array(
            'name' => 'simple_saml_php_default_title',
            'label' => __('Default Title'),
            'explanation' => __(
                'Title to display above the default Omeka login form. Will'.
                ' not be displayed if left blank.'
            )
        )
    ),
    'Logout' => array(
        array(
            'name' => 'simple_saml_php_logout_url',
            'label' => __('Logout URL'),
            'explanation' => __(
                'URL to redirect to after logout. Will redirect to login page'.
                ' if left blank.'
            )
        )
    )
);
?>

<?php foreach ($sections as $section => $fields): ?>
    <h2><?php echo $section; ?></h2>

    <?php foreach ($fields as $field): ?>
        <div class="field">
            <div class="two columns alpha">
                <label for="<?php echo $field['name']; ?>">
                    <?php echo $field['label']; ?>
                </label>
            </div>
            <div class="inputs five columns omega">
                <?php if (isset($field['select'])): ?>
                    <select name="<?php echo $field['name']; ?>" id="<?php echo $field['name']; ?>">
                        <?php foreach ($field['select'] as $value => $option): ?>
                            <option value="<?php echo $value; ?>"<?php if (get_option($field['name']) == $value) echo ' selected'; ?>>
                                <?php echo $option; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif (isset($field['checkbox'])): ?>
                    <input type="hidden" name="<?php echo $field['name']; ?>" value="">
                    <input type="checkbox" name="<?php echo $field['name']; ?>" id="<?php echo $field['name']; ?>" value="<?php echo $field['checkbox']; ?>"<?php if (get_option($field['name']) == $field['checkbox']) echo ' checked'; ?>>
                <?php else: ?>
                    <input type="<?php print(empty($field['password']) ? 'text' : 'password'); ?>" name="<?php echo $field['name']; ?>" id="<?php echo $field['name']; ?>" value="<?php echo get_option($field['name']); ?>">
                <?php endif; ?>

                <?php if (isset($field['explanation'])): ?>
                    <p class="explanation">
                        <?php echo $field['explanation']; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
