sfFormExtraPlugin
=================

The `sfFormExtraPlugin` packages useful validators, widgets, and forms.

This collection holds validators, widgets, and forms which we don't want to
include with the main symfony package because they are too specific or have
external dependencies.

As no third party libraries is bundled in the plugin, you need to install and
load the required dependencies like JQuery, JQuery UI, or TinyMCE by yourself.

Installation
------------

  * Install the plugin

        $ symfony plugin:install sfFormExtraPlugin

  * Clear the cache

        $ symfony cache:clear

Documentation
-------------

All classes have full API and usage documentation. The best way to learn each widget or validator
is to read the API.

You will also find some articles on the symfony blog about this plugin:

  * [Play with the user language](http://www.symfony-project.org/blog/2008/10/16/play-with-the-user-language)
  * [Make your Choice!](http://www.symfony-project.org/blog/2008/10/14/new-in-symfony-1-2-make-your-choice)
  * [Spice up your forms with some nice widgets and validators](http://www.symfony-project.org/blog/2008/10/18/spice-up-your-forms-with-some-nice-widgets-and-validators)

Forms
-----

  * sfFormLanguage: A form to change the symfony user culture

Validators
----------

  * sfValidatorDoctrineNestedSetLevel: Checks wether or not the max level of a nested set object (nestedSet behavior) is achieved
  * sfValidatorReCaptcha: Validates a ReCaptcha (see sfWidgetFormReCaptcha)
  * sfValidatorBlacklist: Validates that a value is not one of the configured forbidden ones
  * sfValidatorSchemaTimeInterval: Validates a time interval between two dates provided by a widget schema
  * sfValidatorDefault: Returns a default value rather than throwing an error

Widgets
-------

  * sfWidgetFormReCaptcha: Displays a ReCaptcha widget (see sfValidatorReCaptcha)
  * sfWidgetFormSelectDoubleList: Displays a double list widget
  * sfWidgetFormJQueryDate: Displays a date using JQuery UI
  * sfWidgetFormJQueryAutocompleter: Displays an input tag with autocomplete support using JQuery
  * sfWidgetFormPropelChoiceGrouped: Displays a grouped set of choices tied to a Propel model
  * sfWidgetFormPropelJQueryAutocompleter: Displays an autocomplete widget tied to a Propel model
  * sfWidgetFormTextareaTinyMCE: A rich textarea rendered with TinyMCE
  * sfWidgetFormSelectUSState: A select menu of US states

As no third party libraries is bundled in the plugin, you need to install and load the required
dependencies like JQuery, JQuery UI, or TinyMCE by yourself.

How to contribute.
------------------

If you want to contribute a validator, a widget, or a form, follow these steps:

  * Check the prerequisites
    * The license must be MIT
    * You must have a unit test suite (100% coverage)
    * You must have PHPdoc for all classes and methods with a documentation usage
    * You must follow symfony coding standards
    * The contribution must not be too specific
    * You must be sure you will be able to maintain your contribution
  * Create a ticket and attach a patch
    * Choose `sfFormExtraPlugin` as the component
    * Change the qualification to `Ready for core team`
