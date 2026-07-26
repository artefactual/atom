# Propel 1

This directory contains the exact AtoM-patched Propel 1.4.2 code that was
previously embedded in `sfPropelPlugin`.

- `runtime` is required by the application and production image.
- `generator` is retained as development-only build tooling for the existing
  model schema.

This is intentionally isolated from both Symfony and Composer dependencies.
Replacing it with a current Propel package would be a separate persistence
migration because generated AtoM models depend on this version and its local
patches.

The Propel license is in `LICENSE`. The generator's historical Phing license
notice is in `LICENSE.phing`.
