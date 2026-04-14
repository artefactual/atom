# Contributing

Thank you for your interest in contributing to the Access to Memory (AtoM)
project! Community involvement and development help make the AtoM project
vibrant and responsive to users' needs. Here are the guidelines on reporting
issues and making pull requests to the AtoM project.


**Contents**

* [Reporting a security vulnerabilitiy](#security-vulnerability)
* [Reporting general bugs](#reporting-general-bugs)
* [Getting started](#getting-started)
* [Contributing code](#contributing-code)
* [Coding standards](#coding-standards)
* [Creating tests](#testing)
* [Contributing documentation and translations](#contributing-documentation-and-translations)

## Security vulnerability

**If you are reporting a security vulnerability**, refer yourself to the
[SECURITY.md](./SECURITY.md) document.

## Reporting general bugs

If you have discovered an issue in AtoM, we welcome your reports.

Start by doing a search on [AtoM user forum](https://groups.google.com/forum/#!forum/ica-atom-users)
and in the [AtoM GitHub issues](https://github.com/artefactual/atom/issues)
to see if your question has been considered in the past.

If you are sure that the issue you have is a bug, please open an issue
on GitHub. If not, feel welcome to open a thread in the AtoM user forum.

The [Troubleshooting guide](https://www.accesstomemory.org/docs/latest/admin-manual/maintenance/troubleshooting/)
contains useful tips on how to find information needed to open an issue,
such as the [version of AtoM](https://www.accesstomemory.org/docs/latest/user-manual/administer/settings/#application-version),
accessing [errors logs and debug mode](https://www.accesstomemory.org/docs/latest/admin-manual/maintenance/troubleshooting/#troubleshooting-logs-debug),
and other information needed to [get support](https://www.accesstomemory.org/docs/latest/admin-manual/maintenance/troubleshooting/#getting-support).

## Getting Started

You will find an overview of the elements that make up AtoM, and many useful
AtoM slides, in the following resources:

* [Overview AtoM documentation](https://www.accesstomemory.org/docs/latest/user-manual/overview/intro/)
* [AtoM presentations](https://www.slideshare.net/accesstomemory/presentations)

AtoM was originally developed using the Symfony 1.x framework, and the Propel
1.x ORM. You might want to familiarize yourself with Symfony before beginning:

* [Symfony 1.x documentation](http://symfony.com/legacy/doc)

AtoM also currently uses Elasticsearch for its search index, Twitter Bootstrap
for theming, and several other libraries. MySQL is used for data storage. We
keep all our code in a git repository, so being comfortable with git or other
distributed version control systems will also help you.
There are useful resources within these guidelines and in the
[Contributors Portal](https://contributors.artefactual.com/index.html).

### Setting up your development environment

* [Docker installation](https://www.accesstomemory.org/en/docs/latest/dev-manual/env/compose/#dev-env-compose)
* [Vagrant installation](https://www.accesstomemory.org/docs/latest/dev-manual/env/vagrant/)

#### Useful commands

*Click on the arrow to expand the section.*

For pre-commit commands, see the section on running [pre-commit checks](#submitting-a-pull-request).
Also check out the [Developer Guidance](https://contributors.artefactual.com/guidelines.html)
on the Contributors Portal for other userful commands and tips.

<details>
<summary><b>Loading AtoM data in Docker</b></summary> 
You will need to customize the path to your mysqldump file in the first line.
Also, change the charset and collation if you’re working with stable/2.5.x or
lower (CHARACTER SET utf8 COLLATE utf8_unicode_ci).

```bash
docker compose cp [mysql source path] percona:[target save path in container] && \
docker compose exec percona mysql -h localhost -u atom -patom_12345 -e "DROP DATABASE IF EXISTS atom;" && \
docker compose exec percona mysql -h localhost -u atom -patom_12345 -e "CREATE DATABASE atom CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;" && \
docker compose exec percona bash -c "mysql -h localhost -u atom -patom_12345 atom < /atom.sql" && \
docker compose exec atom php -d memory_limit=-1 symfony tools:upgrade-sql --no-confirmation && \
docker compose exec atom php -d memory_limit=-1 symfony search:populate
```

If you plan to use this command often, it is recommended that you turn it into a script for ease of use.

</details>

<details>
<summary><b>Clearing AtoM caches</b></summary>

```bash
docker compose exec atom php symfony cc && \
docker compose restart atom && \
docker compose restart memcached && \
docker compose restart atom_worker
```

</details>

<details>
<summary><b>Run symfony without a memory limit</b></summary>

This script is useful for deleting a database and setting up a demo username/password.

```bash
docker compose exec atom php -d memory_limit=-1 symfony tools:purge --demo
```

You can also use the `-d memory-limit=-1` option with any command. This option
sets the PHP memory limit to unlimited.

</details>

#### Debugging and troubleshooting
These commands and scripts can help with some common issues during development.

<details>
<summary><b>If the Elasticsearch container keeps shutting down</b></summary>

If your Elasticsearch container constantly shuts down within a few seconds of
starting it, you may need to `increase vm.max_map_count` to 262144 or higher in
your host system. More information is available
[here](https://github.com/artefactual-labs/am/tree/master/compose#elasticsearch-container).

Run

```bash
sudo sysctl -w vm.max_map_count=262144
```

before starting the ES docker container.

</details>

<details>
<summary><b>Enable symfony debugging panel</b></summary>

From version 2.10.1, you can run the symfony debugging panel. In atom’s root folder, open `index.php` and change (Line 5):

```php
$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'prod', false);
```

to

```php
$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'dev', false);
```

This will enable the symfony debugging panel in AtoM. Remember to change it
back before you commit any code!

Also note that changing the last parameter to 'true' enables php warnings:

```php
$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'dev', true);
```

</details>
Note: this debugging panel is broken between AtoM v2.8 and v2.10.1

<details>
<summary><b>AtoM PHP logger</b></summary>
Use this to print the log messages directly into
Docker. It's useful if you don’t already have
logging in place.

```php
function log_docker($message, $value = "")
{
    if (is_string($value)) {
        $val = $value."\\n";
    } else {
        $val = json_encode($value)."\\n";
    }

    if ($value != "" && $message != "") {
        $message = $message.": ";
    }

    file_put_contents('php://stdout', ($message));
    file_put_contents('php://stdout', ($val));
} 
```

For logging into the qubit_prod file directly:

```php
function prodlog($message = "", $value = "")
{
    $logFile = "./log/qubit_prod.log";

    if ($value != null) {
        $logMessage = $message.": ";
        if (is_string($value)) {
            $logMessage .= $value;
        } elseif (is_bool($value)) {
            $logMessage .= $value ? 'true' : 'false';
        } else {
            $logMessage .= json_encode($value);
        }
    } else {
        $logMessage =json_encode($message);
    }

    error_log("PROD LOG -- " . $logMessage . "\n", 3, $logFile);
}
```

</details>

### Theme development

[Custom theming documentation](https://www.accesstomemory.org/docs/latest/admin-manual/customization/theming/#create-a-custom-theme)

Below are a number of useful code snippets that will help you add
common customizations to a theme.

*Click on the arrow to expand the section.*

<details>
<summary><b>Splash on homepage</b></summary>

```html
plugins/arXyzB5Plugin/modules/staticpage/templates/homeSuccess.php:
<?php decorate_with('layout_homepage');

plugins/arXyzB5Plugin/templates/layout_homepage.php:
<?php echo get_partial('layout_start'); ?>
```

</details>

<details>
<summary><b>Homepage carousel</b></summary>

These code snippets will help set up a homepage carousel.
You need to include class pages. Carousels can be updated.

```code
plugins/arXyzB5Plugin/
├── carousel
│   ├── config.yml                                          # sets captions for each image
│   ├── image01.jpg
│   ├── image02.jpg
│   └── image03.jpg
├── js
│   ├── carousel.js                                         # use as template
│   └── main.js                                             # import
└── modules
    └── staticpage
        ├── actions
        │   └── homeAction.class.php                        # calls and sets $carouselItems array from /carousel directory
        └── templates
            └── homeSuccess.php
```

Carousel `homeaction.class.php` snippet:

```php
class StaticPageHomeAction extends StaticPageIndexAction
{
  public function execute($request)
  {
    parent::execute($request);

    if (null === $this->carouselItems) {
      $this->carouselItems = sfYaml::load('plugins/arXyzB5Plugin/carousel/config.yml');
    }

    $culture = $this->context->user->getCulture();
    if (!in_array($culture, ['en', 'fr'])) {
      $culture = 'en';
    }
    $this->culture = $culture;
  }
}
```

Carousel `homeSuccess.php` div snippet:

```php
 <div id="carousel" class="carousel carousel-fade slide">
    <div class="carousel-inner">
      <?php foreach ($carouselItems as $key => $item) { ?>
        <div class="item<?php if (0 == $n++) echo " active" ?>">
          <?php echo image_tag('/plugins/arXyzB5Plugin/carousel/' . $key) ?>
          <?php if ($item['fr'] && $sf_user->getCulture() == 'fr') { ?>
            <div class="carousel-caption">
              <p>
                <?php echo $item['fr'] ?>
              </p>
            </div>
          <?php } else { ?>
            <div class="carousel-caption">
              <p>
                <?php echo $item['en'] ?>
              </p>
            </div>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
  </div>
```

Carousel js snippet:

```js
(($) => {
  "use strict";

  $(() => {
    var node = $("#carousel");

    $(node)
      .imagesLoaded()
      .always(() => {
        $(".carousel-inner").slick({
          autoplay: true,
          autoplaySpeed: 5000,
          slidesToShow: 1,
          slidesToScroll: 1,
          draggable: false,
          swipe: false,
          arrows: true,
          dots: false,
        });
})(jQuery);
```
To execute the custom carousel script, run the following command:

`npm run build`

</details>

<details>
<summary><b>Newest Additions components</b></summary>
These snippets allow you to update the popular this week section on the homepage with a newest additions section.

`modules/default/templates/_newest.php` template snippet:
```php
<section id="newest-additions" class="card mb-3">
  <h2 class="h5 p-3 mb-0">
    <?php echo __('Newest additions'); ?>
  </h2>
  <div class="list-group list-group-flush">
<?php foreach ($newestAdditions as $item) { ?>
    <?php $object = QubitObject::getById($item); ?>
      <a
        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-break"
        href="<?php echo url_for([$object]); ?>">
        <?php echo render_title(esc_entities($object->__toString())) ?>
      </a>
    <?php } ?>
  </div>
</section>
```

`modules/default/actions/newestComponent.class.php` action code snippet:

```php
class DefaultNewestComponent extends sfComponent
{
    private function getNewestAdditions()
    {
        $rootIds = array(QubitInformationObject::ROOT_ID, QubitRepository::ROOT_ID, QubitActor::ROOT_ID);

        $sql = 'SELECT o.id FROM object o LEFT JOIN status s ON o.id = s.object_id WHERE o.class_name IN ("QubitInformationObject", "QubitRepository", "QubitActor") AND o.id NOT IN ( ' . implode(',', $rootIds) . ') AND s.status_id <> ? ORDER BY created_at DESC LIMIT 10';

        $rows = QubitPdo::fetchAll($sql, array(QubitTerm::PUBLICATION_STATUS_DRAFT_ID));
        return array_map(function($x) { return $x->id; }, $rows);
    }

    public function execute($request)
    {
        $this->newestAdditions = $this->getNewestAdditions();

        if (0 == count($this->newestAdditions)) {
            return sfView::NONE;
        }
    }
}
```

`modules/staticpage/templates/homeSuccess.php` homeSuccess snippet:

```php
 <?php echo get_component('default', 'newest', [
      'limit' => 10,
      'sf_cache_key' => $sf_user->getCulture(),
  ]); ?>
```

</details>

## Contributing code

We welcome code contributions to the public AtoM project. Thank you for
sharing with the community! This guide will help you familiarize yourself with
our coding style and code review guidelines.

Here's an outline of the contribution process:

1. Fork the Artefactual project on GitHub, and commit your changes to a [branch](#multi-line-assignments)
2. Do your own [testing](#testing) and initial review of your work
3. [Ready to contribute?](#ready-to-contribute) Open a [pull request](#submitting-a-pull-request)
4. Back and forth discussion with developers on the pull request
5. Once clarity has been reached in the discussions, make any outstanding
changes suggested by reviewers
6. Repeat 3 and 4 as necessary
7. Clean up the commit history, if necessary
8. Your branch will be merged!

Not all pull requests will be accepted, but the AtoM maintainers will provide an explantion of their decision.

### Naming development branches

When naming branches, Artefactual uses the following naming format:
`dev/issue-####-short-decscription`

* `dev/` marks it as being a development branch, not for QA integration or
  a stable release

* `issue-####` is the issue that the work is mostly related to

* `short-description` is a description of the branch in a few words, to make it
  easier to remember what the topic is. Example: `dev/issue-7129-csv-validator`

If you're not familiar with forking repositories and creating branches in
GitHub, consult their [guide](https://help.github.com/articles/fork-a-repo/).

## Ready to contribute?

* Make sure to sign [Contributor's Agreement here](https://drive.google.com/file/d/1rX_BIeToUpa0jJ69jLdxsvyKd3R-L6p4/view?usp=sharing)
* Learn about our [Coding standard](#coding-standards)
* Make sure you have [tests](#testing) for your change
* Submit a [pull request](#submitting-a-pull-request) with a clear commit history
* Submit a pull request to update to the [AtoM documentation](https://github.com/artefactual/atom-docs)
if your change requires it

### Submitting a pull request

The Developer Guidance in the Contributor's Portal has more details on submitting pull requests in GitHub.

When you make a new pull request, a set of Actions run in the AtoM GitHub repository to check your code for formatting issues and to ensure all tests succeed. These actions are all required to succeed before merging your pull request, so it's recommended to check that your code is formatted correctly and the tests succeed **before making a pull request**.

The pre-commit checks below describe how to check your code's formatting and how to run tests locally before making a pull request or pushing new commits.

Run PHP style checker:
```bash
docker compose exec atom composer php-cs-fix -- fix --dry-run -v
```

Locally run automated tests:

**Note: this command will wipe the database**
```bash
npx cypress run -b {browser name}.
```

For example:
```bash
npx cypress run -b firefox
```

PHP Unit tests (to run all of them):
```bash
docker compose exec atom composer test-cov
```

Prettier fix styling
```bash
npm run format
```

### Tips for submitting code to AtoM

1. Before starting on any new development work, review open issues to check if any describe your work. If there is an issue, comment on it with your intentions to provide a fix. If there isn't an open issue, open a new one so
the project maintainers and community members know not to duplicate work.

> **Note:** If you plan to submit a pull request on an issue, leave a
> comment for our developers that you are working on it and
> we will add the ***work-in-progress*** tag so that all contributors are aware
> that work is being done on this issue.

2. If you’re starting a new development project, we encourage developers to
**open pull requests early**. Clarify the a pull request is a work in progress
in the comments and add any additional information our developers or other
community contributors might need. You can also convert the pull request to
*draft* status.

3. We greatly value having some **simple comments in the code** that help to
explain what your code is doing and why - this helps us maintain your feature
through subsequent releases, and simplifies some of the code review.

4. Before submitting a pull request, please rebase your branch onto the
development branch (qa/2.x), resolve any conflicts, and perform basic testing
to ensure the fix or feature works with the latest release.

5. In general, AtoM modules and large features are based on [Symfony 1.x framework's plugin development model](https://symfony.com/legacy/doc/gentle-introduction/1_4/en/17-Extending-Symfony#chapter_17_plug_ins).
Please refer to our [qtSwordPlugin](https://github.com/artefactual/atom/tree/qa/2.x/plugins/qtSwordPlugin) or
[arRestApiPlugin](https://github.com/artefactual/atom/tree/qa/2.x/plugins/arRestApiPlugin)
for reference when developming new plugins for AtoM.

6. Spend some time reading existing AtoM code - especially in areas of the
application that relate to the work you are doing. We’re aiming for code
consistency, which helps us better maintain the application.

7. For large pull requests, we prefer if these can be broken up into
**atomic commits**. It simplifies code review as overly large pull requests may not
be merged if to complex. With atomic commits, our developers can review each
change and its rationale incrementally, making specific change requests for any
section that does not work.

8. Expect to make changes, and budget time accordingly. When our developers
review and approve community submitted code, we are taking on the maintenance
of the feature through subsequent releases. This means our team needs to ensure
it follows existing design patterns and can be readily understood and maintained by our devs.

9. There are some features that may not be desirable to all AtoM users. Our
usual recommendation in this case is that they be made configurable - either as
a truly optional plugin or as a setting in the Admin section, where users can
control whether or not the feature is enabled, or how it is enabled. The user
forum is a great place to go to sound out the desirablity of a feature.

10. Please be aware that we do not accept all pull requests. In cases where we are
unable to merge the code as is, we are happy to list the share the work so others may
use it in their own local customizations.


### Adding License information to your patch

If you are making a bug fix or enhancement to an existing file, simply add your name as one of the authors in the file header. Here's an example:

```code
/**
 * Extended methods for information object model
 *
 * @package AccesstoMemory
 * @subpackage model
 * @author Peter Van Garderen <peter@artefactual.com>
 * @author David Juhasz <david@artefactual.com>
 * '''@author YourNameHere <youremail@address>'''
 */
 ```

If you're contributing a new file, you need to add the following license header at the very top of the file.
Copy both sections, in full, exactly as it is written here, filling in the information where indicated.

```code
/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 *
 * '''description of what your new file does'''
 *
 * @package AccesstoMemory
 * @subpackage '''name of AtoM module or component to which your file contributes'''
 * '''@author YourNameHere <youremail@address>'''
 */
 ```

## Contributor's Agreement

In order for the AtoM development team to accept any patches or
code commits, contributors must first sign this [Contributor's Agreement](https://drive.google.com/file/d/1rX_BIeToUpa0jJ69jLdxsvyKd3R-L6p4/view?usp=sharing).
The AtoM contributor's agreement is based almost verbatim on the
[Apache Foundation's](http://apache.org/) individual [contributor license](http://www.apache.org/licenses/icla.txt).

If you have any questions or concerns about the Contributor's Agreement,
please email us at <agreement@artefactual.com> to discuss them.

### Why do I have to sign a Contributor's Agreement?

One of the key challenges for open source software is to support a
collaborative development environment while protecting the rights of
contributors and users over the long-term. Unifying AtoM copyrights through
contributor agreements is the best way to protect the availability and
sustainability of AtoM over the long-term as free and open-source software. In
all cases, contributors who sign the Contributor's Agreement retain full rights
to use their original contributions for any other purpose outside of AtoM,
while enabling Artefactual Systems, any successor organization which may
eventually take over responsibility for AtoM, and the wider AtoM community to
benefit from their collaboration and contributions in this open source project.

[Artefactual Systems](http://artefactual.com/) has made the decision and has a
proven track record of making our intellectual property available to the
community at large. By standardizing contributions on these agreements the AtoM
intellectual property position does not become too complicated. This ensures
our resources are devoted to making our project the best they can be, rather
than fighting legal battles over contributions.

### How do I send in an agreement?

Please read and sign the [Contributor's Agreement](https://drive.google.com/file/d/1rX_BIeToUpa0jJ69jLdxsvyKd3R-L6p4/view?usp=sharing) and email it to
<agreement@artefactual.com>.

Alternatively, you may send a printed, signed agreement to:

```text
Artefactual Systems Inc.
#2 - 10138 Whalley Blvd.
Surrey BC  V3T 4H4
Canada
```

## Coding standards

AtoM uses PHP CS Fixer to check and auto-format the PHP code following the
[@PhpCsFixer ruleset](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer), a highly
opinionated extension of the [Symfony Coding Standards](https://symfony.com/doc/current/contributing/code/standards.html#symfony-coding-standards-in-detail)
and the [PSR-12 coding style specification](https://www.php-fig.org/psr/psr-12/).

The PHP CS Fixer tool is included in the AtoM project's Composer dependencies
for development. PHP CS Fixer's configuration is tracked as part of the
project's source code (see [.php-cs-fixer.dist.php](https://github.com/artefactual/atom/blob/qa/2.x/.php-cs-fixer.dist.php))
and it's used in the
[Continuous Integration process](https://github.com/artefactual/atom/actions/workflows/syntax-checks.yml)
to check the code on every pull request and commit merged to the `stable/**`
and `qa/**` branches.

Contributors to the AtoM project should run PHP CS Fixer locally to ensure
their modifications meet the coding standards. There are a number of options
for running PHP CS Fixer on your code: [PHP CS Fixer's README file](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer#editor-integration)
links to plugins for several popular code editors (e.g. VS Studio Code, Sublime text), you can configure a git
[pre-commit hook to run PHP CS Fixer when commiting changes](https://itnext.io/learning-to-add-git-hook-tasks-php-cs-fixer-41f34d99aa8a)
, or PHP CS Fixer can be [run manually](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer#usage).

### Coding standards that are not handled by PHP CS Fixer

While PHP CS Fixer covers a lot of rules to meet the coding standard, there are
a few cases that need to be handled manually, especially for multi-line
statements. The [PSR-12 standard](https://www.php-fig.org/psr/psr-12/#23-lines)
doesn't impose a hard limit on line length, but it recommends a soft limit of
120 characters, and to keep lines under 80 characters where possible. However,
the following cases need to be manually edited at the moment.

* [Multi-line method calls](#multi-line-method-calls)
* [Multi-line control structures](#multi-line-control-structures)
* [Multi-line assignments](#multi-line-assignments)

### Multi-line method calls

Due to an issue formatting the Symfony templates, the `ensure_fully_multiline` option of the `method_argument_space` rule is currently disabled. Nevertheless, as noted in the [PSR-12 standard](https://www.php-fig.org/psr/psr-12/#47-method-and-function-calls):

> Argument lists MAY be split across multiple lines, where each subsequent line
> is indented once. When doing so, the first item in the list MUST be on the
> next line, and there MUST be only one argument per line. A single argument
> being split across multiple lines (as might be the case with an anonymous
> function or array) does not constitute splitting the argument list itself.

```php
$foo->bar(
    $longArgument,
    $longerArgument,
    $muchLongerArgument
);
```

**N.B.** AtoM argument lists SHOULD be split into multiple lines, according to
the rules above, to keep the line length under 80 characters.

### Multi-line control structures

The [PSR-12 standard on control structures](https://www.php-fig.org/psr/psr-12/#5-control-structures) is not fully covered by PHP CS Fixer currently, but
control structure formatting follows the same pattern as the method call
formatting, with the additional rule:

> Boolean operators between conditions MUST always be at the beginning or at
> the end of the line, not a mix of both.

```php
if (
    $expr1
    && $expr2
) {
    // if body
} elseif (
    $expr3
    && $expr4
) {
    // elseif body
}
```

**N.B.** AtoM boolean operators MUST come at the beginning of the line, as shown
above, when boolean statements are split over multiple lines.

### Multi-line assignments

The PSR-12 standard doesn't specify how to format multi-line assignments, nor
does the @PhpCsFixer ruleset. However, the AtoM standard formats multi-line
assignments thus:

* The assignment statement starts on the same line as the assigned variable or
return keyword.
* Subsequent operators come at the start of the following lines, with
indentation.

For example:

```php
$foo = $condition
    ? 'true value'
    : 'false value';

return $conditionOne
    && $conditionTwo
    && $conditionThree;

$sum = $sumOne
    + $sumTwo
    + 123;

return $stringOne
    .$stringTwo
    .' extra content';

$bar = 'Long string
    where whitespace
    is not an issue';
```

**N.B.** statements SHOULD NOT be broken over multiple lines unless it is
necessary to keep lines under the recommended 80 character line length.

## Testing

We prefer tests to be included with new pull requests. If the section of code you are working on is not written in a way to allow the addition of unit tests, we encourage you to consider refactoring the code rather than leaving it. You can also get in touch
with the project maintainers for further advice by commenting on your issue, pull request or emailing <maintainers@artefactual.com>.

### Unit tests

[AtoM's unit tests](https://github.com/artefactual/atom/tree/qa/2.x/test) are developed with
[PHPUnit](https://phpunit.de/). The
required dependencies are managed with Composer and
included in the development
environments. At the moment, these tests require a configured instance and
connection to the MySQL and Elasticsearch servers.

The coverage report is generated in a ".coverage/html" sub-directory of the
AtoM directory.

### Integration tests

[AtoM's integration tests](https://github.com/artefactual/atom/tree/qa/2.x/cypress) are developed with [Cypress](https://www.cypress.io/).
The required dependencies are managed with NPM but they are not included by
default in the development environments. These tests require a browser and they
are meant to be run in the host of the development environment or in a
different machine with access to the AtoM site.

## Contributing documentation and translations

If you would like to [help us improve the AtoM documentation, please see our
wiki](https://wiki.accesstomemory.org/Resources/Documentation) for more information.

Additionally, with each new AtoM release we include user interface
translations generously provided by our volunteer translator community. Here is the
information on [how to help contribute translations](https://wiki.accesstomemory.org/Resources/Translation)

## Additional Resources

* [AtoM User Forum](https://groups.google.com/forum/#!forum/ica-atom-users)
* [Contributors Portal](https://contributors.artefactual.com/)
* [General GitHub documentation](http://help.github.com/)
* [Artefactual Systems](http://www.artefactual.com/)
* [List of contributors](https://wiki.accesstomemory.org/wiki/Community/Contributors) - before version 2.8.1
* [List of Contributors](https://github.com/artefactual/atom/releases) - from version 2.8.1

Thanks!
