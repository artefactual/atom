# Pinned challenge dependencies

Browser assets are served locally, with no CDN requests at runtime. Widget
3.5.0 uses the v2 PBKDF2/SHA-256 protocol with the official PHP library
`altcha-org/altcha` 2.3.0, installed through Composer. Review upstream changes
and update versions, licenses and hashes together. BotD monitoring and the
widget's optional Human Interaction Signature collector are disabled.
Keep bundles byte-for-byte: ALTCHA contains significant template-literal
whitespace, excluded from Git whitespace checks by `.gitattributes`.

- `altcha-3.5.0.js`: https://cdn.jsdelivr.net/gh/altcha-org/altcha@v3.5.0/dist/main/altcha.js
  SHA-256: `240732e1690e9fed906f298bb56ceccdc3cadfac65176e84b6bd91a04b7cee2a`
- `botd-2.0.0.js`: https://cdn.jsdelivr.net/npm/@fingerprintjs/botd@2.0.0/dist/botd.esm.js
  SHA-256: `f438ed251dc7414ece9d4a2b6941441ad9ffae1a1905817f5f0c7366e701dd86`
- `ALTCHA-LICENSE.txt`: https://raw.githubusercontent.com/altcha-org/altcha/v3.5.0/LICENSE.txt
  SHA-256: `bee1fb9d9c97d42c12c22332c5250f2fb1ef5c2321f6f82beb23e3b77be50a29`
- `BOTD-LICENSE.txt`: https://raw.githubusercontent.com/fingerprintjs/BotD/v2.0.0/LICENSE
  SHA-256: `dda20c4ba0799b22561763c4263f7584b1d366887b05a547afc6935fd467ce13`

## One manifest for code updates

[`lib/challenge/dependencies.php`](../../lib/challenge/dependencies.php) is the
single place to change active browser versions, local module paths, bundle
SHA-256 checksums, and the ALTCHA stylesheet CSP hash. The challenge page reads its
imports and CSP from that manifest; do not edit JavaScript imports or the CSP
header in the page just to bump a version. Bundle checksums are verified by
unit tests, not reread on each request.

The routine upgrade is:

1. Choose and review a pinned upstream release; download its module and license
   using the commands below.
2. Edit the relevant entry in `lib/challenge/dependencies.php`: `version`,
   `asset`, `sha256`, and, for ALTCHA, `style_hash`.
3. Update this README's source/hash inventory and the dated version table.
4. Run the PHP and browser checks below, then deploy the manifest and assets
   together. Roll back that set together if necessary.

No npm install or application asset build is needed for these standalone
bundles. Major releases may require integration changes in addition to this
procedure; compatibility checks must not be skipped.

## Version status

Checked against upstream releases on 2026-11-05:

