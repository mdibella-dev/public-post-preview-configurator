Feature: Uninstall plugin
  In order to clean up
  As an administrator
  I need to be able to uninstall the plugin without a footprint

  Scenario: Uninstall plugin
    Given a fresh WordPress is installed
    And the plugin "public-post-preview-configurator" is installed (from source)
    And the plugin "public-post-preview-configurator" is activated
    And the option "ppp_configurator_expiration_hours" has the value "100000"
    And I am logged as an administrator
    When I go to "/wp-admin/plugins.php"
    And I deactivate the plugin "public-post-preview-configurator"
    And I uninstall the plugin "public-post-preview-configurator"
    Then I should see the message "The selected plugins have been deleted"
    And the option "ppp_configurator_expiration_hours" should not exist
