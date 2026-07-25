# Authenticated UI coverage

The Playwright suite measures coverage of the authenticated administrator
surface, rather than PHP or JavaScript line coverage. Its denominator is the
route and workflow inventory in `authenticated-surface.js`.

The navigation inventory test compares the Add, Manage, Import, Admin, and
Settings links visible at runtime with the route manifest. This prevents a new
administrator destination from silently falling outside the metric.

Run the suite and enforce the default 100% minimum:

```sh
npm run test:playwright:coverage
```

The default suite runs in Chrome and Firefox. To run a single browser:

```sh
npm run test:playwright:chrome
npm run test:playwright:firefox
```

Set `PLAYWRIGHT_COVERAGE_MINIMUM` to require a different threshold. The
machine-readable result is written to `output/playwright/coverage.json`.

The suite uses `PLAYWRIGHT_BASE_URL` when set and otherwise tests
`http://localhost:63001`. Administrator credentials match the local demo
fixture.
