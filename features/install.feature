Feature: Install and activate plugin
  In order to use the plugin
  As an administrator
  I need to be able to install and activate the plugin
  
  Scenario: See plugin in plugin overview
    Given a fresh WordPress installation
    And the plugin "public-post-preview-configurator" in the plugin directory
    And I am logged as an administrator
    When I go to "/wp-admin/plugins.php"
    Then I should see "Public Post Preview Configurator"

  Scenario: See german translations
    Given a fresh WordPress installation (de_DE)
    And the plugin "public-post-preview-configurator" in the plugin directory
    And I am logged as an administrator
    When I go to "/wp-admin/plugins.php"
    Then I should see "Public Post Preview Configurator"
