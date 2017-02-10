Feature: Install and activate plugin
  In order to use the plugin
  As an administrator
  I need to be able to install and activate the plugin
  
  Scenario: See plugin in plugin overview
    Given a fresh WordPress is installed
    And the plugin "public-post-preview-configurator" is installed (from source)
    And I am logged as an administrator
    When I go to "/wp-admin/plugins.php"
    Then I should see "Public Post Preview Configurator"

  Scenario: Activate plugin
    Given a fresh WordPress is installed
    And the plugin "public-post-preview-configurator" is installed (from source)
    And I am logged as an administrator
    When I go to "/wp-admin/plugins.php"
    And I activate the plugin "Public Post Preview Configurator"
    Then I should see the message "Plugin activated"
    And the plugin "public-post-preview-configurator" is activated
