Feature: Configure the plugin
  In order to use the plugin properly
  As an administrator
  I need to configure it

  Scenario: Set expiration hours
    Given a fresh WordPress is installed
    And the plugin "public-post-preview-configurator" is installed (from source)
    And the plugin "public-post-preview-configurator" is activated
    And I am logged as an administrator
    And I go to "/wp-admin/options-general.php?page=public-post-preview-configurator.php"
    And I fill in "ppp_configurator_expiration_hours" with "100000"
    And I press "submit"
    Then I should see the message "Settings saved"
    And the option "ppp_configurator_expiration_hours" should have the value "100000"

  Scenario: Preset expiration hours
    Given a fresh WordPress is installed
    And the plugin "public-post-preview-configurator" is installed (from source)
    And the plugin "public-post-preview-configurator" is activated
    And the option "ppp_configurator_expiration_hours" has the value "100000"
    And I am logged as an administrator
    And I go to "/wp-admin/options-general.php?page=public-post-preview-configurator.php"
    Then the "ppp_configurator_expiration_hours" field should contain "100000"