| Dependency | Bundled | Latest upstream stable |
| --- | --- | --- |
| ALTCHA widget | 3.5.0 | [3.5.0](https://github.com/altcha-org/altcha/releases/tag/v3.5.0) |
| ALTCHA PHP library | 2.3.0 | [2.3.0](https://github.com/altcha-org/altcha-lib-php/releases/tag/v2.3.0) |
| BotD | 2.0.0 | [2.0.0](https://github.com/fingerprintjs/BotD/releases/tag/v2.0.0) |

These are a dated snapshot, not a promise that the bundled versions stay latest.
Check the [ALTCHA releases](https://github.com/altcha-org/altcha/releases) and
[BotD releases](https://github.com/fingerprintjs/BotD/releases) before updating.
Widget and PHP library version numbers are independent. This integration
accepts only v2 PBKDF2/SHA-256 proofs with a signed derived key, not v1 proofs.
Install the locked production PHP dependency with `composer install --no-dev`
before enabling ALTCHA. Neither browser dependency updates itself or downloads
code at runtime. Composer runs during deployment, not per request.

## Upgrade ALTCHA

1. Work on a development branch and retain the previous version for rollback.
   Review the target release notes, browser requirements, license and protocol
   compatibility. In particular check `challenge`, `configure({fetch})`,
   `workers`, `load`, `verify()`, `statechange` and `verified` events, and the
   base64 payload containing `challenge.parameters`, `challenge.signature`,
   `solution.counter`, and `solution.derivedKey` expected by
   `QubitChallengeProof::redeem()`. A protocol change requires server changes
   and regression tests; copying a new JavaScript file alone is insufficient.
2. Download the pinned ES-module build and its license. For the pinned v3
   packaging, the source URL pattern is:

   ```sh
   version=3.5.0
   curl --fail --location \
     "https://cdn.jsdelivr.net/gh/altcha-org/altcha@v${version}/dist/main/altcha.js" \
     --output "js/challenge/altcha-${version}.js"
   curl --fail --location \
     "https://raw.githubusercontent.com/altcha-org/altcha/v${version}/LICENSE.txt" \
     --output js/challenge/ALTCHA-LICENSE.txt
   ```

   Set `version` to the reviewed release and confirm its distribution filenames
   first: a major release may use different build, worker or license paths.
   Use a standalone module build; if it imports other files, vendor those too.
   Preserve the downloaded bytes and update `.gitattributes` for the new pinned
   filename if it contains significant template-literal whitespace.
3. Change the ALTCHA `version` and `asset` in `lib/challenge/dependencies.php`.
   Keep the version in the filename so browsers cannot reuse the previous build
   under the same cache key. Update the bundled version and source URLs above.
4. Update ALTCHA's `style_hash` in `lib/challenge/dependencies.php`. In the tested
   3.5.0 build the widget creates a style element named `altcha-css`. On an
   isolated test page, compute the hash of its exact runtime text in DevTools:

   ```js
   const css = document.getElementById('altcha-css').textContent;
   const digest = await crypto.subtle.digest(
     'SHA-256', new TextEncoder().encode(css)
   );
   console.log("'sha256-" + btoa(String.fromCharCode(...new Uint8Array(digest))) + "'");
   ```

   Paste that value without its surrounding quotes into `style_hash` in the
   manifest. The page adds CSP quoting and retains the per-response nonce. If the
   new build changes style injection, inspect upstream and adapt this step.
   Keep `script-src` restricted to self and the nonce, and keep workers local.
   Do not fix CSP errors by enabling `unsafe-inline`, `unsafe-eval`, or CDNs.
5. Compute `sha256sum` for the new bundle and license. Set the bundle's `sha256`
   in the manifest, and update the inventory at the top of this README. Review
   the diff and run the validation below.

## Upgrade the PHP proof library

`composer.json` and `composer.lock` pin `altcha-org/altcha` to 2.3.0. Review the
upstream PHP changelog and migration notes before changing that dependency;
its version is unrelated to the widget's version. Run the proof and HTTP tests,
including malformed payloads, signed metadata, expiry, binding and replay.
Keep the mandatory `keySignature` check: missing or invalid signatures must
never fall back to PBKDF2 derivation while verifying untrusted submissions.
The minimum PHP version for this dependency is 8.1. Production deployments
need the Composer package as well as the browser assets.

## Upgrade BotD

The upstream `load` option `monitoring` is kept false. `botd_mode` and
`botd_allowed_kinds` are AtoM policies applied to the detector result; they do
not alter upstream detection tests. See the settings README for their behavior.
If a new BotD version adds kinds, update the bounded kind lists in
`QubitChallengeProof` and `QubitChallengeDecisionLog`, and their policy/log tests,
as well as reviewing module compatibility. Raw component/fingerprint data is
not sent to the server or written to logs.


1. Review [upstream release notes](https://github.com/fingerprintjs/BotD/releases)
   and supported browsers. Confirm it still exports `load`, accepts
   `{monitoring: false}`, and that `detect()` returns a result with boolean
   `bot` and optional `botKind`. The integration awaits its result. Version 2.0.0 targets ES2018; test the browsers used by your visitors.
2. Download the pinned standalone ES module and license:

   ```sh
   version=2.0.0
   curl --fail --location \
     "https://cdn.jsdelivr.net/npm/@fingerprintjs/botd@${version}/dist/botd.esm.js" \
     --output "js/challenge/botd-${version}.js"
   curl --fail --location \
     "https://raw.githubusercontent.com/fingerprintjs/BotD/v${version}/LICENSE" \
     --output js/challenge/BOTD-LICENSE.txt
   ```

   Set `version` to the reviewed release and verify its published paths.
3. Update BotD's `version` and `asset` in `lib/challenge/dependencies.php`.
   Retain `load({monitoring: false})` in the challenge page. Never send signing secrets to BotD or trust its
   result as a replacement for server proof verification. The browser can
   falsify a `not_bot` report.
4. Update the bundle's `sha256` in the manifest and the version, URLs and
   SHA-256 inventory above. BotD 2.0.0
   injects no stylesheet; if a future release adds workers, styles or remote
   dependencies, review CSP and privacy implications before enabling them.

## Validate and deploy either update

The standalone challenge suite needs Composer dev dependencies but no AtoM
SQL database, search index or Symfony configuration:

```sh
php vendor/composer/bin/phpunit -c test/phpunit/challenge.xml
```


- Run the challenge PHPUnit tests, including proof, filter, HTTP, configuration
  and logging tests, plus PHP CS Fixer and `git diff --check`. PHP tests alone
  cannot establish that a new widget's JavaScript API or solver is compatible.
- In an isolated browser test, check that the button loads, proof solving and
  `/challenge/verify` succeed, clearance has Secure/HttpOnly/SameSite attributes,
  and the browser returns only to its saved local URL. Retest with BotD on/off.
- Test a normal desktop browser and iPhone/Android. Exercise automated-browser
  rejection, load/network failures, expired and replayed proofs, retry, and
  configured exception bypasses. Check for CSP errors, missing assets, worker
  failures, and unexpected third-party requests in DevTools.
- Confirm nginx serves the versioned `/js/challenge/*.js` assets with a
  JavaScript MIME type and the challenge endpoints reach the front controller.
  Deploy the dependency manifest, bundles, licenses, Composer lock file and
  installed production dependencies in the same release.
  Reload PHP-FPM when OPcache deployment settings require it.
- Roll back the whole release if necessary, including the manifest, bundles and CSP
  hash. Do not leave an old widget with a new protocol validator or vice versa.

No runtime YAML asset override or automatic updater is provided: upgrades are
reviewed code changes. Keeping the dependencies in one directory, their active
versions and CSP hash in one manifest, and this inventory together makes the
update surface explicit without letting YAML configuration load arbitrary
scripts.
