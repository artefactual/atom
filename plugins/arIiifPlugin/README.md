# AtoM IIIF Plugin

This plugin adds IIIF (International Image Interoperability Framework) Presentation API 2.1 support to AtoM (Access to Memory), enabling archival institutions to share digital images using industry-standard protocols for enhanced interoperability and user experience.

## Overview

The IIIF Plugin generates IIIF Presentation API manifests for AtoM information objects that contain digital images. These manifests can be consumed by IIIF-compatible viewers such as Mirador, Universal Viewer, or OpenSeadragon to provide rich, interactive viewing experiences.

## Installation

### Method 1: Via AtoM Web Interface (Recommended)

1. **Enable via Web UI**:
   - Log in to AtoM as an administrator
   - Navigate to **Admin > Plugins**
   - Find **"AtoM IIIF Plugin"** in the list
   - Click **"Enable"** to activate the plugin
   - Clear cache:

      ```bash
      php symfony cc
      ```

2. **Configure IIIF server URL** (see Configuration section below)

## URL Structure

The plugin exposes IIIF manifests using AtoM slugs for SEO-friendly, persistent URLs:

```
https://your-atom-site.org/iiif/manifest/{slug}
```

**Examples:**

- `https://archives.example.org/iiif/manifest/fonds-1-series-2-file-15`
- `https://archives.example.org/iiif/manifest/john-doe-correspondence-1942`

## Configuration

### IIIF Image Server Configuration

Configure your IIIF image server URL using one of these methods:

#### Option 1: Plugin Configuration (Recommended)

Edit `plugins/arIiifPlugin/config/app.yml`:

```yaml
all:
  app_iiif_server_url: "https://your-cantaloupe-server.com/iiif/2"
  app_iiif_default_width: 800
  app_iiif_default_height: 600
  app_iiif_manifest_cache_ttl: 3600
```

#### Option 2: Global AtoM Configuration

Add to `apps/qubit/config/app.yml`:

```yaml
all:
  app_iiif_server_url: "https://your-cantaloupe-server.com/iiif/2"
```

#### Option 3: Environment Variable

Set the environment variable:

```bash
export IIIF_SERVER_URL="https://your-cantaloupe-server.com/iiif/2"
```

#### Option 4: Default Fallback

If no configuration is set, the plugin defaults to:

```
http://cantaloupe:8182/iiif/2
```

### Feature Configuration

The plugin supports several configurable features:

```yaml
all:
  # Core settings
  app_iiif_server_url: 'http://cantaloupe:8182/iiif/2'
  app_iiif_default_width: 800
  app_iiif_default_height: 600
  app_iiif_manifest_cache_ttl: 3600  # seconds, 0 to disable
  
  # Feature toggles
  app_iiif_enable_compound_objects: true
  app_iiif_enable_thumbnails: true
  app_iiif_enable_metadata: true
  
  # Debug mode
  app_iiif_debug: false
```

### Recommended IIIF Image Servers

- **Cantaloupe**: High-performance Java-based server (recommended)
- **Loris**: Python-based server (experimented but had some issues)
- **IIPImage**: Fast C++ server (untested)
- **RAIS**: Go-based server (untested)

## Security Model

The plugin implements comprehensive security following AtoM's access control patterns:

### Access Control Checks

1. **Object Read Permission**: User must have `read` access to the information object
2. **Publication Status**: Draft objects require additional `viewDraft` permission
3. **Digital Object Access**: User must have `readReference` permission for image access
4. **PREMIS Rights**: Anonymous users subject to PREMIS-based access restrictions

### Error Responses

- `400 Bad Request`: Invalid slug parameter
- `403 Forbidden`: Access denied due to insufficient permissions
- `404 Not Found`: Information object or digital objects not found
- `500 Internal Server Error`: Manifest generation errors

## How It Works

### 1. Request Processing Flow

```
User Request → Route Handler → Security Validation → Digital Object Discovery → 
Manifest Generation → Image Dimension Extraction → JSON Response
```

### 2. Security Validation

The plugin performs layered security checks:

```php
// Basic read permission
QubitAcl::check($object, 'read')

// Publication status for drafts
QubitAcl::check($object, 'viewDraft')

// Digital object access
QubitAcl::check($object, 'readReference')

// PREMIS rights for anonymous users
QubitGrantedRight::checkPremis($object->id, 'readReference')
```

### 3. Digital Object Discovery

The plugin handles multiple digital object scenarios:

- **Single images**: Primary digital object only
- **Compound objects**: Parent object with multiple children
- **Mixed media**: Filters to image-type objects only

### 4. Image Dimension Extraction

Three-tier fallback system for accurate dimensions:

1. **Local file analysis**: Uses PHP's `getimagesize()` for fast, accurate results
2. **IIIF server query**: Fetches dimensions from `{server}/{image}/info.json`
3. **Sensible defaults**: Falls back to 800x600 if other methods fail

### 5. Manifest Structure

Generated manifests include:

#### Core Elements

- **@context**: IIIF Presentation API 2.1 context
- **@id**: Persistent manifest identifier using slug
- **label**: Information object title
- **sequences**: Image sequence(s)
- **canvases**: Individual image canvases with correct dimensions

#### Metadata

- **Description**: From `scopeAndContent` field
- **Level of Description**: Archival hierarchy level
- **Repository**: Holding institution
- **Format**: Original file MIME type

#### Thumbnails

- **Manifest thumbnail**: Links to AtoM's generated thumbnails
- **Canvas thumbnails**: Individual image thumbnails

### 6. Performance Optimizations

#### HTTP Caching

