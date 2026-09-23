# arIiifPlugin - IIIF Image Carousel Plugin for AtoM

A comprehensive IIIF (International Image Interoperability Framework) plugin for Access to Memory (AtoM) that provides rotating image carousels, deep zoom viewers, and IIIF Presentation API manifest generation.

## Features

- **Auto-rotating Image Carousel** with configurable intervals
- **OpenSeadragon Integration** for deep zoom and pan capabilities
- **IIIF Presentation API 2.1** manifest generation
- **Multiple viewing modes**: carousel and single viewer
- **Navigation controls**: previous, next, play/pause
- **Keyboard navigation** (arrow keys)
- **Optional thumbnail strip**
- **Fully responsive design**
- **Configurable via app.yml**

## Requirements

- AtoM 2.x or later (Symfony 1.4)
- PHP 5.6+ (7.x recommended)
- IIIF-compliant image server (Cantaloupe, IIPImage, Loris, etc.)
- OpenSeadragon library (included)

## Installation

### 1. Copy Plugin to AtoM

```bash
# Copy the plugin to the AtoM plugins directory
sudo cp -r arIiifPlugin /usr/share/nginx/atom/plugins/

# Set correct permissions
sudo chown -R www-data:www-data /usr/share/nginx/atom/plugins/arIiifPlugin
sudo chmod -R 755 /usr/share/nginx/atom/plugins/arIiifPlugin
```

### 2. Download OpenSeadragon

```bash
# Download OpenSeadragon
cd /tmp
wget https://github.com/openseadragon/openseadragon/releases/download/v4.1.0/openseadragon-bin-4.1.0.zip
unzip openseadragon-bin-4.1.0.zip

# Copy to plugin vendor directory
sudo cp openseadragon-bin-4.1.0/openseadragon.min.js /usr/share/nginx/atom/plugins/arIiifPlugin/vendor/openseadragon/
sudo cp -r openseadragon-bin-4.1.0/images/* /usr/share/nginx/atom/plugins/arIiifPlugin/vendor/openseadragon/images/

# Set permissions
sudo chown -R www-data:www-data /usr/share/nginx/atom/plugins/arIiifPlugin/vendor
```

### 3. Configure Plugin

Edit your AtoM configuration to include the IIIF server URL:

```bash
sudo nano /usr/share/nginx/atom/apps/qubit/config/app.yml
```

Add or modify the following configuration:

```yaml
all:
  iiif:
    # Base URL for your IIIF Image Server
    base_url: https://your-iiif-server.com/iiif/2
    
    # IIIF API version (2 or 3)
    api_version: 2
    
    # Image server type
    server_type: cantaloupe
    
    # Enable IIIF Presentation API manifest generation
    enable_manifests: true
    
    # Carousel default settings
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

### 4. Enable Plugin in AtoM

```bash
# Enable the plugin
sudo php /usr/share/nginx/atom/symfony tools:enable-plugin arIiifPlugin

# Clear cache
sudo php /usr/share/nginx/atom/symfony cc

# Rebuild assets
sudo php /usr/share/nginx/atom/symfony tools:clear-cache
```

### 5. Restart Services

```bash
sudo systemctl restart php7.4-fpm  # Adjust PHP version as needed
sudo systemctl restart nginx
```

## Usage

### Method 1: Using Component in Templates

#### Display Carousel for Information Object

In your template file (e.g., `apps/qubit/modules/informationobject/templates/showSuccess.php`):

```php
<?php 
// Display IIIF carousel if digital objects are available
include_component('arIiifPlugin', 'carousel', array(
  'resource' => $resource,
  'autoRotate' => true,
  'rotateInterval' => 5000,
  'showNavigation' => true,
  'showThumbnails' => true,
  'viewerHeight' => 600
));
?>
```

#### Display Single Viewer for Digital Object

```php
<?php 
// Display single IIIF viewer
include_component('arIiifPlugin', 'viewer', array(
  'resource' => $digitalObject,
  'viewerHeight' => 600
));
?>
```

### Method 2: Custom Implementation

You can also manually specify IIIF images:

```php
<?php
$images = array(
  array(
    'url' => 'https://iiif.example.com/image/1/info.json',
    'label' => 'First Image'
  ),
  array(
    'url' => 'https://iiif.example.com/image/2/info.json',
    'label' => 'Second Image'
  )
);

