# GeoIP from nginx

When nginx already looks up the client ASN and country, the challenge can use
those values instead of opening GeoIP databases in PHP.

In `config/appChallenge.yml`, the default is:

```yaml
geoip_from_nginx: auto
```

`auto` uses nginx when either `GEOIP_ASN` or `GEOIP_COUNTRY` FastCGI parameter
exists, including an empty value. If neither exists, it uses the local GeoIP
databases. Omitting the setting also selects `auto`.

Set it to `true` to always use nginx values, or `false` to always use local
databases and ignore nginx values.

In each nginx location that sends AtoM requests to PHP-FPM, add these parameters
alongside the existing `fastcgi_param` directives:

```nginx
fastcgi_param GEOIP_ASN     $geoip2_asn;
fastcgi_param GEOIP_COUNTRY $geoip2_country_code;
```

Use the variable names from your existing GeoIP2 configuration. For example,
the variables above can be defined in the `http` block as follows:

```nginx
geoip2 /etc/nginx/geoip/GeoLite2-ASN.mmdb {
    $geoip2_asn autonomous_system_number;
}
geoip2 /etc/nginx/geoip/GeoLite2-City.mmdb {
    $geoip2_country_code country iso_code;
}
```

These are FastCGI parameters, read by PHP as `$_SERVER['GEOIP_ASN']` and
`$_SERVER['GEOIP_COUNTRY']`. Ordinary client HTTP headers use `HTTP_` names and
are not read by the challenge. Set these parameters from nginx's lookup results,
never from `$http_*` variables, and restrict access to PHP-FPM to trusted web
servers. GeoIP must use the same client address as AtoM's `REMOTE_ADDR`; if nginx
is behind another proxy, configure its trusted real-IP handling first.

When nginx is selected, neither GeoIP database is opened by the challenge.
Missing, empty, or invalid values are treated as unknown and do not match
country/ASN exceptions. CIDR and network exceptions continue to work. No local
database fallback occurs, so a missing ASN parameter will challenge bots that
previously bypassed using an ASN exception. Local GeoIP database paths may be
omitted in this mode.

The ASN must be a decimal integer from 1 through 4294967295. Country values must
contain two ASCII letters; lowercase values are normalized to uppercase. Leave
nginx's missing-record results empty rather than supplying a default allowed
country or ASN.

Send both parameters on every request, including empty values for unknown IPs;
do not add `if_not_empty` to the `fastcgi_param` directives. This lets `auto`
distinguish an nginx lookup miss from an installation using local databases.
Validate nginx configuration with `nginx -t` before reloading it.

