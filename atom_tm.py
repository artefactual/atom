from pytm import TM, Actor, Server, Datastore, Boundary, Dataflow, Lambda

tm = TM("AtoM Threat Model with Auth, Files, and Background Jobs")

# --- Boundaries ---
internet = Boundary("Internet")
dmz = Boundary("DMZ")
internal_net = Boundary("Internal Network")

# --- Actors ---
researcher = Actor("Researcher (Public User)")
researcher.isHuman = True
researcher.authenticated = False

archivist = Actor("Archivist (Authenticated User)")
archivist.isHuman = True
archivist.authenticated = True

sysadmin = Actor("System Administrator")
sysadmin.isHuman = True
sysadmin.authenticated = True
sysadmin.isAdmin = True

# --- Servers / Components ---
web_server = Server("Web Server")
web_server.inBoundary = dmz
web_server.isHardened = True
web_server.sanitizesInput = True

app_server = Server("AtoM Application (PHP/Symfony)")
app_server.inBoundary = dmz
app_server.isHardened = True
app_server.sanitizesInput = True
app_server.handlesSessions = True

db = Datastore("MySQL Database")
db.inBoundary = internal_net
db.isSQL = True
db.storesSensitiveData = True     # user accounts, archival metadata
db.isEncrypted = True

search_index = Datastore("Elasticsearch")
search_index.inBoundary = internal_net
search_index.storesSensitiveData = False
search_index.isEncrypted = False
search_index.note = "Risk accepted: Elasticsearch traffic is internal-only; TLS not required."


file_storage = Datastore("File Storage (Digital Objects)")
file_storage.inBoundary = internal_net
file_storage.storesSensitiveData = True    # uploaded archival objects
file_storage.isEncrypted = True

bg_worker = Lambda("Background Worker (Indexing/Jobs)")
bg_worker.inBoundary = internal_net
bg_worker.hasAccessControl = True

# --- Dataflows ---

# User interactions
df1 = Dataflow(researcher, web_server, "HTTP/HTTPS requests (browse, search)")
df1.protocol = "HTTPS"
df1.isEncrypted = True

df2 = Dataflow(archivist, web_server, "HTTP/HTTPS requests (auth, upload, manage)")
df2.protocol = "HTTPS"
df2.isEncrypted = True
df2.authenticated = True

# Authentication
df3 = Dataflow(archivist, app_server, "Login credentials (username/password)")
df3.protocol = "HTTPS"
df3.isEncrypted = True
df3.containsCredentials = True

df4 = Dataflow(app_server, db, "Auth check (SQL queries)")
df4.protocol = "SQL"
df4.isEncrypted = True

df5 = Dataflow(db, app_server, "Auth results")
df5.protocol = "SQL"
df5.isEncrypted = True

# Web server to Application
df6 = Dataflow(web_server, app_server, "Forward requests (PHP-FPM / FastCGI)")
df6.isEncrypted = False   # usually local socket/pipe

# Application to Database
df7 = Dataflow(app_server, db, "SQL queries (CRUD)")
df7.protocol = "SQL"
df7.isEncrypted = True

df8 = Dataflow(db, app_server, "SQL results")
df8.protocol = "SQL"
df8.isEncrypted = True

# Application to Elasticsearch
df9 = Dataflow(app_server, search_index, "Indexing and search queries")
df9.protocol = "HTTP"
df9.isEncrypted = False   # ES plaintext unless configured TLS
df9.note = "Risk accepted: internal traffic, protected by firewall."

df10 = Dataflow(search_index, app_server, "Search results")
df10.protocol = "HTTP"
df10.isEncrypted = False

# File handling
df11 = Dataflow(archivist, app_server, "File upload request (metadata + file)")
df11.protocol = "HTTPS"
df11.isEncrypted = True

df12 = Dataflow(app_server, file_storage, "Store digital object")
df12.protocol = "File I/O"
df12.isEncrypted = True

df13 = Dataflow(file_storage, app_server, "Retrieve stored file")
df13.protocol = "File I/O"
df13.isEncrypted = True

df14 = Dataflow(app_server, researcher, "Download file (if public)")
df14.protocol = "HTTPS"
df14.isEncrypted = True

# Background worker interactions
df15 = Dataflow(app_server, bg_worker, "Dispatch background job (indexing, import/export)")
df15.protocol = "Internal API"
df15.isEncrypted = True

df16 = Dataflow(bg_worker, search_index, "Background indexing")
df16.protocol = "HTTP"
df16.isEncrypted = False

df17 = Dataflow(bg_worker, db, "Batch DB operations")
df17.protocol = "SQL"
df17.isEncrypted = True

df18 = Dataflow(bg_worker, file_storage, "Bulk file operations")
df18.protocol = "File I/O"
df18.isEncrypted = True

# Sysadmin access
df19 = Dataflow(sysadmin, web_server, "SSH / Admin management")
df19.protocol = "SSH"
df19.isEncrypted = True
df19.authenticated = True

df20 = Dataflow(sysadmin, db, "Direct DB management")
df20.protocol = "SQL over SSH"
df20.isEncrypted = True
df20.authenticated = True

df21 = Dataflow(sysadmin, search_index, "Direct Elasticsearch management")
df21.protocol = "HTTPS"
df21.isEncrypted = True
df21.authenticated = True

df22 = Dataflow(sysadmin, file_storage, "Direct file storage access")
df22.protocol = "SSH / Filesystem"
df22.isEncrypted = True
df22.authenticated = True

if __name__ == "__main__":
    tm.process()
