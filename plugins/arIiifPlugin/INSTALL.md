# Installation Guide for arIiifPlugin

This guide provides detailed instructions for installing the IIIF Image Carousel plugin in AtoM.

## Prerequisites

- AtoM 2.x or later installed at `/usr/share/nginx/atom` (or adjust paths accordingly)
- Root or sudo access to the server
- PHP 5.6+ (PHP 7.x recommended)
- IIIF-compliant image server (optional but recommended)

## Quick Installation

### Option 1: Automated Installation (Recommended)

```bash
# Navigate to the plugin directory
cd arIiifPlugin

# Run the installation script
sudo ./install.sh
```

The script will:
- Copy plugin files to AtoM plugins directory
- Download and install OpenSeadragon
- Set correct permissions
- Enable the plugin
- Clear AtoM cache
- Restart services

### Option 2: Manual Installation

Follow these steps if the automated installation doesn't work or you need more control:

#### 1. Copy Plugin Files

```bash
# Copy the plugin to AtoM plugins directory
sudo cp -r arIiifPlugin /usr/share/nginx/atom/plugins/

# Set correct ownership
sudo chown -R www-data:www-data /usr/share/nginx/atom/plugins/arIiifPlugin

# Set correct permissions
sudo chmod -R 755 /usr/share/nginx/atom/plugins/arIiifPlugin
```

#### 2. Install OpenSeadragon

```bash
# Download OpenSeadragon
cd /tmp
wget https://github.com/openseadragon/openseadragon/releases/download/v4.1.0/openseadragon-bin-4.1.0.zip

# Extract
unzip openseadragon-bin-4.1.0.zip

# Copy to plugin vendor directory
sudo cp openseadragon-bin-4.1.0/openseadragon.min.js \
    /usr/share/nginx/atom/plugins/arIiifPlugin/vendor/openseadragon/

sudo cp -r openseadragon-bin-4.1.0/images/* \
    /usr/share/nginx/atom/plugins/arIiifPlugin/vendor/openseadragon/images/

# Set permissions
sudo chown -R www-data:www-data /usr/share/nginx/atom/plugins/arIiifPlugin/vendor
sudo chmod -R 755 /usr/share/nginx/atom/plugins/arIiifPlugin/vendor
```

#### 3. Enable Plugin in AtoM

```bash
cd /usr/share/nginx/atom

# Enable the plugin
sudo -u www-data php symfony tools:enable-plugin arIiifPlugin

# Verify plugin is enabled
sudo -u www-data php symfony tools:list-plugins
```

#### 4. Configure Plugin

Edit your AtoM configuration:

```bash
sudo nano /usr/share/nginx/atom/apps/qubit/config/app.yml
```

Add the IIIF configuration (adjust as needed):

```yaml
all:
  iiif:
    # Your IIIF Image Server base URL
    base_url: https://your-iiif-server.com/iiif/2
    
    # IIIF API version
    api_version: 2
    
    # Enable IIIF Presentation API manifests
    enable_manifests: true
    
    # Carousel settings
    carousel:
      auto_rotate: true
      rotate_interval: 5000
      show_navigation: true
      show_thumbnails: false
      viewer_height: 600
      
    # Viewer settings
    viewer:
      enable_zoom: true
      enable_rotation: true
      enable_fullscreen: true
      max_zoom_level: 4
```

#### 5. Clear Cache and Restart Services

```bash
# Clear AtoM cache
sudo -u www-data php symfony cc

# Clear symfony cache
sudo rm -rf /usr/share/nginx/atom/cache/*

# Restart PHP-FPM (adjust version as needed)
sudo systemctl restart php7.4-fpm

# Restart Nginx
sudo systemctl restart nginx
```

## Post-Installation Configuration

### 1. Setting up IIIF Image Server (Cantaloupe Example)

If you don't have a IIIF image server, here's how to set up Cantaloupe:

```bash
# Install Java (if not already installed)
sudo apt-get update
sudo apt-get install -y openjdk-11-jre-headless

# Download Cantaloupe
cd /opt
sudo wget https://github.com/cantaloupe-project/cantaloupe/releases/download/v5.0.5/cantaloupe-5.0.5.zip
sudo unzip cantaloupe-5.0.5.zip
cd cantaloupe-5.0.5

# Create configuration
sudo cp cantaloupe.properties.sample cantaloupe.properties
sudo nano cantaloupe.properties
```

Edit these key settings in `cantaloupe.properties`:

```properties
# HTTP port
http.port = 8182

# Enable IIIF Image API 2.0
endpoint.iiif.2.enabled = true

# Disable IIIF Image API 3.0 (unless needed)
endpoint.iiif.3.enabled = false

# Set source for images
FilesystemSource.BasicLookupStrategy.path_prefix = /usr/share/nginx/atom/uploads/

# Enable caching
cache.server.derivative.enabled = true
cache.server.derivative = FilesystemCache
FilesystemCache.pathname = /var/cache/cantaloupe
```

Create systemd service:

```bash
sudo nano /etc/systemd/system/cantaloupe.service
```

