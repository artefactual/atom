# AtoM on Symfony 7.4

This document is the technical and operational guide to the Symfony 7.4 work
on the `dev/sf7` branch. It is intended to stand on its own for developers,
reviewers, operators, and project stakeholders.

The work began at commit
`db06a3041759ed53b9eb777608a011cd05de24f0` (`Add Symfony 7.4 foundation`).
It replaces the Symfony 1 runtime in place while deliberately preserving the
recognizable AtoM application above it.

The result is an advanced proof of concept, not a second AtoM product and not
a permanently supported compatibility mode. Symfony 7.4 is the only
application runtime on this branch.

## Contents

- [Executive summary](#executive-summary)
- [Why this work exists](#why-this-work-exists)
- [The architectural decision](#the-architectural-decision)
- [Long-term architectural direction](#long-term-architectural-direction)
- [Goals and non-goals](#goals-and-non-goals)
- [Solution architecture](#solution-architecture)
- [How compatibility works](#how-compatibility-works)
- [Runtime request flows](#runtime-request-flows)
- [Configuration conventions](#configuration-conventions)
- [Application and infrastructure boundaries](#application-and-infrastructure-boundaries)
- [Safety and maturity](#safety-and-maturity)
- [Branch narrative](#branch-narrative)
- [Developer quick start](#developer-quick-start)
- [Operator and evaluator quick start](#operator-and-evaluator-quick-start)
- [Optional host-PHP workflow](#optional-host-php-workflow)
- [Working on this branch](#working-on-this-branch)
- [Production considerations](#production-considerations)
- [Known limitations and open risks](#known-limitations-and-open-risks)
- [Future directions](#future-directions)
- [Versioning and release naming](#versioning-and-release-naming)
- [Frequently asked questions](#frequently-asked-questions)

## Executive summary

AtoM 2.x is a large, mature application whose structure and extension
conventions are deeply connected to Symfony 1. Rewriting it all at once would
discard years of working behaviour and make regressions difficult to isolate.
Continuing to run Symfony 1 would leave the project dependent on an obsolete
framework.

This branch takes a third route:

1. Symfony 7.4 owns the modern runtime responsibilities: HTTP, routing,
   configuration loading, dependency injection, sessions, console integration,
   maintained framework components, and error handling.
2. A small AtoM-owned compatibility layer translates the conventions that the
   application still uses.
3. Existing actions, components, templates, forms, helpers, plugins, and
   Propel models remain the application layer.
4. Compatibility is removed gradually as individual areas are refactored,
   rather than by maintaining two permanent runtimes.

The word **shim** in this document means AtoM-owned adapters running on
Symfony 7. It does not mean Symfony 1 is booted inside Symfony 7. The old
Symfony action runtime is deliberately absent from the production image.

This approach gives the project a stable intermediate architecture:

- the obsolete runtime is removed now;
- the amount of application churn is constrained;
- existing AtoM configuration remains meaningful;
- new code can use current Symfony services and components;
- future refactoring can proceed one bounded area at a time;
- failures can be attributed to a relatively narrow runtime boundary;
- database and service upgrades remain separate decisions.

The branch is already substantially beyond a skeleton. It boots the web and
console applications, dispatches existing modules, enforces authentication and
authorization, renders legacy views, executes background work, supports fresh
installation and upgrade workflows, builds a minimal production image, and
exercises authenticated administrator workflows in real browsers.

That evidence should not be confused with a mathematical claim of universal
compatibility. Local plugins, custom themes, unusual data, and deployments at
different scales still need representative testing.

## Why this work exists

### The problem with the current foundation

Symfony 1 shaped AtoM's directory layout and conventions, but it has been
unmaintained for many years. Its presence creates several connected problems:

- unsupported framework code sits on the request path;
- newer PHP versions expose increasing compatibility gaps;
- security and correctness fixes cannot be inherited from a maintained
  framework;
- dependency upgrades are constrained by assumptions in the old runtime;
- new contributors must learn an obsolete framework before changing AtoM;
- application behaviour and framework internals are difficult to separate;
- operational tooling cannot rely on current framework practices.

Replacing Symfony 1 therefore matters even if no user-facing feature changes.
It is a risk-reduction and maintainability project.

### Why not rewrite AtoM

AtoM contains more than HTTP controllers. Its behaviour is distributed across
actions, forms, templates, configuration, plugins, translations, Propel
models, importers, exporters, background jobs, and years of domain-specific
edge cases.

A complete rewrite would create a long period in which two incomplete
applications had to be compared. It would also turn every existing behaviour
into a new design decision. That makes review harder, not easier: a regression
could originate in new domain code, new persistence code, new templates, or a
new framework integration.

This branch instead treats the existing application as an asset. It replaces
the foundation underneath it, adds explicit tests around the boundary, and
leaves domain refactoring for later iterations.

### Why Symfony 7.4

Symfony is modular enough to adopt at the responsibility level. AtoM can use
the maintained HTTP kernel, router, service container, console, YAML loader,
translation, form, validator, security primitives, and other components
without first converting every module to a modern Symfony bundle.

Symfony 7.4 is also a long-term support release. That provides a maintained
target with a predictable support window while the application is gradually
modernized.

Modularity is important here because the migration does not need to make these
changes simultaneously:

- replace the request lifecycle;
- redesign every controller;
- replace Propel;
- convert every template;
- redesign plugin APIs;
- change the database;
- upgrade Elasticsearch;
- change the job queue.

The branch changes the first item and builds seams for the others.

## The architectural decision

The core decision is to evolve AtoM **in place**.

There is no `ATOM_RUNTIME` switch and no supported Symfony 1 fallback. A
request either succeeds through the Symfony 7 runtime or it exposes a defect
that must be fixed and covered by a test.

This is preferable to a dual runtime for several reasons:

- every test exercises the architecture intended for deployment;
- there is only one routing and session model to reason about;
- fixes cannot accidentally land only in a fallback path;
- production images do not carry an unused application framework;
- the project does not inherit the cost of keeping two implementations aligned;
- removal of an adapter becomes possible as soon as its callers are migrated.

Rollback remains an operational concern, but it is handled through normal
release practices: immutable images, code revisions, database backups, tested
upgrade procedures, and deployment rollback. A hidden runtime switch would not
make incompatible database changes safe.

### Why minimize application changes

The compatibility layer exists to avoid spreading framework migration changes
through thousands of application call sites.

An existing action should generally remain an existing action. The same is
true for a Propel model, a PHP template, a plugin module, or a configuration
file. If its behaviour is valid, changing its shape during the runtime
migration adds review cost without improving the migration boundary.

This produces a useful division of responsibility:

- `src/`, front controllers, and infrastructure contain most runtime work;
- existing application code changes only when a real incompatibility or defect
  is demonstrated;
- generated Propel code is not hand-edited;
- tests document intentional adaptations;
- later refactors can be reviewed as refactors, not hidden inside the runtime
  replacement.

Minimal change does not mean preserving every historical implementation
forever. It means sequencing the work so that the runtime can be replaced
safely before the application is redesigned.

## Long-term architectural direction

This runtime replacement and a broader API-led architecture are not
incompatible. They address different boundaries and can reinforce each other.

The work in this branch modernizes the foundation beneath the existing AtoM
application. It preserves the web interface, extensions, operational workflows,
and domain behaviour while removing Symfony 1 as a runtime dependency. That is
valuable independently of how future clients interact with AtoM.

A new API boundary could evolve alongside it, offering a deliberate contract
for integrations and new applications without exposing internal routes,
actions, or database structures. The existing AtoM domain can remain
authoritative at first, while implementation details behind the API are
refactored or replaced gradually.

Together, these approaches suggest an incremental architecture:

- the Symfony 7 runtime provides a maintained home for the existing product;
- a stable API provides a forward-looking boundary for new consumers;
- domain data, permissions, and behaviour retain clear ownership;
- capabilities move behind cleaner internal services when there is a proven
  reason to extract them;
- compatibility tests protect behaviour while those boundaries evolve.

This avoids making a complete rewrite the price of modernization. It also
avoids treating the compatibility layer as the final architecture. The runtime
bridge creates safety and time; an API boundary can create room for new clients
and deeper change. Each can progress at its own pace, provided that ownership
remains explicit and the two paths do not develop competing versions of the
same domain rules.

## Goals and non-goals

### Current goals

- Run AtoM on PHP 8.3 and Symfony 7.4.
- Remove Symfony 1 from the web and console request lifecycles.
- Preserve existing AtoM routes and configuration conventions.
- Preserve existing actions, components, templates, forms, plugins, and
  Propel-backed domain behaviour where practical.
- Preserve authentication, authorization, sessions, filters, translations,
  view configuration, imports, exports, and background jobs.
- Support fresh installation and upgrades from historical AtoM databases.
- Produce a small, immutable, non-root production container.
- Provide health checks and repeatable production verification.
- Exercise public and authenticated workflows in Chrome and Firefox.
- Make future modernization incremental rather than all-or-nothing.

### Explicit non-goals

- Maintain a Symfony 1 compatibility runtime.
- Rewrite the AtoM domain model.
- Replace Propel as part of the runtime migration.
- Convert all PHP templates to Twig.
- Convert every action to a native Symfony controller immediately.
- Redesign AtoM's plugin system in the same change.
- Upgrade Elasticsearch, Percona, Gearman, or Memcached merely to justify a
  new release number.
- Promise compatibility with every third-party plugin or theme without testing
  it.
- Define the final product version or release policy from architecture alone.

## Solution architecture

### High-level view

```text
                         Existing AtoM configuration
                    routes.yml, app.yml, settings.yml,
                  security.yml, filters.yml, view.yml, ...
                                  |
                                  v
Nginx / FastCGI -> index.php -> Symfony 7.4 Kernel
                                  |
                         compiled Symfony routes
                                  |
                                  v
                           LegacyController
                                  |
                 AtoM request context and filter chain
                                  |
                     security / session / user state
                                  |
                                  v
                           ActionRunner
                                  |
              existing actions, components, forms, helpers
                                  |
                   existing PHP templates and layouts
                                  |
                     Propel models and domain services
                                  |
         Percona | Elasticsearch | Memcached | Gearman

CLI -> symfony -> Symfony Console -> ConsoleRuntime
                                  |
                       existing AtoM task classes

Worker -> production entry point -> existing Gearman tasks
                                  |
                      same Symfony 7 bootstrapping
```

The `LegacyController` name describes the shape of the application being
adapted. It is not a Symfony 1 controller.

### Major components

| Area | Symfony 7 / AtoM runtime responsibility | Preserved application surface |
| --- | --- | --- |
| Boot | `Atom\Kernel`, Composer autoloading, service container | Application and plugin class directories |
| HTTP | HttpKernel request and response lifecycle | Existing module/action semantics |
| Routes | Compile AtoM routing configuration to Symfony routes | Project, application, and plugin `routing.yml` |
| Dispatch | Resolve modules and run the filter/action pipeline | `actions.class.php` and action classes |
| Context | Request-scoped AtoM services | Familiar `sfContext`-style access points |
| Security | Authentication, credentials, module/action rules | Existing `security.yml` and user permissions |
| Sessions | Symfony session infrastructure | Existing AtoM user/session expectations |
| Views | AtoM template, component, layout, slot, and asset runtime | PHP templates, layouts, `view.yml` |
| Forms | Maintained form/validator components plus adapters | Existing AtoM form classes |
| Translation | Maintained translation infrastructure | Existing catalogues and translation calls |
| Persistence | Runtime initialization and service integration | Propel 1 models and generated classes |
| Console | Symfony Console application and command wrapping | Existing task classes and command names |
| Plugins | Plugin discovery, configuration, class and module paths | Enabled AtoM plugins and their conventions |
| Jobs | Symfony 7 bootstrap and health integration | Existing Gearman job behaviour |
| Operations | Containers, health checks, verification scripts | Current AtoM service topology |

### Code map

The principal implementation locations are:

| Path | Purpose |
| --- | --- |
| [`src/Kernel.php`](Kernel.php) | Application composition, routes, services, framework configuration, and boot |
| [`src/Controller/`](Controller/) | Symfony controllers that enter the existing application and render errors |
| [`src/EventSubscriber/`](EventSubscriber/) | Request hooks for users, Propel, and resource routes |
| [`src/Console/`](Console/) | Symfony Console application entry and command registration |
| [`src/Framework/Autoload/`](Framework/Autoload/) | Narrow loader for application, plugin, and generated classes |
| [`src/Framework/Bridge/`](Framework/Bridge/) | AtoM context, dispatch, views, aliases, and compatibility entry points |
| [`src/Framework/Configuration/`](Framework/Configuration/) | Existing configuration discovery, loading, merging, and compilation |
| [`src/Framework/Console/`](Framework/Console/) | Existing task discovery and CLI context |
| [`src/Framework/Database/`](Framework/Database/) | Propel initialization |
| [`src/Framework/Filter/`](Framework/Filter/) | Configured request filter pipeline |
| [`src/Framework/Form/`](Framework/Form/) | Form, widget, validator, and CSRF adapters |
| [`src/Framework/Health/`](Framework/Health/) | Role-aware liveness and readiness probes |
| [`src/Framework/Module/`](Framework/Module/) | Action, component, template, and layout location |
| [`src/Framework/Plugin/`](Framework/Plugin/) | Plugin discovery and initialization |
| [`src/Framework/Routing/`](Framework/Routing/) | Route compilation and resource route resolution |
| [`src/Framework/Security/`](Framework/Security/) | Existing security configuration resolution |
| [`src/Framework/Session/`](Framework/Session/) | Symfony session integration |
| [`src/Framework/Translation/`](Framework/Translation/) | Existing catalogue and translation integration |
| [`test/framework/`](../test/framework/) | Focused contracts for the new runtime |
| [`playwright/`](../playwright/) | Browser workflows and authenticated surface inventory |

This map also shows the intended isolation: the new runtime is concentrated in
`src/`, while the established application remains under `apps/`, `lib/`, and
`plugins/`.

### Kernel and boot sequence

`src/Kernel.php` is the main composition root. During boot it:

1. registers AtoM-owned bridge classes and helper functions;
2. installs a class loader for application, plugin, and generated classes;
3. loads and merges the existing configuration layers;
4. initializes enabled plugins;
5. registers runtime services in the Symfony container;
6. compiles configured AtoM routes into Symfony routes;
7. boots request-scoped AtoM context and user state when a request arrives.

Composer is authoritative for maintained dependencies and new namespaced code.
The AtoM class loader is intentionally narrower: it resolves application,
plugin, and generated classes without restoring Symfony 1's runtime loader.

### The compatibility layer

Most compatibility code lives under `src/Framework`. Its responsibilities are
small in concept even though the covered surface is broad:

- read existing configuration;
- translate configuration into current runtime objects;
- locate existing modules, actions, components, templates, and plugins;
- expose request-scoped services using interfaces expected by application code;
- translate control-flow exceptions into Symfony responses;
- preserve established helper and view behaviour;
- adapt old form, validator, task, and utility entry points;
- initialize Propel and external services.

`src/Framework/Bridge/BridgeRegistrar.php` registers familiar `sf*` class
names as aliases of AtoM-owned or maintained implementations. This avoids a
mechanical edit of every caller while changing what those names resolve to.

An `sf*` name on this branch is therefore not evidence that Symfony 1 is
running. The important question is which class implements the name. Production
verification explicitly checks that Symfony 1 action code is absent.

### The isolated Propel 1 dependency

Propel 1 is still the persistence layer. Its exact AtoM-patched runtime is
isolated under `vendor/propel1/runtime`; its generator lives alongside it as
development-only build tooling. The historical `sfPropelPlugin` and Symfony 1
vendor trees are not retained.

That is a deliberate exception, not a hidden framework fallback:

- no Symfony 1 framework or plugin files are present;
- Propel is initialized by the new runtime;
- generated Propel code is treated as generated code and is not hand-edited;
- replacing Propel is a future bounded migration, not a prerequisite for
  replacing the HTTP framework.

This distinction should remain explicit in code review and deployment audits.

## How compatibility works

Compatibility is implemented at conventions and boundaries, not by preserving
the obsolete framework process.

### Routes

The route compiler reads the application's existing route definitions,
including resource routes, and creates Symfony routing objects. Existing route
names, parameters, defaults, requirements, and module/action destinations can
therefore continue to drive URL matching and generation.

Symfony's router performs the match. The resulting module and action are then
passed to the AtoM dispatcher.

### Actions and components

The dispatcher resolves module files in project and plugin locations, creates
the existing action, and executes it through an AtoM-owned action runner.
Component calls use the same locator and view infrastructure.

The runtime preserves established results such as:

- selecting a named view;
- forwarding to another module/action;
- returning a response directly;
- stopping execution;
- producing a not-found response;
- rendering a partial or component;
- applying a layout.

### Filters, security, and users

Configured filters run around action execution. Security configuration is
resolved for the current module and action, and credentials are enforced
before protected work is allowed.

The AtoM user object is persisted through Symfony-backed sessions. This is
important because authentication compatibility is not merely the ability to
display a login page: subsequent requests must reconstruct the same identity,
credentials, culture, and other session state.

### Forms and validation

Existing form classes can remain in place. Bridge types map their expected
form, widget, validator, error, CSRF, and event behaviour to maintained
implementations. Compatibility fixes are tested at the runtime layer and in
real administrator creation/edit workflows.

Forms are one of the most sensitive compatibility surfaces because successful
rendering is insufficient. Tests must also cover binding, validation errors,
CSRF handling, persistence, redirects, and the resulting record.

### Views, helpers, and escaping

The view runtime locates the existing PHP template, applies configured assets
and formats, resolves helpers, renders components and slots, and wraps the
result in the configured layout.

Legacy output escaping and dynamic-property behaviour are centralized rather
than reimplemented ad hoc in templates. This keeps compatibility visible and
makes later removal measurable.

### Plugins

Enabled plugins contribute configuration, class paths, modules, routes,
templates, and services through the new runtime. The objective is to preserve
AtoM's plugin structure, not to pretend third-party code is automatically
compatible with PHP 8.3.

Each deployed plugin should still be tested for:

- Symfony 1 classes that are outside the provided bridge;
- PHP language incompatibilities;
- assumptions about removed framework internals;
- custom filters, validators, or route classes;
- filesystem and container assumptions;
- background jobs and external binaries.

### Console tasks

The `symfony` executable now starts Symfony Console. `ApplicationRunner`
discovers and wraps existing AtoM task classes so established command names can
continue to work while benefiting from a maintained console lifecycle.

New commands can be written as native Symfony Console commands. Existing tasks
can be migrated independently when their behaviour is being changed.

## Runtime request flows

### Web request

1. Nginx sends the request to PHP-FPM.
2. `index.php` creates the Symfony 7 kernel.
3. Trusted proxy and host rules are applied.
4. Symfony starts or resumes the configured session.
5. The Symfony router matches a route compiled from AtoM configuration.
6. `LegacyController` creates a request-scoped AtoM context.
7. The filter chain applies security and other configured behaviour.
8. `ActionRunner` executes the existing module/action.
9. The view runtime renders a template and layout when required.
10. Symfony sends the final response and terminates the request.

The development front controller follows the same architecture and adds the
expected development access checks and debug behaviour.

### Console command

1. `php symfony ...` creates the Symfony 7 kernel in the CLI environment.
2. Symfony Console registers native commands.
3. `ConsoleRuntime` discovers compatible existing task classes.
4. The requested command executes with AtoM configuration and services.
5. Request-global bridge state is cleaned at command termination.

### Background worker

The production container accepts `worker` as its role. The entry point boots
the same application code and runs the existing Gearman worker. Readiness is
role-aware: a worker is not considered ready merely because PHP starts; it
must connect to required services and register with Gearman.

Web and worker processes must use the exact same application image in a
deployment. Building separate code states would invalidate much of the
compatibility evidence.

## Configuration conventions

Preserving AtoM's configuration conventions is a central design constraint.
The runtime understands the existing configuration layers rather than
requiring an immediate rewrite of every YAML file.

The supported surface includes the familiar roles of:

- `app.yml` for application parameters;
- `settings.yml` for application and framework settings;
- `routing.yml` for route definitions;
- `security.yml` for module/action security;
- `filters.yml` for the request filter chain;
- `view.yml` for layouts, formats, helpers, and assets;
- module configuration;
- plugin configuration;
- factories and external service settings.

Configuration is loaded from the existing project, application, module, and
plugin locations and compiled into runtime services and parameters. Environment
overrides remain part of that process.

### Runtime environment variables

Container and production concerns that should not live in committed YAML are
available through environment variables. Important examples include:

| Variable | Purpose |
| --- | --- |
| `APP_SECRET` | Runtime secret when no existing configured secret is used |
| `ATOM_READ_ONLY` | Enable application read-only behaviour |
| `ATOM_SESSION_STORAGE` | Select `file` or `memcache` session storage |
| `ATOM_SESSION_PATH` | Directory for file-backed sessions |
| `ATOM_SESSION_TTL` | Session lifetime |
| `ATOM_SESSION_PREFIX` | Session key prefix |
| `ATOM_MEMCACHED_HOST` | Memcached endpoint |
| `ATOM_TRUSTED_PROXIES` | Trusted reverse proxies |
| `ATOM_TRUSTED_HOSTS` | Allowed host patterns |
| `ATOM_GEARMAND_HOST` | Gearman endpoint |
| `ATOM_ELASTICSEARCH_HOST` | Elasticsearch endpoint |

Symfony-compatible trusted proxy and host variables are accepted as fallbacks
where appropriate. See `src/Framework/Configuration/RuntimeOptions.php` for the
authoritative parsing and defaults.

Secrets should be provided by the deployment platform. The development
environment file under `docker/etc/environment` is for local use and automated
tests, not a production secret source.

## Application and infrastructure boundaries

### Application layer retained for now

- AtoM modules and actions
- components and partials
- PHP templates and layouts
- forms and validators
- helpers
- plugins
- translation catalogues
- Propel models and generated classes
- domain services
- import and export logic
- current Gearman tasks

### Runtime layer replaced or newly owned

- kernel boot
- dependency injection
- routing execution
- request and response lifecycle
- session integration
- error handling
- module and resource resolution
- action/filter execution
- view orchestration
- console application
- production health and readiness
- production container construction

### External services intentionally unchanged

The reference environments continue to use the current AtoM service family:

- Nginx and PHP-FPM;
- Percona Server 8.4;
- Elasticsearch OSS 7.10.2;
- Memcached;
- Gearman.

Keeping these stable isolates the framework migration. Service upgrades can be
planned and tested separately and do not need to be bundled with a release of
this runtime.

The published development service images support ARM64. Apple Silicon and
other ARM-based developers should use the normal Compose files. Do not add
`platform: linux/amd64` for routine development; doing so forces emulation and
usually makes the environment slower.

## Safety and maturity

### What “advanced proof of concept” means

This branch is described as a proof of concept because it has not yet passed a
project release process or broad community deployment. It is described as
advanced because the proof covers complete application and operational flows,
not only a booting home page.

At the time this guide was written, the branch had demonstrated:

- a fresh demo installation;
- the public web application;
- authenticated administrator navigation;
- creation and editing of records;
- users with different permissions;
- imports and exports;
- digital object workflows;
- global replacement;
- background jobs;
- Symfony Console tasks;
- production web and worker containers;
- readiness of database, search, cache, and queue dependencies;
- an upgrade from a historical v1.9.3 database;
- a repeated upgrade for idempotence;
- restoration of the pre-upgrade database with an exact data fingerprint;
- Chrome and Firefox coverage of the declared authenticated surface;
- clean runtime logs without accepted PHP warnings or deprecations.

The latest full branch validation recorded:

- 179 framework tests with 498 assertions;
- 546 application tests with 1,067 assertions;
- 143 successful Playwright executions across Chrome and Firefox;
- 74 authenticated route surfaces covered in each browser;
- 100% coverage of the declared authenticated navigation inventory.

These counts will evolve. The test commands and CI results are authoritative,
not the numbers copied into this explanation.

### Compatibility is evidence, not a slogan

“100% compatibility” is not a useful unconditional claim for an extensible
application. No finite test suite can enumerate every local plugin, theme,
database history, language, browser, integration, or data volume.

The branch instead aims for:

- complete coverage of an explicit supported surface;
- regression tests for every compatibility defect found;
- representative historical database upgrades;
- production-image tests rather than source-only tests;
- clean logs, because PHP warnings often reveal hidden incompatibility;
- transparent documentation of untested surfaces.

For a particular deployment, compatibility confidence comes from adding that
deployment's plugins, theme, representative database, integrations, and
operational load to the same process.

### Test layers

| Layer | What it proves |
| --- | --- |
| Framework unit/integration tests | Runtime adapters implement their contracts |
| Application tests | Existing AtoM behaviours remain functional |
| Browser tests | Real routing, sessions, forms, JavaScript, and permissions work together |
| Navigation inventory | Declared authenticated areas do not silently lose coverage |
| Image verification | Production contents, user, extensions, labels, and exclusions are correct |
| Runtime verification | The exact image serves HTML and runs a registered worker |
| Log gate | Warnings, notices, deprecations, uncaught errors, and worker failures fail CI |
| Fresh installation | A clean environment can be initialized without development artifacts |
| Upgrade rehearsal | Historical data upgrades, repeats, and restores safely |

### Production image properties

The multi-stage `Dockerfile` creates separate development and production
targets. The production target:

- contains built front-end assets;
- installs Composer dependencies without development packages;
- omits Node, npm, `node_modules`, Playwright, PHPUnit, Phing, vendored test
  fixtures, and host-only build scripts;
- contains no Symfony 1 framework or plugin tree;
- retains only the Propel runtime required by the application, not its
  development generator;
- runs as the unprivileged `atom` user with UID/GID 1000;
- includes a role-aware readiness health check;
- records the source revision as an OCI image label.

The verifier checks these properties instead of relying on Dockerfile review
alone.

### Health checks

`php docker/healthcheck.php live` answers whether the process can start.

`php docker/healthcheck.php ready` answers whether the selected role can do
useful work. Depending on the role and configuration, readiness verifies:

- Percona connectivity;
- an acceptable Elasticsearch cluster state;
- Memcached when used for sessions;
- Gearman connectivity;
- worker registration for the worker role.

Liveness and readiness should not be interchanged in an orchestrator.
Liveness is intentionally shallow; readiness protects traffic and job
execution from unavailable dependencies.

## Branch narrative

The commit series beginning at `db06a304...` is intended to be reviewable as a
story. Its broad phases are:

### 1. Establish the Symfony foundation

The initial commits add Symfony 7.4 dependencies, configuration loading,
routing translation, the kernel, Composer-based application loading, and
module resolution.

This creates a narrow vertical slice before attempting every feature.

### 2. Restore the AtoM request lifecycle

Subsequent commits add action dispatch, Propel initialization, resource routes,
security, sessions, filters, plugins, layouts, components, assets,
translations, forms, and output behaviour.

The application becomes progressively usable while the old framework remains
out of the request path.

### 3. Replace entry points and utilities

Web front controllers and console tasks move to Symfony 7. Old utility
services and plugin initialization points are replaced with AtoM-owned
implementations.

### 4. Harden real workflows

Imports, validation, exports, digital objects, administrator forms,
permissions, integrations, translations, and maintenance tools receive
targeted fixes and regression tests.

This phase is where keeping application changes small is most valuable. Each
change can be tied to observed behaviour rather than a speculative rewrite.

### 5. Add production and upgrade evidence

The branch adds a minimal production image, non-root execution, state
isolation, health checks, image inspection, runtime smoke tests, fresh
installation, upgrade rehearsal, repeatability checks, and rollback
fingerprinting.

### 6. Expand browser coverage

Authenticated administrator workflows are added and Cypress is replaced by
Playwright. Chrome and Firefox execute the same inventory, including
authority boundaries and creation/import flows.

### 7. Isolate and document the runtime

Later commits remove accidental development coupling, centralize adaptations,
exclude obsolete artifacts, harden CI, isolate browser state, and document the
architectural boundary.

The sequence is important. It lets a reviewer evaluate the foundation,
compatibility restoration, workflow hardening, and deployment evidence as
separate ideas even when individual commits are sizeable.

## Developer quick start

The Compose development environment is the recommended starting point because
it matches the tested PHP extensions and service versions.

### Prerequisites

- Docker Engine or Docker Desktop with Compose v2
- Git
- at least 8 GiB of memory available to the container runtime
- Node.js 22 and npm when running Playwright from the host

Run all commands from the repository root.

### First installation

Set the development Compose file for the current shell:

```bash
export COMPOSE_FILE="$PWD/docker/docker-compose.dev.yml"
```

Build the development image and start state services:

```bash
docker compose build atom atom_worker
docker compose up -d percona elasticsearch memcached gearmand
```

Wait for those services to accept connections:

```bash
until docker compose run --rm atom \
  php docker/healthcheck.php ready
do
  sleep 2
done
```

Install AtoM into the empty development database:

```bash
docker compose run --rm atom php symfony tools:install \
  --database-host=percona \
  --database-port=3306 \
  --database-name=atom \
  --database-user=atom \
  --database-password=atom_12345 \
  --search-host=elasticsearch \
  --search-port=9200 \
  --search-index=atom \
  --demo \
  --no-confirmation
```

The installer is destructive to the selected database. Use it only for a new
local environment or a database that can be discarded.

Start the application:

```bash
docker compose up -d atom atom_worker nginx
docker compose ps
```

Open <http://localhost:63001>. The demo administrator credentials are:

```text
Email:    demo@example.com
Password: demo
```

### Normal daily use

Start or rebuild the environment:

```bash
export COMPOSE_FILE="$PWD/docker/docker-compose.dev.yml"
docker compose up -d --build
```

Check readiness:

```bash
docker compose exec atom php docker/healthcheck.php ready
docker compose exec atom_worker php docker/healthcheck.php ready
```

Inspect commands and logs:

```bash
docker compose exec atom php symfony list
docker compose logs -f atom atom_worker nginx
```

Stop containers while retaining databases and caches:

```bash
docker compose down
```

Delete the whole local environment, including database and search volumes:

```bash
docker compose down --volumes
```

The last command destroys local data.

### Run PHP tests

```bash
docker compose exec atom composer test
```

The main suites can also be run separately:

```bash
docker compose exec atom composer test-application
docker compose exec atom composer test-framework
```

The active PHP tests live under `test/phpunit` and `test/framework`. The
non-executable Symfony 1 Lime harness and its dormant suites have been removed;
use PHPUnit for application and adapter regressions, and Playwright for real
HTTP and browser workflows.

Check PHP style without changing files:

```bash
docker compose exec atom composer php-cs-fixer -- fix --dry-run -v
```

### Run browser tests

Install host-side JavaScript dependencies and browsers once:

```bash
npm ci
npx playwright install chrome firefox
```

With the development environment running at port 63001:

```bash
npm run test:playwright:coverage
```

Useful narrower commands are:

```bash
npm run test:playwright:chrome
npm run test:playwright:firefox
npx playwright test --project=chrome --grep "permissions"
```

The coverage command verifies the declared authenticated navigation inventory,
not JavaScript line coverage. See `playwright/README.md` for the inventory and
test-state design.

Playwright output is written below `output/playwright/` and should not be
included in a production image.

### Build front-end assets

```bash
npm ci
npm run build
npm run check-format
```

The production image builds assets in its own build stage, so a host build is
primarily useful for development feedback.

## Operator and evaluator quick start

This section builds and tests the production artifact without publishing it.
It is suitable for a sysadmin evaluating the branch on a non-production host.

It is not a complete production deployment manifest. Production still needs
site-specific TLS, secrets, persistent storage, backup, monitoring, ingress,
and rollout configuration.

### Build the exact production image

```bash
revision="$(git rev-parse HEAD)"
docker build \
  --target production \
  --build-arg "VCS_REF=${revision}" \
  --tag atom:sf7-poc \
  .
```

Inspect the image contract:

```bash
docker/verify-image.sh atom:sf7-poc "${revision}"
```

This verifies both required content and important exclusions.

### Create isolated supporting services

Use a distinct Compose project so evaluation does not share state with a
developer environment:

```bash
export COMPOSE_PROJECT_NAME=atom-sf7-eval
docker compose -f docker/docker-compose.test.yml up -d \
  percona elasticsearch memcached gearmand
```

Wait for the production image to see all dependencies:

```bash
until docker run --rm \
  --network atom-sf7-eval_default \
  --env-file docker/etc/environment \
  --entrypoint php \
  atom:sf7-poc \
  docker/healthcheck.php ready
do
  sleep 2
done
```

Install a fresh demo database through the production image:

```bash
docker run --rm \
  --network atom-sf7-eval_default \
  --env-file docker/etc/environment \
  --env ATOM_DEVELOPMENT_MODE=off \
  --env NODE_ENV=production \
  atom:sf7-poc \
  php symfony tools:install \
  --database-host=percona \
  --database-port=3306 \
  --database-name=atom \
  --database-user=atom \
  --database-password=atom_12345 \
  --search-host=elasticsearch \
  --search-port=9200 \
  --search-index=atom \
  --demo \
  --no-confirmation
```

Again, the installer assumes the selected database can be initialized.

### Verify the running artifact

```bash
docker/verify-runtime.sh \
  atom:sf7-poc \
  atom-sf7-eval_default
```

The runtime verifier starts temporary web and worker containers from the exact
image, waits for role-specific health, checks non-root execution, issues a real
FastCGI request, confirms the worker registration, and rejects suspicious
runtime logs.

Clean up the isolated evaluation environment:

```bash
docker compose -f docker/docker-compose.test.yml down --volumes
```

This deletes the evaluation database and search index.

### Rehearse an upgrade

The branch provides two related tools:

- `docker/create-upgrade-fixture.sh` derives a historical fixture from a
  disposable installed database;
- `docker/rehearse-upgrade.sh` restores a backup into an isolated database,
  upgrades it, repeats the upgrade, and proves restoration against a data
  fingerprint.

For a development Compose environment:

```bash
docker/create-upgrade-fixture.sh /tmp/atom-v193.sql
docker/rehearse-upgrade.sh /tmp/atom-v193.sql atom:sf7-poc
```

The fixture output path must not already exist. A real production backup should
be copied to an evaluation host and rehearsed there; never point a rehearsal
at the live production database.

The exact upgrade path, installed plugins, data volume, and database options
for the intended deployment should be represented in the rehearsal.

## Optional host-PHP workflow

Running PHP directly on the host can shorten some edit/test loops. It is an
optional fast path, not the reference deployment.

The most useful hybrid arrangement is:

- PHP, Composer, Node, and tests on the host;
- Percona, Elasticsearch, and Gearman in containers;
- full Compose and production-image tests before considering a change done.

This arrangement is already close to the integration CI topology and avoids
maintaining a separate local MySQL installation.

### Why not install every service on the host

A local PHP process can make unit and focused integration tests faster. A local
MySQL server usually provides less benefit:

- the reference service is Percona 8.4, not an arbitrary host MySQL;
- Elasticsearch and Gearman are still required;
- Homebrew or operating-system upgrades can silently change versions;
- local ports and configuration become machine-specific;
- production-image behaviour still has to be tested afterward.

Containerizing stateful dependencies while running PHP on the host is normally
the better compromise.

### Host prerequisites

Use PHP 8.3 with the extensions required by AtoM and Composer dependencies.
The container image is the authoritative reference. Important extensions
include MySQL/PDO, `intl`, `mbstring`, XML/XSL, ZIP, sockets, PCNTL where
applicable, image support, and the extensions used by configured caches.

Also install:

- Composer 2;
- Node.js 22 and npm;
- the system libraries required by Composer packages and AtoM tooling.

If maintaining that toolchain is inconvenient, use the development container.

### Hybrid setup

Start the test dependencies:

```bash
docker compose -f docker/docker-compose.test.yml up -d \
  percona elasticsearch gearmand
```

Install source dependencies:

```bash
composer install
npm ci
npm run build
```

Initialize the host-facing development configuration and database:

```bash
php symfony tools:install \
  --database-host=127.0.0.1 \
  --database-port=63003 \
  --database-name=atom \
  --database-user=atom \
  --database-password=atom_12345 \
  --search-host=127.0.0.1 \
  --search-port=63002 \
  --search-index=atom \
  --demo \
  --no-confirmation
```

For browser or worker testing, make the generated
`apps/qubit/config/gearman.yml` point to the host-published endpoint:

```yaml
all:
  servers:
    default: 127.0.0.1:4730
```

Run focused tests directly:

```bash
composer test-application
composer test-framework
```

This can be considerably faster than entering a container for every test
process, especially when Composer and PHPUnit caches are warm.

For browser testing, use the tested Nginx/PHP-FPM topology. PHP's built-in web
server does not reproduce FastCGI parameters, rewrite rules, process users, or
Nginx behaviour and should not be used as production evidence.

### Avoid shared-state surprises

The installer writes local runtime configuration and initializes the named
database and search index. A developer switching between host and container
workflows should use intentional project/database/index names and should not
assume that two concurrently running environments are isolated.

Use separate Compose project names and credentials when concurrent
environments matter.

## Working on this branch

### Preferred change boundary

When a compatibility problem is found, ask these questions in order:

1. Is the runtime translating an existing convention incorrectly?
2. Can the correction live under `src/Framework`, the kernel, a front
   controller, or infrastructure?
3. Can a focused framework test reproduce it?
4. Does existing application code contain a genuine PHP 8.3 bug independent of
   the runtime?
5. Is this actually a desired application refactor that should be reviewed
   separately?

The default is to fix the adapter when the application is relying on an
established convention. Change an action, model, template, or plugin only when
the application code itself must change.

This is a review heuristic, not a ban. Some targeted application changes are
necessary, particularly where Symfony 1 exposed undocumented behaviour or old
PHP tolerated invalid code. Those changes should be small, explained, and
covered by a workflow test.

### New code

New infrastructure code should use namespaced classes, dependency injection,
and maintained Symfony components directly. Do not introduce new calls to a
bridge class merely because old application code uses one.

New application features have two reasonable shapes:

- use the existing AtoM module conventions when integration with a mature
  module makes that the smallest coherent change;
- use a native Symfony controller/service boundary when the feature is
  genuinely independent and can coexist cleanly with compiled AtoM routes.

The choice should reduce coupling rather than create a second hidden
application architecture.

### Generated code

Do not hand-edit generated Propel classes or other generated output. Adapt to
their current shape or run the appropriate generator when regeneration is the
intended change.

### Tests expected with changes

- Adapter changes: focused tests under the framework test suite.
- Application defect fixes: application regression tests.
- User-visible or permission-sensitive behaviour: Playwright coverage.
- Container changes: image and runtime verification.
- Persistence or installer changes: fresh install and upgrade rehearsal.
- PHP compatibility changes: clean runtime-log check.

### Commit history

Keep commits reviewable as a narrative. A sizeable commit is acceptable when
it represents one coherent compatibility boundary. Commit messages use an
imperative, capitalized subject of no more than 50 characters, no trailing
period, a blank line before the body, and body lines wrapped at 80 columns.

The branch should remain local unless publication is explicitly requested.

## Production considerations

### What exists

- a production container target;
- an immutable revision label;
- non-root FPM and worker roles;
- role-aware health checks;
- fresh-install automation;
- exact-image runtime verification;
- historical upgrade and restore rehearsal;
- public and authenticated browser coverage;
- CI gates for tests, formatting, image contents, and runtime logs.

### What a deployment still must provide

- a reviewed image registry and promotion process;
- TLS termination and reverse-proxy configuration;
- production secrets and secret rotation;
- persistent storage ownership and backup policy;
- database and digital-object backups;
- an Elasticsearch recovery plan;
- job retry, alerting, and queue monitoring;
- centralized logs and metrics;
- capacity and load testing using representative data;
- a maintenance/read-only procedure;
- a staged rollout and rollback runbook;
- testing of local plugins, themes, integrations, and authentication;
- acceptance by users responsible for critical workflows.

### Suggested promotion sequence

1. Build one image from a reviewed revision.
2. Verify that exact image with `docker/verify-image.sh`.
3. Restore a recent production backup into an isolated environment.
4. Rehearse upgrade, repeat, and restore.
5. Run browser tests plus site-specific acceptance tests.
6. Exercise workers, imports, exports, search indexing, and digital objects.
7. Load-test the representative environment.
8. Deploy the same image to staging.
9. Observe it for an agreed period with clean logs and healthy dependencies.
10. Back up production and record the restore procedure.
11. Deploy the same image digest to production.
12. Run readiness and high-value smoke tests.
13. Keep the previous image and pre-upgrade backup until the rollback window
    closes.

If the database upgrade is not backward-compatible, rolling the application
image back without restoring the database is not a valid rollback.

### Recommended production-readiness gate

Before calling a particular deployment ready, record evidence for:

- [ ] all PHP and Playwright suites pass at the release revision;
- [ ] the production image verifier passes;
- [ ] the exact production image passes web and worker runtime verification;
- [ ] a fresh installation succeeds;
- [ ] the target database upgrade succeeds in rehearsal;
- [ ] the upgrade can be repeated safely;
- [ ] the backup restores with the expected fingerprint;
- [ ] local plugins and themes pass their acceptance tests;
- [ ] role and permission tests cover local authorization policy;
- [ ] integrations and background jobs complete;
- [ ] representative load and data volume are acceptable;
- [ ] logs contain no unexplained warnings, deprecations, or errors;
- [ ] backup, restore, deployment, and rollback runbooks are reviewed;
- [ ] operators and application owners approve the release.

## Known limitations and open risks

### Propel 1 remains

Removing the Symfony 1 runtime does not modernize the persistence layer.
Propel 1 and its generated model shape remain significant technical debt.
They are isolated more clearly, but not eliminated.

### Compatibility adapters are transitional code

The bridge makes the migration tractable, but every adapter is another contract
to understand and test. It should not become an excuse to add new legacy-style
code. The long-term direction is to shrink its caller set.

### Third-party extensions vary

Plugins and themes may depend on undocumented Symfony 1 internals or old PHP
behaviour. Core test success cannot prove them compatible. A catalogue of
supported extensions and an extension-level test strategy are still needed.

### Browser inventory has a boundary

The authenticated inventory provides a strong guard against route gaps, but it
does not prove every field combination, locale, data state, or JavaScript
branch. High-value local workflows should be added explicitly.

### Scale evidence is deployment-specific

The branch proves functional flows and service integration. It does not yet
establish performance for every large archive, concurrent import volume, or
digital-object storage topology.

### Operational packaging is not a platform

The production image and verification scripts are strong building blocks.
They do not prescribe Kubernetes, Compose in production, a particular cloud,
or a backup product. Supported deployment patterns and their ownership remain
a project decision.

### Front-end build warnings remain

The current build may report Sass deprecations and bundle-size warnings. They
have not blocked runtime verification, but they are useful signals for a later
front-end modernization effort.

## Future directions

The value of this architecture is not only that AtoM runs on Symfony 7. It
creates a path for smaller, independently reviewable improvements.

### 1. Establish a supported compatibility matrix

- test representative production databases;
- catalogue maintained plugins and themes;
- record supported operating and container platforms;
- add site-specific integrations to automated acceptance tests;
- define performance baselines and target data volumes.

### 2. Make native Symfony the default for new infrastructure

- use constructor-injected services;
- define explicit interfaces at domain boundaries;
- avoid new global context lookups;
- use native Symfony events and console commands where appropriate;
- keep framework services out of Propel/domain objects.

### 3. Convert modules incrementally

A module can move from `LegacyController` to native Symfony controllers when:

- its routes can be owned explicitly;
- security behaviour has tests;
- form binding and validation are understood;
- templates have a deliberate rendering strategy;
- plugins do not depend on replacing the old module;
- the conversion removes more bridge surface than it adds.

Both controller shapes can coexist in the Symfony router during this process.
That is incremental application migration, not a dual framework runtime.

### 4. Shrink bridge aliases

Measure which aliases are used, migrate callers in bounded sets, deprecate
unused aliases, and delete their adapters with tests. Usage telemetry or static
analysis can turn this into an explicit burn-down rather than an open-ended
cleanup.

### 5. Clarify plugin contracts

- document which configuration and extension points are supported;
- expose stable namespaced interfaces;
- add a plugin compatibility test harness;
- provide deprecation diagnostics for bridge-only APIs;
- separate application plugins from framework-internal hooks.

This is likely one of the highest-impact steps before a broad release because
plugins are where local variability is greatest.

### 6. Modernize persistence by bounded context

Replacing all Propel models at once would recreate the risks avoided by this
branch. A safer path is to introduce explicit repositories or data services
around selected contexts, preserve tested behaviour, migrate their callers,
and retire generated classes only when no longer used.

Database schema modernization should be planned separately from repository
modernization so rollback and upgrade risks remain visible.

### 7. Modernize rendering and forms selectively

Twig, native Symfony forms, and modern front-end components are available
future options, not migration requirements. Adopt them where they improve a
specific module and where output, accessibility, translation, and plugin
behaviour can be compared.

### 8. Evolve background processing

Existing Gearman workflows are preserved. A later project could evaluate
Symfony Messenger or another maintained queue abstraction, but it should begin
with delivery, retry, idempotency, visibility, and operational requirements
rather than a framework preference.

### 9. Decouple service upgrades

Elasticsearch, Percona, PHP, front-end tooling, and container base images each
need their own compatibility evidence. The new runtime makes those projects
easier, but combining them all into one release would make diagnosis and
rollback harder.

### 10. Turn production evidence into release policy

- publish required CI gates;
- define supported images and architectures;
- formalize upgrade fixtures;
- keep a warning-free runtime policy;
- add vulnerability and dependency scanning;
- define an LTS patch and security update process for Symfony 7.4.

## Versioning and release naming

This architecture does not require the product to be called AtoM 3.x.

There are reasonable arguments for a major version:

- the internal framework changes completely;
- the minimum PHP and deployment requirements may change;
- unsupported plugins that depend on Symfony 1 internals may break;
- a major number can communicate a new support baseline.

There are also reasonable arguments for continuing AtoM 2.x:

- the user-facing application and information architecture are intentionally
  preserved;
- the database model and current service family remain substantially the same;
- routes, configuration, modules, templates, and workflows remain recognizable;
- the change can be understood as replacing an internal runtime dependency.

Semantic versioning is only useful relative to a declared public contract. The
project first needs to decide whether that contract includes undocumented
Symfony 1 internals used by plugins and deployments.

Release naming should therefore be a governance and support decision made
after compatibility policy and release criteria are agreed. The code does not
need a speculative 3.x branch today, and the architecture should not encode a
marketing decision.

## Frequently asked questions

### Is Symfony 1 still running?

No. Symfony 7 is the only web and console runtime on this branch. The
production image contains no Symfony 1 framework or plugin code. The patched
Propel 1 runtime is isolated under `vendor/propel1` because it remains AtoM's
persistence layer.

### Why do some classes still begin with `sf`?

Existing AtoM code refers to those names. The bridge aliases them to AtoM-owned
or maintained implementations so application call sites do not all have to
change at once. The name is a compatibility API, not the implementation.

### Is this a fork of AtoM?

It is an in-place development branch of the same application. The goal is to
replace the foundation without creating and indefinitely synchronizing a
second product.

### Can old and new runtimes be selected with an environment variable?

No. There is intentionally no runtime switch. Tests, development, and
production all exercise Symfony 7.

### Does minimal application change prevent modernization?

No. It separates modernization into reviewable steps. Once the runtime is
stable, modules, persistence, templates, plugins, and jobs can be modernized
independently with their own acceptance criteria.

### Will existing plugins work?

Plugins using documented AtoM conventions have a much better migration path
than they would in a rewrite. Compatibility is not automatic, especially for
plugins calling Symfony 1 internals or relying on old PHP behaviour. Test each
supported plugin.

### Does this require new database or search versions?

Not inherently. The reference branch intentionally retains Percona 8.4 and
Elasticsearch OSS 7.10.2 so framework and service changes are not conflated.

### Is Docker required for development?

No, but it is the recommended reference environment. Host PHP can speed up
focused loops if its version and extensions match. Final validation should use
the production image and reference services.

### Is the branch production-ready?

It has unusually strong proof-of-concept evidence, including production image,
browser, worker, install, upgrade, and restore testing. Production readiness
belongs to a specific release and deployment. It still requires representative
data, local extension testing, operational runbooks, performance evidence, and
project approval.

### Where should a compatibility fix go?

Prefer the runtime boundary under `src/Framework` when an existing AtoM
convention is being translated incorrectly. Change application code when it
contains a genuine defect or when a separately reviewed refactor is intended.

### What should we do next?

The highest-impact next steps are to validate representative deployments,
formalize plugin compatibility, establish a supported release matrix, and
begin removing bridge surface through bounded native Symfony migrations.

## Related repository material

This document is intended to be sufficient for a team review. These files are
the executable or more focused references behind it:

- [`docs/symfony-7-runtime.md`](../docs/symfony-7-runtime.md) — concise
  runtime policy;
- [`CONTRIBUTING.md`](../CONTRIBUTING.md) — repository-wide contributor
  commands;
- [`playwright/README.md`](../playwright/README.md) — browser coverage
  inventory and state model;
- [`Dockerfile`](../Dockerfile) — development and production image targets;
- [`docker/docker-compose.dev.yml`](../docker/docker-compose.dev.yml) — local
  development topology;
- [`docker/docker-compose.test.yml`](../docker/docker-compose.test.yml) —
  isolated state services;
- [`docker/verify-image.sh`](../docker/verify-image.sh) — production image
  contract;
- [`docker/verify-runtime.sh`](../docker/verify-runtime.sh) — exact-image
  web/worker verification;
- [`docker/healthcheck.php`](../docker/healthcheck.php) — liveness and
  readiness implementation;
- [`docker/create-upgrade-fixture.sh`](../docker/create-upgrade-fixture.sh) —
  historical fixture creation;
- [`docker/rehearse-upgrade.sh`](../docker/rehearse-upgrade.sh) — upgrade,
  repeat, and restore rehearsal;
- [`test/check-runtime-logs.sh`](../test/check-runtime-logs.sh) — runtime
  warning/error gate;
- [`.github/workflows/`](../.github/workflows/) — continuously executed
  reference procedures.

When documentation and executable checks disagree, treat the executable check
and current CI result as authoritative, then update this guide.
