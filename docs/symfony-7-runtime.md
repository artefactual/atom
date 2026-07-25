# Symfony 7 runtime architecture

AtoM evolves in place on Symfony 7.4 LTS. This is not a second runtime, a
separate product line, or a compatibility mode selected by an environment
variable. The Symfony 7 kernel is the only application runtime.

## Boundary

Symfony owns HTTP handling, routing, configuration loading, the service
container, console dispatch, and maintained framework components. Existing
AtoM actions, components, templates, forms, plugins, and Propel models remain
the application layer.

The request path is:

```text
front controller
  -> Symfony kernel and router
  -> AtoM action dispatcher
  -> existing action/component
  -> existing template and Propel model
```

Code under `src/Framework` adapts the existing AtoM conventions to maintained
Symfony components. Legacy `sf*` names may remain at application call sites
when an adapter preserves a useful convention, but those names must resolve to
AtoM bridge classes or maintained dependencies. They must never load the
Symfony 1 runtime.

Propel 1 remains the ORM for the generated model layer. The production image
retains only that runtime from the old `sfPropelPlugin` tree and removes the
rest of Symfony 1.

## Change policy

Keep migrations narrow:

- Prefer changes in `src/Framework`, front controllers, runtime configuration,
  and infrastructure.
- Keep actions, components, templates, forms, and models unchanged unless a
  test demonstrates an application bug that cannot be fixed at the boundary.
- Preserve YAML configuration, route, module, plugin, and helper conventions
  where they are still useful.
- Add an adapter only for an actively used surface and cover both the adapter
  and a real legacy consumer.
- Do not add a dual-runtime switch or restore a Symfony 1 dependency.
- Do not edit generated Propel code.

## Verification

Run both PHP suites:

```sh
docker compose exec atom composer test
```

Run authenticated browser coverage in both supported browsers:

```sh
npm run test:playwright:coverage
```

The production image gate additionally proves that Symfony 1 core files and
development dependencies are absent, then loads every retained project
declaration and checks every referenced `sf*` class against the native runtime:

```sh
docker/verify-image.sh IMAGE REVISION
```

The container-image workflow also rehearses a fresh install, the production
runtime, and an upgrade from the maintained historical fixture.
