vcl 4.1;
import cookie;

backend default {
  .host = "nginx";
}

sub vcl_recv {
  # Do not cache culture change request
  if (req.url ~ "sf_culture") {
    return (pass);
  }

  # Do not cache login or logout requests
  if (req.url ~ "/user/login" || req.url ~ "/user/logout") {
    return (pass);
  }

  # Parse cookies so we can read their values
  cookie.parse(req.http.cookie);

  # Set HTTP header to culture cookie value
  set req.http.X-Atom-Culture = cookie.get("atom_culture");

  # If user was athenticated by AtoM then bypass cache
  if (cookie.get("atom_authenticated") ~ "1") {
    # Set fake header so vcl_backend_response can read
    # (see https://varnish-cache.org/docs/trunk/reference/vmod_cookie.html)
    set req.http.X-Atom-Authenticated = "1";

    return (pass);
  }

  # Do not cache clipboard POST request
  if (req.method == "POST" && req.url ~ "/clipboard/") {
    return (pass);
  }

  unset req.http.Cookie;
  return (hash);
}

sub vcl_hash {
  # Factor culture into hash
  hash_data(req.http.X-Atom-Culture);
}

sub vcl_backend_response {
  # Allow cookie setting in culture change request
  if (bereq.url ~ "sf_culture")  {
    return (deliver);
  }

  # Allow cookie setting in login and logout actions
  if (bereq.url ~ "/user/login" || bereq.url ~ "/user/logout")  {
    return (deliver);
  }

  # Allow cookie setting if authenticated so AtoM can, if the user logs out,
  # reset the "atom_authenticated" cookie
  if (bereq.http.X-Atom-Authenticated ~ "1") {
    return (deliver);
  }

  unset beresp.http.Set-Cookie;

  return (deliver);
}

sub vcl_deliver {
  if (obj.hits > 0) {
    set resp.http.X-Cache = "HIT";
    set resp.http.X-Cache-Hits = obj.hits;
  } else {
    set resp.http.X-Cache = "MISS";
  }
  return (deliver);
}
