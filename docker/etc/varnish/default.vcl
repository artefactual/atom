vcl 4.1;

backend default {
  .host = "nginx";
}

sub vcl_recv {
  # Do not cache clipboard POST request
  if (req.method == "POST" && req.url ~ "/clipboard/") {
    return (pass);
  }
  unset req.http.Cookie;
  return (hash);
}

sub vcl_backend_response {
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
