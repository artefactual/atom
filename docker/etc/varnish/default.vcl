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

  # Set HTTP header to culture cookie value
  cookie.parse(req.http.cookie);
  set req.http.X-Atom-Culture = cookie.get("atom_culture");

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
  if (bereq.url !~ "sf_culture")  {
    unset beresp.http.Set-Cookie;
  }

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