References: [GeoIP2 module](https://github.com/leev/ngx_http_geoip2_module),
[nginx FastCGI parameters](https://nginx.org/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_param).

## Parsed configuration cache

The request filter and `/challenge` share a standalone configuration loader.
It stores the parsed YAML as a PHP array in `cache/challenge/`, without loading
Symfony or connecting to AtoM's configured APCu/Memcache backend. PHP's OPcache
can cache the generated PHP file when enabled for PHP-FPM. The loader also works
without OPcache, though PHP then compiles the cached array on each request.

Each request reads and hashes the small YAML source to check freshness. Cache
hits skip YAML parsing and do not load the YAML parser. A content hash detects
same-size edits within the same second, rather than relying on timestamps.
Changing the YAML replaces the cache and invalidates its OPcache entry, even
when `opcache.validate_timestamps` is disabled.

The cache is written to a private temporary file and renamed atomically. It is
not rewritten on cache hits. Cache files have mode `0600` because they contain
the challenge salt. The PHP-FPM user needs write access to the application's
`cache/` directory; if the cache cannot be written, the loader parses YAML
normally. No additional configuration setting is required.

Removing or making the YAML unreadable disables the challenge rather than using
stale cached values. Invalid YAML still raises a configuration error. Deleting the generated
`cache/challenge/appChallenge-*.php` files is safe; the next request rebuilds
them. In ALTCHA mode, retain live entries in `cache/challenge/replay/` as
described below.

## Testing ALTCHA and BotD

The proof-of-work mode is opt-in. It runs before Symfony and reuses the GeoIP
source and configuration cache described above. The existing challenge remains
available as `verification: legacy` (also the default when omitted).

For a test deployment, generate a secret with `openssl rand -hex 32`, then set
these options in `config/appChallenge.yml`:

```yaml
activated: true
verification: altcha
salt: 'REPLACE_WITH_YOUR_GENERATED_SECRET'
botd_enabled: true
altcha_max_number: 100000
altcha_ttl_seconds: 120
delay_seconds: 5
clearance_ttl_seconds: 3600
```

ALTCHA settings and defaults:

| Setting | Default | Effect |
| --- | --- | --- |
| `verification` | `legacy` | Set `altcha` to select server proof verification. |
| `botd_enabled` | `true` | Run BotD before ALTCHA. |
| `botd_mode` | `enforce` | `enforce` blocks detections/errors; `observe` allows ALTCHA to continue. |
| `botd_allowed_kinds` | `[]` | Permit named BotD kinds to continue to ALTCHA. |
| `altcha_max_number` | `100000` | Solver search range, clamped to 1–1000000. |
| `altcha_ttl_seconds` | `120` | Proof submission lifetime, clamped to 30–600 seconds. |
| `delay_seconds` | `5` | Minimum proof age enforced by the server, clamped to 0–30 seconds. |
| `clearance_ttl_seconds` | `3600` | Fixed access lifetime after verification, clamped to 60–86400 seconds. |
| `clearance_bind_to_ip` | `true` | Set boolean `false` for mobile clearance across IP changes. |
| `decision_log_enabled` | `false` | Enable diagnostic JSON-lines logging for testing. |
| `decision_log_dir` | `%SF_ROOT_DIR%/log` | Directory for `appChallenge.decisions.log`. |

These options do not configure AtoM's Symfony session timeout. In ALTCHA mode,
`cookie_days`, `test_headless` and the legacy JS/headless cookie names do not
control access; `cookiename_visited` still selects the clearance cookie name.

Use HTTPS. Keep the secret on the server, and retain your current endpoint,
CIDR, ASN and country exceptions. Do not copy the example salt literally. ALTCHA
mode refuses empty, placeholder and shorter-than-32-character secrets. Changing
the salt invalidates outstanding proofs and clearance immediately.

The page shows a **Verify and continue** button. Clicking runs BotD and solves
an ALTCHA SHA-256 proof in the browser. The server verifies the proof before
issuing `atom_visited` (or `cookiename_visited`) with Secure, HttpOnly and
SameSite=Lax attributes. The signature covers the expiry and client binding.
The filter validates this cookie without accessing the replay cache or GeoIP.
Old visited/JS/headless cookies cannot grant access in ALTCHA mode. Existing
exception rules still grant access independently of the challenge.

### BotD exceptions and relaxed rules

These are AtoM policy settings around the open-source detector, not extra BotD
library options. For a diagnostic run that records detections while still
requiring a valid ALTCHA proof, use:

```yaml
botd_enabled: true
botd_mode: observe
decision_log_enabled: true
```

To enforce BotD while allowing a specific automation kind through that step:

```yaml
botd_mode: enforce
botd_allowed_kinds:
  - selenium
```

That example allows Selenium to attempt ALTCHA; it does not grant clearance or
bypass server verification. Leave the list empty for the default strict policy.
Supported kinds are `unknown`, `headless_chrome`, `phantomjs`, `nightmare`,
`selenium`, `electron`, `nodejs`, `rhino`, `couchjs`, `sequentum`, `slimerjs`, and
`cefsharp`. Unknown entries are ignored. An invalid mode falls back to `enforce`;
a malformed allow-list permits no kinds. Errors are allowed only in `observe`
mode, not by putting `error` in the kind list. `botd_enabled: false` disables
this step entirely while retaining ALTCHA.

The BotD report remains client-controlled even when the server enforces the
policy. This feature has no tunable detection score or confidence threshold.
Changing BotD policy does not revoke already-issued clearance; rotate the salt
if immediate revocation is needed. Existing ASN/CIDR/User-Agent exceptions
continue to bypass the complete challenge independently of these settings.

The client address is `$_SERVER['REMOTE_ADDR']`, exactly as in AtoM's existing
filter. In nginx-to-PHP-FPM deployments nginx supplies it as a FastCGI parameter.
Ensure it contains the actual visitor IP after trusted proxy/real-IP handling,
not the proxy address. No client-supplied forwarding headers are trusted here.
By default, proofs and clearance are bound to this address and the User-Agent.
For mobile visitors who change networks, add:

```yaml
clearance_bind_to_ip: false
```

Use the YAML boolean `false`, without quotes. This disables IP binding only for
clearance cookies. Proofs remain IP-bound during verification; clearance still
requires a valid server signature, expiry and matching User-Agent. An IP change
after verification, including IPv4 to IPv6, then does not trigger a challenge.
The default is `true`, preserving existing IP-bound cookies and behavior.
Changing this setting either way invalidates clearance from the previous policy
and requires one new verification. A copied mobile clearance cookie can be
reused from another IP with the same User-Agent until it expires.

Clearance lifetime is fixed from issuance, not renewed by activity. This option
does not prevent interruptions when clearance expires or the User-Agent changes.
The filter runs before Symfony authentication and does not preserve POST data
when challenging an expired form submission. Longer clearance reduces this risk
but also extends access for bots that pass; authenticated-session handling is
not implemented by this option.

Dependency versions and upgrade instructions, including CSP hash updates,
are in [the dependency README](../../js/challenge/README.md).

All assets are pinned and served locally under `/js/challenge/`;
BotD monitoring is disabled. The page uses a new CSP nonce on each response,
allows local module imports and blob workers, and permits the widget's static
stylesheet by its SHA-256 hash. Updating ALTCHA requires updating that hash in
`lib/challenge/dependencies.php` alongside the asset. No `unsafe-inline` or
`unsafe-eval` is enabled. Verification pages and JSON responses are not cached.

`/challenge/proof`, `/challenge/verify` and `/challenge/report` use the
existing `index.php` and `qubit_dev.php` front controllers. There are no direct
PHP asset endpoints to expose and no new nginx locations to configure. As with
the existing challenge, `/challenge` must reach AtoM's front controller. Unknown
paths such as `/challenge-extra` do not bypass the filter.

Proofs expire in 120 seconds by default (configurable 30–600 seconds), and the
server enforces `delay_seconds` independently of the browser (clamped 0–30).
The signed proof carries the difficulty and client binding; generation is
stateless and does not disclose the solution. `altcha_max_number` is clamped to
1–1000000; start with the default 100000 and measure on ordinary/mobile devices
before increasing it. Clearance defaults to one hour (configurable 60–86400
seconds), independently of legacy `cookie_days`.

Single-use proof claims are created atomically in `cache/challenge/replay/`
only after successful verification. The PHP-FPM user must be able to create
these private directories. Storage failure returns 503 and grants no clearance.
All workers serving this installation must share that directory for replay
protection. Retain live claims: deleting them permits replay until proof expiry.
Periodically remove expired claims, for example using an existing maintenance
job from the AtoM root (15 minutes exceeds the maximum 10-minute proof lifetime):

```sh
find cache/challenge/replay -mindepth 1 -maxdepth 1 -type d -mmin +15 -empty -delete
```

BotD is a client-supplied signal: a custom HTTP client can report `not_bot` and
still solve a valid proof. Neither it nor a button proves a human is present.
Use `botd_enabled: false` to compare proof-of-work alone or investigate false
positives. In the default `enforce` mode, a BotD load/detection error blocks
clearance and displays a retry option. In `observe` mode it records the error
when logging is enabled and allows the ALTCHA step to continue. There is no automatic fallback to the legacy challenge.

Suggested comparison:

1. Record current request rate, CPU, expensive browse/image requests reaching
   AtoM, and legitimate visitor failures over a fixed interval.
2. Enable ALTCHA mode with the options above. Existing cookies require fresh
   verification. Confirm a normal browser returns to its original local URL
   and receives the HttpOnly cookie only after `/challenge/verify` succeeds.
3. Confirm no-cookie requests remain blocked and permitted crawler exceptions
   still work. Correlate CPU with application request rate, not just challenge
   completion count. Bots can solve the challenge and reuse clearance until
   expiry; this feature does not limit requests after clearance.
4. Compare BotD on/off under similar traffic. Set `verification: legacy` to
   roll back; ALTCHA cookies will then be rejected by the legacy filter.

The test suite exercises proof tampering, expiry, replay, client binding,
secret rotation, server-enforced delay, cookie flags, forged forwarding headers,
and the real standalone HTTP endpoints. Browser checks should additionally
cover local asset/CSP loading, retry behavior and same-origin return navigation.

Upstream references: [ALTCHA widget 2.3.0](https://github.com/altcha-org/altcha/tree/v2.3.0),
[BotD 2.0.0](https://github.com/fingerprintjs/BotD/tree/v2.0.0).

## Optional decision logging

For a short diagnostic run, enable:

```yaml
decision_log_enabled: true
decision_log_dir: '%SF_ROOT_DIR%/log'
```

The PHP-FPM user must be able to write to that directory. One private file,
`appChallenge.decisions.log`, contains JSON lines with UTC timestamp, decision,
reason, stage, source, optional BotD kind, client IP, HTTP method, URL path,
User-Agent, and existing nginx ASN and country values. It makes no extra GeoIP lookups. When local GeoIP is selected,
ASN/country fields are empty rather than performing lookups solely for logging.
Cookies, authorization headers, proof payloads, secrets and query strings are
never recorded. Client strings are bounded and JSON-escaped.

`stage` identifies `clearance`, `exception`, `altcha`, `botd`, `legacy`,
`request`, or `system`. `source` is `server` for a server decision and `browser`
for diagnostic self-reports. `bot_kind` contains a recognized BotD kind when
supplied, otherwise null; that classification is always client-controlled,
even when recorded alongside a server verification decision. Missing nginx ASN
or country values are empty strings, not lookup errors.

| Decision / reason | Meaning |
| --- | --- |
| `challenged` / `clearance_required` | Filter stopped a request without valid clearance; outcome still unknown. |
| `passed` / `altcha_verified` | Server verified a fresh proof and issued clearance. |
| `visited` / `valid_clearance` | Request reused valid clearance. |
| `skipped` / `configured_exception` | Existing CIDR/ASN/country/User-Agent exception allowed access. |
| `skipped` / `endpoint_exception` | Configured path exception allowed access. |
| `rejected` / `invalid_proof` | Malformed proof, invalid signature or wrong solution. |
| `rejected` / `expired_proof`, `replayed_proof`, `proof_too_early` | Server rejected the proof's lifetime, reuse or timing. |
| `rejected` / `client_binding_mismatch` | Proof was submitted with a different IP/User-Agent. |
| `rejected` / `botd_rejected` | Submitted BotD report did not satisfy server policy. |
| `client_reported` / `botd_detected` | Browser reports a BotD detection, possibly permitted by policy. |
| `client_reported` / `botd_error` | Browser reports a BotD loading/detection error. |
| `client_reported` / `altcha_error`, `verification_error` | Browser reports a widget or verification-request failure. |
| `error` / `verification_unavailable` | Misconfiguration or replay-storage failure prevented verification. |

Browser reports are sent only when diagnostic logging is enabled, at most once
per page. `/challenge/report` accepts only a small whitelist of reasons and
known bot kinds; it never issues cookies or grants clearance. Reports can be
forged or omitted and do not prove a bot was blocked. A client that never runs
JavaScript, never clicks the button or cannot send its report may only appear
as `challenged`; that alone cannot distinguish BotD from ALTCHA. In `observe`
mode, `botd_detected` can be followed by `passed` after successful ALTCHA.
Exception events do not identify the individual matched rule.

### Query examples with jq

Run from the application's `log/` directory. Results identify client IPs and
User-Agents, not named AtoM users. For example, a live view of successful access:

```sh
tail -F appChallenge.decisions.log |
  jq --unbuffered -c 'select(
    .decision == "passed" or .decision == "visited" or .decision == "skipped"
  ) | {timestamp, decision, reason, ip, asn, country, path}'
```

Newly passed proofs, existing clearance, and exception bypasses separately:

```sh
jq -c 'select(.decision == "passed") |
  {timestamp, ip, reason, bot_kind, asn, country, user_agent}' appChallenge.decisions.log

jq -c 'select(.decision == "visited") |
  {timestamp, ip, asn, country, path}' appChallenge.decisions.log

jq -c 'select(.decision == "skipped") |
  {timestamp, ip, reason, asn, country, user_agent}' appChallenge.decisions.log
```

Server-rejected ALTCHA proofs versus BotD events (retain `source` to distinguish
server policy decisions from untrusted browser reports):

```sh
jq -c 'select(.decision == "rejected" and .stage == "altcha") |
  {timestamp, ip, source, reason, asn, country}' appChallenge.decisions.log

jq -c 'select(.stage == "botd") |
  {timestamp, ip, decision, source, reason, bot_kind, asn, country}' appChallenge.decisions.log
```

All challenges, rejections, reported failures and server errors:

```sh
jq -c 'select(
  .decision == "challenged" or .decision == "rejected" or
  .decision == "client_reported" or .decision == "error"
) | {timestamp, ip, decision, stage, source, reason, asn, country}' appChallenge.decisions.log
```

A client's full decision timeline and requests from one ASN/country:

```sh
jq -c --arg ip '73.96.89.76' 'select(.ip == $ip) |
  {timestamp, decision, stage, source, reason, bot_kind, path}' appChallenge.decisions.log

jq -c 'select(.asn == "22773" and .country == "US") |
  {timestamp, ip, decision, stage, reason, path}' appChallenge.decisions.log
```

Summary counts by decision/stage/reason and the most frequently challenged
ASNs. Empty ASN values are shown as unknown:

```sh
jq -r '[.decision, (.stage // "unknown"), .reason] | @tsv' appChallenge.decisions.log |
  sort | uniq -c | sort -nr

jq -r 'select(.decision == "challenged") |
  if .asn == "" then "unknown" else .asn end' appChallenge.decisions.log |
  sort | uniq -c | sort -nr | head -20
```

These are counts of recorded events, not unique bots or exact traffic totals.
A challenge or browser report can be followed by a successful verification;
correlate the IP, User-Agent and timestamps before interpreting outcomes.
Older log entries from before these changes lack `stage`, `source`, and
`bot_kind`; filters using those fields only match newer entries.

Disable logging after collecting evidence, and use normal log rotation and
retention for this file. It can grow quickly when recording every request.
Logging uses a nonblocking file lock, so records can be dropped on contention
or storage failure; it is diagnostic output, not an exact request counter or
audit trail. It never falls back to nginx/PHP error logging on write failure
and never changes a challenge decision. With logging disabled, it accesses no
log files or directories. No nginx configuration changes are required.

## Cherry-picking onto older branches

This change was developed on the QA base, where
`QubitUserChallenge::matchesEndpointException()` already exists. When applying
the commits to an older base such as AtoM 2.10.2, include upstream commit
`ca895bc479c4ac6b46ea17c8812d561665d59f4b` ("Enhance JS Challenge endpoint
exception matching") first. Otherwise the filter can fail with an undefined
method error. If the method was added manually during deployment, reconcile
that edit before applying the upstream commit to avoid duplicating it.

Then apply the performance/GeoIP commit, ALTCHA/BotD/logging commit and optional
clearance-IP-binding commit in order. Test the resulting branch as a whole;
passing tests on QA does not establish compatibility with every older base.
