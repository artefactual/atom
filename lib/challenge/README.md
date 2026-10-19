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
stale cached values. Invalid YAML still raises a configuration error. Deleting
`cache/challenge/` is safe; the next request rebuilds it.
