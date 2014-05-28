Feature: See plugin in blog language
  In order to work with the plugin properly
  As an administrator
  I need to be able to read all text of the plugin in the blog language

  Scenario: See german translations on plugins page
    Given the blog language is "de_DE"
    And a fresh WordPress is installed
    And the plugin "public-post-preview-configurator" is installed (from source)
    And the plugin "public-post-preview-configurator" is activated
    And I am logged as an administrator
    When I go to "/wp-admin/plugins.php"
    Then I should see "Konfigurator für Öffentliche Vorschau"
    And I should see "Ermöglicht die Konfiguration des Plugins 'Öffentliche Vorschau' per Benutzeroberfläche."
    When I go to "/wp-admin/options-general.php?page=public-post-preview-configurator"
    Then I should see "Konfigurator für Öffentliche Vorschau"
    And I should see "Gültigkeit in Stunden"
    And I should see "Gültigkeit des Vorschau-Links in Stunden (Default = 48)"
