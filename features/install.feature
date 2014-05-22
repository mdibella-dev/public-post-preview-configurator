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

  Scenario: Activate plugin
    Given a fresh WordPress installation
    And the plugin "public-post-preview-configurator" in the plugin directory
    And I am logged as an administrator
    When I go to "/wp-admin/plugins.php"
    And I activate the plugin "public-post-preview-configurator"
    Then I should see the message "Plugin activated"

  Scenario: Set expiration hours
    Given a fresh WordPress installation
    And the plugin "public-post-preview-configurator" in the plugin directory
    And I am logged as an administrator
    When I go to "/wp-admin/plugins.php"
    And I activate the plugin "public-post-preview-configurator"
    And I go to "/wp-admin/options-general.php?page=public-post-preview-configurator.php"
    And I fill in "ppp_configurator_expiration_hours" with "100000"
    And I press "submit"
    Then I should see the message "Settings saved"
    And the option "ppp_configurator_expiration_hours" should have the value "100000"

  Scenario: See german translations
    Given a fresh WordPress installation (de_DE)
    And the plugin "public-post-preview-configurator" in the plugin directory
    And I am logged as an administrator
    When I go to "/wp-admin/plugins.php"
    Then I should see "Public Post Preview Configurator"
