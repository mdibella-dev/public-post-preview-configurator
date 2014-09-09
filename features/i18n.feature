Feature: See plugin in blog language
  In order to work with the plugin properly
  As an administrator
  I need to be able to read all text of the plugin in the blog language

  Scenario: See german translations on plugins page
    Given a fresh WordPress is installed
    And the option "WPLANG" has the value "de_DE"
    And the plugin "public-post-preview-configurator" is installed (from source)
    And the plugin "public-post-preview-configurator" is activated
    And I am logged as an administrator
    When I go to "/wp-admin/plugins.php"
    Then I should see "Konfigurator für Öffentliche Vorschau"
    And I should see "Ermöglicht die Konfiguration des Plugins 'Öffentliche Vorschau' per Benutzeroberfläche."

  Scenario: See german translations on settings page
    Given a fresh WordPress is installed
    And the option "WPLANG" has the value "de_DE"
    And the plugin "public-post-preview-configurator" is installed (from source)
    And the plugin "public-post-preview-configurator" is activated
    And I am logged as an administrator
    When I go to "/wp-admin/options-general.php?page=public-post-preview-configurator"
    Then I should see "Konfigurator für Öffentliche Vorschau"
    And I should see "Gültigkeit in Stunden"
    And I should see "Gültigkeit des Vorschau-Links in Stunden (Default = 48)"
    When I fill in "ppp_configurator_expiration_hours" with "abc"
    And I press "submit"
    Then I should see "Ungültiger Wert für 'Gültigkeit in Stunden'. Positive Ganzzahl erwartet."
