# Symfony 7 runtime follow-up

This document records important improvements that are intentionally deferred
from the compatibility-focused Symfony 7 runtime work. They should be delivered
as independently reviewable changes with focused regression coverage.

## Move the web root to a public directory

Priority: high.

The supplied Nginx configuration now serves only declared static assets
directly and routes all other requests through the front controller. This is an
important immediate boundary, but the repository root remains the configured
document root.

A future change should:

- create a dedicated `public/` directory;
- place the production front controller and public static assets there;
- move generated frontend bundles into that public tree;
- update `sf_web_dir`, upload paths, and static-file helpers coherently;
- preserve protected upload delivery through the application;
- configure Nginx and other supported web servers to use only `public/`;
- make the production image copy an explicit runtime and public-file allowlist;
- retain negative HTTP coverage for project metadata and private source files.

The migration is complete when adding a file outside `public/` cannot make that
file reachable over HTTP without an explicit deployment rule.

## Move remaining PHP libraries to Composer

The remaining manually vendored libraries are active and must not be deleted
without replacing their compatibility contracts.

- Install `brianlmoon/net_gearman` with Composer. Move AtoM's job-prefix
  handling out of the vendored `Job.php` and into an application adapter. Cover
  prefixed dispatch and a real worker round trip.
- Install `fluentdom/fluentdom` with Composer. Add focused EAC import and export
  fixtures before changing the currently used global query API.
- Install `easyrdf/easyrdf` with Composer. Port `sfSkosPlugin` from legacy
  underscore classes to namespaced classes and cover a real SKOS import.
- Replace `FreeBeerIso639Map.php` with an AtoM language-code service backed by
  Symfony Intl and explicit ISO 639-2 bibliographic-code exceptions. Cover both
  import and export mappings.
- Keep the isolated Propel 1 runtime until a separate model-layer migration can
  replace its generated classes and query contracts.

Each migration should remove its corresponding legacy autoload entry and add an
image assertion preventing the manual copy from returning.

## Consolidate frontend dependencies

The Bootstrap 5 bundle and the public `vendor/` tree still contain overlapping
libraries and legacy globals.

- Suppress route-level jsTree, MediaElement, and YUI files when the same module
  is already present in the Bootstrap 5 bundle.
- Remove the standalone jsTree and MediaElement copies after confirming that no
  supported non-Bootstrap theme consumes them.
- Replace URI.js with the platform `URL` APIs.
- Replace the `attrchange` plugin with `MutationObserver`.
- Remove the Modernizr compatibility file once the input shim is the only
  supported behaviour.
- Replace remaining YUI dialogs, buttons, data sources, and autocomplete
  widgets incrementally with maintained components.
- Remove the legacy ImageFlow assets after carousel and digital-object coverage
  confirms that only the Bootstrap 5 implementation remains active.

These changes should preserve the current `view.yml` conventions at the bridge
boundary until individual modules are deliberately refactored.