```http
Cache-Control: public, max-age=3600
ETag: {manifest-hash}
```

#### Timeout Controls

- IIIF server requests timeout after 5 seconds
- Graceful fallback to default dimensions

#### Lazy Processing

- Only processes image-type digital objects
- Skips generation for objects without valid images

## Manifest Examples

### Simple Image Manifest

```json
{
  "@context": "http://iiif.io/api/presentation/2/context.json",
  "@type": "sc:Manifest",
  "@id": "https://archives.example.org/iiif/manifest/photograph-123",
  "label": "Portrait of John Smith, 1942",
  "metadata": [
    {"label": "Description", "value": "Formal portrait taken at Smith Studio"},
    {"label": "Repository", "value": "City Archives"}
  ],
  "thumbnail": {
    "@id": "https://iiif-server.com/photo123.jpg/full/!270,270/0/default.jpg",
    "@type": "dctypes:Image"
  },
  "sequences": [{
    "@type": "sc:Sequence",
    "canvases": [{
      "@id": "https://archives.example.org/iiif/manifest/photograph-123/canvas/1",
      "@type": "sc:Canvas",
      "label": "photograph-123.jpg",
      "height": 2048,
      "width": 1536,
      "images": [{
        "@type": "oa:Annotation",
        "motivation": "sc:painting",
        "resource": {
          "@id": "https://iiif-server.com/photo123.jpg/full/full/0/default.jpg",
          "@type": "dctypes:Image",
          "format": "image/jpeg",
          "height": 2048,
          "width": 1536
        },
        "on": "https://archives.example.org/iiif/manifest/photograph-123/canvas/1"
      }]
    }]
  }]
}
```

### Compound Object Manifest

```json
{
  "@context": "http://iiif.io/api/presentation/2/context.json",
  "@type": "sc:Manifest",
  "@id": "https://archives.example.org/iiif/manifest/letter-1942-03-15",
  "label": "Letter from Jane Doe to John Smith, March 15, 1942",
  "sequences": [{
    "@type": "sc:Sequence",
    "canvases": [
      {
        "@id": "https://archives.example.org/iiif/manifest/letter-1942-03-15/canvas/1",
        "label": "Page 1",
        "height": 3000,
        "width": 2000
      },
      {
        "@id": "https://archives.example.org/iiif/manifest/letter-1942-03-15/canvas/2",
        "label": "Page 2", 
        "height": 3000,
        "width": 2000
      }
    ]
  }]
}
```

## Integration with IIIF Viewers

### Mirador Integration

```html
<div id="mirador-viewer"></div>
<script>
Mirador.viewer({
  id: 'mirador-viewer',
  manifests: {
    'https://archives.example.org/iiif/manifest/photograph-123': {
      provider: 'City Archives'
    }
  },
  windows: [{
    manifestId: 'https://archives.example.org/iiif/manifest/photograph-123'
  }]
});
</script>
```

### Universal Viewer Integration

```html
<iframe src="https://universalviewer.io/uv.html?manifest=https://archives.example.org/iiif/manifest/photograph-123"></iframe>
```

## Troubleshooting

### Common Issues

#### 403 Forbidden Errors

- Check information object read permissions
- Verify publication status (drafts require viewDraft permission)
- Confirm digital object readReference permissions
- Review PREMIS rights settings for anonymous access

#### 404 Not Found Errors

- Verify the slug exists and is correct
- Ensure the information object has digital objects attached
- Check that digital objects are image types

#### Performance Issues

- Configure HTTP caching at web server level
- Optimize IIIF image server configuration
- Consider CDN for manifest distribution
- Monitor IIIF server response times

#### Dimension Issues

- Ensure IIIF server is accessible from AtoM
- Check file permissions for local image analysis
- Verify network connectivity to IIIF server
- Review IIIF server logs for errors

### Debug Mode

Enable debug information by adding to your configuration:

```yaml
all:
  app_debug_iiif: true
```

This will include additional error details in manifest responses.

## Development

### File Structure

```
plugins/arIiifPlugin/
├── README.md                              # This documentation
├── config/
│   └── arIiifPluginConfiguration.class.php  # Plugin configuration and routing
└── modules/
    └── iiif/
        └── actions/
            └── manifestAction.class.php      # Main manifest generation logic
```

### Key Classes and Methods

#### `iiifActions::executeManifest()`

Main entry point for manifest generation.

#### `iiifActions::buildManifest()`

Constructs the complete IIIF manifest structure.

#### `iiifActions::getImageDimensions()`

Extracts actual image dimensions using multiple fallback methods.

#### `iiifActions::buildCanvas()`

Creates individual canvas objects for each image.

### Testing

Test your implementation:

```bash
# Test basic manifest generation
curl -H "Accept: application/json" \
     "https://your-atom-site.org/iiif/manifest/your-slug"

# Test with IIIF validator
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"url":"https://your-atom-site.org/iiif/manifest/your-slug"}' \
     "https://iiif.io/api/presentation/validator/service/validate"
```

## Standards Compliance

This plugin implements:

- **IIIF Presentation API 2.1**: Full specification compliance
- **HTTP caching standards**: Proper Cache-Control and ETag headers
- **Security best practices**: Input validation and access control
- **AtoM conventions**: Follows AtoM's architecture and security patterns

## Contributing

Contributions are welcome! Please:

1. Follow AtoM's coding standards
2. Add tests for new functionality  
3. Update documentation for changes
4. Ensure security best practices are maintained

## Support

For issues and questions:

1. Check AtoM's documentation and forums
2. Review IIIF specifications at <https://iiif.io/>
3. Submit issues to the AtoM GitHub repository
