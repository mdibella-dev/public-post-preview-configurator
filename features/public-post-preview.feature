Feature: Use plugin
  In order to give others access to unpublished articles
  As an administrator
  I need to be able to create urls with a given validity
  
  Scenario: Default behaviour without plugin
    Given a fresh WordPress is installed
    And the plugin "public-post-preview" is installed
    And the plugin "public-post-preview" is activated
    And I am logged as an administrator
    When I go to "/wp-admin/post-new.php"
    And I fill in "title" with "This is an unpublished article"
    And I press "save-post"
    And I check "public-post-preview"
    Then the public post preview should have a validity of 48 hours

  Scenario: With configured plugin
    Given a fresh WordPress is installed
    And the plugin "public-post-preview" is installed
    And the plugin "public-post-preview" is activated
    And the plugin "public-post-preview-configurator" is installed (from source)
    And the plugin "public-post-preview-configurator" is activated
    And the option "ppp_configurator_expiration_hours" has the value "100000"
    And I am logged as an administrator
    When I go to "/wp-admin/post-new.php"
    And I fill in "title" with "This is an unpublished article"
    And I press "save-post"
    And I check "public-post-preview"
    Then the public post preview should have a validity of 100000 hours
