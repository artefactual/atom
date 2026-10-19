# Pinned challenge dependencies

Served locally; no CDN requests at runtime. ALTCHA uses the v1 SHA-256
protocol supported by widget 2.3.0. Review upstream changes and update the
version, license and hashes together. BotD monitoring is disabled.
Keep bundles byte-for-byte: ALTCHA contains significant template-literal
whitespace, excluded from Git whitespace checks by `.gitattributes`.

- `altcha-2.3.0.js`: https://cdn.jsdelivr.net/gh/altcha-org/altcha@2.3.0/dist/altcha.js
  SHA-256: `7baf50cdd57bd393582341ccb47bd82bc08a47cef8713d42362f1737a3bf2e66`
- `botd-2.0.0.js`: https://cdn.jsdelivr.net/npm/@fingerprintjs/botd@2.0.0/dist/botd.esm.js
  SHA-256: `f438ed251dc7414ece9d4a2b6941441ad9ffae1a1905817f5f0c7366e701dd86`
- `ALTCHA-LICENSE.txt`: https://raw.githubusercontent.com/altcha-org/altcha/v2.3.0/LICENSE.txt
  SHA-256: `74aa56cabe930086caec41910278796acea0f4b0582af3a83ff3d3271674c702`
- `BOTD-LICENSE.txt`: https://raw.githubusercontent.com/fingerprintjs/BotD/v2.0.0/LICENSE
  SHA-256: `dda20c4ba0799b22561763c4263f7584b1d366887b05a547afc6935fd467ce13`

## One manifest for code updates

[`lib/challenge/dependencies.php`](../../lib/challenge/dependencies.php) is the
single place to change active versions, local module paths, bundle SHA-256
checksums, and the ALTCHA stylesheet CSP hash. The challenge page reads its
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

Checked against upstream releases on 2026-10-19:

| Dependency | Bundled | Latest upstream stable |
| --- | --- | --- |
| ALTCHA widget | 2.3.0 | [3.4.1](https://github.com/altcha-org/altcha/releases/tag/v3.4.1) |
| BotD | 2.0.0 | [2.0.0](https://github.com/fingerprintjs/BotD/releases/tag/v2.0.0) |

These are a dated snapshot, not a promise that the bundled versions stay latest.
Check the [ALTCHA releases](https://github.com/altcha-org/altcha/releases) and
[BotD releases](https://github.com/fingerprintjs/BotD/releases) before updating.
ALTCHA 3 is a major release: review its migration notes and v1 challenge support
before replacing the 2.3.0 bundle. This integration generates and verifies the
v1 SHA-256 payload; it does not implement ALTCHA's newer proof protocol.
Neither dependency updates itself or downloads code at runtime.

## Upgrade ALTCHA

1. Work on a development branch and retain the previous version for rollback.
   Review the target release notes, browser requirements, license and protocol
   compatibility. In particular check `challengeurl`, `customfetch`, `workers`,
   `verify()`, `statechange` and `verified` events, and the base64 payload fields
   `algorithm`, `challenge`, `number`, `salt`, and `signature` expected by
   `QubitChallengeProof::redeem()`. A protocol change requires server changes
   and regression tests; copying a new JavaScript file alone is insufficient.
2. Download the pinned ES-module build and its license. For the existing 2.3.0
   packaging, the source URL pattern is:

   ```sh
   version=2.3.0
   curl --fail --location \
     "https://cdn.jsdelivr.net/gh/altcha-org/altcha@${version}/dist/altcha.js" \
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
   2.3.0 build the widget creates a style element named `__altcha-css`. On an
   isolated test page, compute the hash of its exact runtime text in DevTools:

   ```js
   const css = document.getElementById('__altcha-css').textContent;
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
  Deploy the dependency manifest, bundles and licenses in the same release.
  Reload PHP-FPM when OPcache deployment settings require it.
- Roll back the whole release if necessary, including the manifest, bundles and CSP
  hash. Do not leave an old widget with a new protocol validator or vice versa.

No runtime YAML asset override or automatic updater is provided: upgrades are
reviewed code changes. Keeping the dependencies in one directory, their active
versions and CSP hash in one manifest, and this inventory together makes the
update surface explicit without letting YAML configuration load arbitrary
scripts.