include_component('arIiifPlugin', 'carousel', array(
  'images' => $images,
  'autoRotate' => true,
  'showThumbnails' => true
));
?>
```

## Configuration Options

### Carousel Component

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `resource` | object | required | QubitInformationObject or QubitDigitalObject |
| `images` | array | null | Manual array of IIIF image URLs (alternative to resource) |
| `autoRotate` | boolean | true | Enable automatic rotation |
| `rotateInterval` | integer | 5000 | Rotation interval in milliseconds |
| `showNavigation` | boolean | true | Show navigation controls |
| `showThumbnails` | boolean | false | Show thumbnail strip |
| `viewerHeight` | integer | 600 | Height of viewer in pixels |

### Viewer Component

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `resource` | object | required | QubitDigitalObject |
| `viewerHeight` | integer | 600 | Height of viewer in pixels |

## IIIF Manifest API

The plugin provides IIIF Presentation API 2.1 manifests:

### Information Object Manifest

```
GET /iiif/{slug}/manifest
```

Example:
```
https://your-atom-site.com/iiif/my-collection/manifest
```

### Digital Object Manifest

```
GET /iiif/object/{id}/manifest
```

Example:
```
https://your-atom-site.com/iiif/object/123/manifest
```

### Canvas Endpoint

```
GET /iiif/{slug}/canvas/{canvas_index}
```

## Digital Object Configuration

### Option 1: Add IIIF Property

Add a property to your digital object:

```php
$property = new QubitProperty();
$property->objectId = $digitalObject->id;
$property->name = 'iiifManifestUrl';
$property->value = 'https://your-iiif-server.com/iiif/2/image123/info.json';
$property->save();
```

### Option 2: Auto-generate from File Path

The plugin can automatically generate IIIF URLs from digital object file paths if your IIIF server is configured in `app.yml`.

## Setting up IIIF Image Server

### Recommended: Cantaloupe

1. **Download Cantaloupe:**
```bash
cd /opt
sudo wget https://github.com/cantaloupe-project/cantaloupe/releases/download/v5.0.5/cantaloupe-5.0.5.zip
sudo unzip cantaloupe-5.0.5.zip
cd cantaloupe-5.0.5
```

2. **Configure Cantaloupe:**
```bash
sudo cp cantaloupe.properties.sample cantaloupe.properties
sudo nano cantaloupe.properties
```

Set:
```properties
FilesystemSource.BasicLookupStrategy.path_prefix = /usr/share/nginx/atom/uploads/
endpoint.iiif.2.enabled = true
endpoint.iiif.3.enabled = false
```

3. **Run Cantaloupe:**
```bash
java -Dcantaloupe.config=cantaloupe.properties -Xmx2g -jar cantaloupe-5.0.5.jar
```

4. **Create systemd service:**
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

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable cantaloupe
sudo systemctl start cantaloupe
```

## Troubleshooting

### Images Not Loading

1. **Check IIIF server is running:**
```bash
curl http://localhost:8182/iiif/2/
```

2. **Verify CORS headers** on IIIF server

3. **Check browser console** for errors

4. **Verify file permissions:**
```bash
sudo ls -la /usr/share/nginx/atom/uploads/
```

### OpenSeadragon Not Found

Check that OpenSeadragon files exist:
```bash
ls -la /usr/share/nginx/atom/plugins/arIiifPlugin/vendor/openseadragon/
```

### Plugin Not Enabled

```bash
# Check enabled plugins
php /usr/share/nginx/atom/symfony tools:list-plugins

# Enable if needed
sudo php /usr/share/nginx/atom/symfony tools:enable-plugin arIiifPlugin
sudo php /usr/share/nginx/atom/symfony cc
```

### Clear Cache Issues

```bash
# Clear all caches
sudo php /usr/share/nginx/atom/symfony cc
sudo rm -rf /usr/share/nginx/atom/cache/*
sudo php /usr/share/nginx/atom/symfony tools:clear-cache

# Restart services
sudo systemctl restart php7.4-fpm
sudo systemctl restart nginx
```

## File Structure

```
arIiifPlugin/
├── config/
│   ├── arIiifPluginConfiguration.class.php
│   └── app.yml
├── css/
│   └── iiif-carousel.css
├── js/
│   └── iiif-carousel.js
├── lib/
│   └── arIiifPluginComponents.class.php
├── modules/
│   ├── arIiifPlugin/
│   │   └── templates/
│   │       ├── _carousel.php
│   │       └── _viewer.php
│   └── iiif/
│       └── actions/
│           └── actions.class.php
├── vendor/
│   └── openseadragon/
│       ├── openseadragon.min.js
│       └── images/
└── README.md
```

## Example Integration

Replace the default digital object display in `showSuccess.php`:

```php
<?php if ($resource->getDigitalObjectCount() > 0): ?>
  <section id="digital-object-section">
    <h2><?php echo __('Digital Objects') ?></h2>
    
    <?php 
    // Use IIIF carousel instead of default viewer
    include_component('arIiifPlugin', 'carousel', array(
      'resource' => $resource,
      'showThumbnails' => true,
      'autoRotate' => true
    ));
    ?>
    
  </section>
<?php endif; ?>
```

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## License

AGPL-3.0 (same as AtoM)

## Support

- AtoM Documentation: https://www.accesstomemory.org/docs/
- IIIF Specifications: https://iiif.io/
- OpenSeadragon: https://openseadragon.github.io/

## Credits

- Built for Access to Memory (AtoM)
- Uses OpenSeadragon for deep zoom
- Implements IIIF Image API and Presentation API
