OIDC Integration Test Notes

Overview
- Identity Provider: Keycloak 26.3 (Docker), two realms: demo and secondary.
- AtoM uses the arOidcPlugin with provider selection and auto user creation enabled.
- Redirect base: http://localhost; Keycloak is accessed by AtoM at http://127.0.0.1:8080.

Keycloak Realms, Clients, Users
- Realm demo
  - Client: atom
  - Users:
    - demo / demo (role: atom-editor)
    - admin / admin (role: atom-admin)
- Realm secondary
  - Client: atom-secondary
  - Users:
    - supportdefault / support (role: atom-editor-secondary)
    - supportadmin / support (role: atom-admin-secondary)
- Clients allow redirects from:
  - http://localhost/*
  - http://127.0.0.1/*
  - (kept) http://atom:8000/*

AtoM OIDC Plugin Config (test/etc/oidc/arOidcPlugin/config/app.yml)
- Providers:
  - demo: url http://127.0.0.1:8080/realms/demo
  - secondary: url http://127.0.0.1:8080/realms/secondary
- primary_provider_name: demo
- provider_query_param_name: provider (use /user/login?provider=secondary)
- redirect_url: http://localhost/index.php/oidc/login
- logout_redirect_url: http://localhost
- roles_source: access-token; roles_path: realm_access -> roles
- user groups map realm roles to AtoM groups (IDs 100–103).

Workflow (GitHub Actions)
- File: .github/workflows/integration-tests-oidc.yml
- Sequence:
  - Start Percona, Elasticsearch, Gearman (docker-compose.test.yml).
  - Launch Keycloak (docker/docker-compose.keycloak.yml; image quay.io/keycloak/keycloak:26.3).
  - Wait for realm well-known endpoint to be ready.
  - Copy OIDC app.yml into plugin; activate plugin via empty file activate-oidc-plugin.
  - Install dependencies, run AtoM installer, start PHP-FPM, worker, and Nginx.
  - Run Cypress across matrix: Chrome, Electron, Firefox.
  - Print logs and upload screenshots on failure.

Cypress Tests
- File: cypress/e2e/oidc_auth.cy.js
- Primary flow:
  - Visit /user/login -> click "Log in with SSO" -> complete Keycloak login (demo/demo) via cy.origin('http://127.0.0.1:8080', ...).
  - Assert AtoM shows the logged-in username; logout.
- Secondary flow:
  - Visit /user/login?provider=secondary -> login (supportdefault/support); assert username.
- Environment overrides:
  - OIDC_USERNAME / OIDC_PASSWORD (defaults demo/demo)
  - OIDC_SECONDARY_USERNAME / OIDC_SECONDARY_PASSWORD (defaults supportdefault/support)

Local Reproduction Tips
- Ensure AtoM is reachable at http://localhost and Keycloak at http://127.0.0.1:8080 (or adjust provider URLs accordingly).
- If you change hosts/domains, update both the Keycloak clients (redirectUris/webOrigins) and AtoM plugin app.yml.