```ini
[Unit]
Description=Cantaloupe IIIF Image Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/opt/cantaloupe-5.0.5
ExecStart=/usr/bin/java -Dcantaloupe.config=/opt/cantaloupe-5.0.5/cantaloupe.properties -Xmx2g -jar /opt/cantaloupe-5.0.5/cantaloupe-5.0.5.jar
Restart=on-failure
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Start and enable:

```bash
# Create cache directory
sudo mkdir -p /var/cache/cantaloupe
sudo chown www-data:www-data /var/cache/cantaloupe

# Start service
sudo systemctl daemon-reload
sudo systemctl enable cantaloupe
sudo systemctl start cantaloupe

# Check status
sudo systemctl status cantaloupe
```

Update `app.yml` with Cantaloupe URL:

```yaml
all:
  iiif:
    base_url: http://localhost:8182/iiif/2
```

### 2. Configure Nginx Proxy (Optional but Recommended)

To serve IIIF over HTTPS alongside AtoM:

```bash
sudo nano /etc/nginx/sites-available/atom
```

Add inside the server block:

```nginx
# IIIF Image Server Proxy
location /iiif/2/ {
    proxy_pass http://localhost:8182/iiif/2/;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    
    # CORS headers
    add_header 'Access-Control-Allow-Origin' '*' always;
    add_header 'Access-Control-Allow-Methods' 'GET, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Origin, X-Requested-With, Content-Type, Accept' always;
    
    if ($request_method = 'OPTIONS') {
        return 204;
    }
}
```

Test and reload:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

Update `app.yml`:

```yaml
all:
  iiif:
    base_url: https://your-atom-domain.com/iiif/2
```

## Verification

### 1. Check Plugin Status

```bash
cd /usr/share/nginx/atom
sudo -u www-data php symfony tools:list-plugins
```

You should see `arIiifPlugin` in the enabled plugins list.

### 2. Check Files

```bash
# Check plugin directory
ls -la /usr/share/nginx/atom/plugins/arIiifPlugin/

# Check OpenSeadragon
ls -la /usr/share/nginx/atom/plugins/arIiifPlugin/vendor/openseadragon/

# Check assets
ls -la /usr/share/nginx/atom/plugins/arIiifPlugin/js/
ls -la /usr/share/nginx/atom/plugins/arIiifPlugin/css/
```

### 3. Test IIIF Server (if installed)

```bash
# Test Cantaloupe is running
curl http://localhost:8182/iiif/2/

# Should return JSON with IIIF server info
```

### 4. Test in Browser

1. Log into your AtoM instance
2. Navigate to an information object with digital objects
3. Add the component to a template (see Usage section in README.md)
4. View the page to see the carousel

## Troubleshooting Installation

### Plugin Not Listed

```bash
# Check plugin directory exists
ls -la /usr/share/nginx/atom/plugins/ | grep arIiif

# Check permissions
ls -la /usr/share/nginx/atom/plugins/arIiifPlugin/

# Try re-enabling
sudo -u www-data php symfony tools:enable-plugin arIiifPlugin
```

### OpenSeadragon Not Loading

```bash
# Verify OpenSeadragon files exist
ls -la /usr/share/nginx/atom/plugins/arIiifPlugin/vendor/openseadragon/openseadragon.min.js

# Check file permissions
sudo chmod 755 /usr/share/nginx/atom/plugins/arIiifPlugin/vendor/openseadragon/openseadragon.min.js
```

### Cache Issues

```bash
# Clear all caches thoroughly
cd /usr/share/nginx/atom
sudo -u www-data php symfony cc
sudo rm -rf cache/*
sudo -u www-data php symfony tools:clear-cache

# Restart services
sudo systemctl restart php7.4-fpm nginx
```

### Permission Issues

```bash
# Fix all permissions
sudo chown -R www-data:www-data /usr/share/nginx/atom/plugins/arIiifPlugin
sudo chmod -R 755 /usr/share/nginx/atom/plugins/arIiifPlugin

# Fix web directory permissions
sudo chown -R www-data:www-data /usr/share/nginx/atom/
```

### IIIF Server Connection Issues

```bash
# Test local connection
curl http://localhost:8182/iiif/2/

# Check if Cantaloupe is running
sudo systemctl status cantaloupe

# Check logs
sudo journalctl -u cantaloupe -f
```

### PHP Errors

```bash
# Check PHP error log
sudo tail -f /var/log/php7.4-fpm.log

# Check Nginx error log
sudo tail -f /var/log/nginx/error.log

# Check AtoM logs
tail -f /usr/share/nginx/atom/log/*.log
```

## Uninstallation

If you need to remove the plugin:

```bash
# Disable plugin
cd /usr/share/nginx/atom
sudo -u www-data php symfony tools:disable-plugin arIiifPlugin

# Remove plugin files
sudo rm -rf /usr/share/nginx/atom/plugins/arIiifPlugin

# Clear cache
sudo -u www-data php symfony cc

# Restart services
sudo systemctl restart php7.4-fpm nginx
```

## Getting Help

- Check the README.md in the plugin directory
- Review AtoM documentation: https://www.accesstomemory.org/docs/
- IIIF specifications: https://iiif.io/
- OpenSeadragon documentation: https://openseadragon.github.io/

## Next Steps

After successful installation, see README.md for:
- Usage examples
- Configuration options
- Integration with your templates
- IIIF manifest generation
