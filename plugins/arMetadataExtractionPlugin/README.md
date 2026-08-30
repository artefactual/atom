# arMetadataExtractionPlugin for AtoM 2.9

A Symfony 1.4 plugin for AtoM (Access to Memory) that automatically extracts and applies metadata from uploaded digital objects (images, PDFs, etc.) to their associated information objects.

## Features

- **Automatic Metadata Extraction**: Extracts EXIF, IPTC, and XMP metadata from uploaded files
- **Smart Field Population**: Intelligently populates AtoM fields including:
  - Title
  - Description (Scope and Content)
  - Creator (with automatic actor creation)
  - Creation dates
  - Subject access points (keywords)
  - Rights statements
  - GPS coordinates
- **Technical Metadata**: Adds camera settings and technical details to physical characteristics
- **Auto-generated Keywords**: Creates relevant subject terms based on camera type and settings
- **Configurable Settings**: Admin interface for controlling extraction behavior
- **Non-destructive Updates**: Option to only update empty fields or overwrite existing data

## Requirements

- AtoM 2.9.x
- PHP 8.3+
- exiftool installed on the server
- arEmbeddedMetadataParser class (usually included with AtoM)

## Installation

### Step 1: Install the Plugin

```bash
# Navigate to your AtoM plugins directory
cd /usr/share/nginx/atom/plugins/

# Copy the plugin folder
sudo cp -r /path/to/arMetadataExtractionPlugin ./

# Set proper permissions
sudo chown -R www-data:www-data arMetadataExtractionPlugin/
sudo chmod -R 755 arMetadataExtractionPlugin/
```

### Step 2: Replace the Digital Object Upload Action

The plugin includes a modified version of the upload action. You have two options:

#### Option A: Replace the Core File (Recommended for Testing)
```bash
# Backup the original file
sudo cp /usr/share/nginx/atom/apps/qubit/modules/object/actions/addDigitalObjectAction.class.php \
        /usr/share/nginx/atom/apps/qubit/modules/object/actions/addDigitalObjectAction.class.php.backup

# Copy the plugin's version
sudo cp /usr/share/nginx/atom/plugins/arMetadataExtractionPlugin/modules/object/actions/addDigitalObjectAction.class.php \
        /usr/share/nginx/atom/apps/qubit/modules/object/actions/addDigitalObjectAction.class.php
```

#### Option B: Create a Local Override (Recommended for Production)
```bash
# Create local override directory if it doesn't exist
sudo mkdir -p /usr/share/nginx/atom/apps/qubit/modules/object/actions/

# Copy the plugin's action
sudo cp /usr/share/nginx/atom/plugins/arMetadataExtractionPlugin/modules/object/actions/addDigitalObjectAction.class.php \
        /usr/share/nginx/atom/apps/qubit/modules/object/actions/
```

### Step 3: Clear Symfony Cache

```bash
cd /usr/share/nginx/atom/
sudo -u www-data php symfony cc
```

### Step 4: Update Database Settings (Optional)

The plugin will automatically create its settings on first use. To manually initialize:

```bash
1. Log in to AtoM as an administrator
2. Navigate to Admin → Settings
3. Look for "Metadata extraction settings" in the settings list
4. Configure the plugin according to your needs
```

### Step 5: Verify Installation

1. Log in to AtoM as an administrator
2. Navigate to Admin → Settings
3. Look for "Metadata extraction settings" in the settings list
4. Configure the plugin according to your needs

## Configuration

Access the plugin settings at: **Admin → Settings → Metadata extraction settings**

### Available Settings:

#### Main Settings
- **Enable/Disable**: Turn metadata extraction on or off globally

#### Metadata Types
- **Extract EXIF**: Camera settings, date taken, technical information
- **Extract IPTC**: Headlines, captions, keywords, creator information
- **Extract XMP**: Adobe metadata including descriptions and rights

#### Field Update Behavior
- **Always overwrite title**: Replace existing titles (unchecked = only update if empty)
- **Always overwrite description**: Replace existing descriptions (unchecked = only update if empty)

#### Additional Features
- **Auto-generate keywords**: Create subject terms based on camera/technical data
- **Extract GPS coordinates**: Store location data from geotagged images
- **Add technical metadata**: Include camera settings in physical characteristics

## Usage

Once installed and configured, the plugin works automatically:

1. Upload a digital object to any information object
2. The plugin extracts available metadata from the file
3. Metadata is mapped to appropriate AtoM fields
4. Information object is updated with the extracted data

### Metadata Mapping

| Source Metadata | AtoM Field | Notes |
|-----------------|------------|-------|
| XMP Title / IPTC Headline | Title | Only updates if empty or configured to overwrite |
| XMP Description / IPTC Caption | Scope and Content | Only updates if empty or configured to overwrite |
| XMP Creator / IPTC By-line / EXIF Artist | Creator (Event) | Creates new actor if needed |
| EXIF DateTimeOriginal | Creation Date (Event) | Parsed to YYYY-MM-DD format |
| XMP Subject / IPTC Keywords | Subject Access Points | Added as taxonomy terms |
| EXIF GPS Data | Digital Object Properties | Stored as latitude/longitude |
| EXIF Camera/Lens Data | Physical Characteristics | Appended as technical metadata |
| XMP Rights / IPTC Copyright | Access Conditions | Only updates if empty |

### Auto-generated Keywords Examples

Based on camera and settings, the plugin may generate keywords like:
- "Canon Photography" (for Canon cameras)
- "Mobile Photography" (for phone cameras)
- "Wide Angle Photography" (focal length ≤ 35mm)
- "Telephoto Photography" (focal length ≥ 85mm)
- "High ISO Photography" (ISO ≥ 1600)
- "Digital Photography" (always added)

## Troubleshooting

### Metadata Not Extracting

1. Check that exiftool is installed:
   ```bash
   which exiftool
   ```

2. Verify file permissions:
   ```bash
   ls -la /usr/share/nginx/atom/plugins/arMetadataExtractionPlugin/
   ```

3. Check Symfony logs:
   ```bash
   tail -f /usr/share/nginx/atom/log/frontend_*.log
   ```

### Settings Not Appearing

1. Clear the cache:
   ```bash
   cd /usr/share/nginx/atom/
   sudo -u www-data php symfony cc
   ```

2. Check plugin is enabled in ProjectConfiguration.class.php

### GPS Coordinates Not Saving

1. Ensure "Extract GPS coordinates" is enabled in settings
2. Verify the image contains GPS data using exiftool:
   ```bash
   exiftool -GPS* your-image.jpg
   ```

## Uninstallation

1. Restore the original addDigitalObjectAction.class.php:
   ```bash
   sudo mv /usr/share/nginx/atom/apps/qubit/modules/object/actions/addDigitalObjectAction.class.php.backup \
           /usr/share/nginx/atom/apps/qubit/modules/object/actions/addDigitalObjectAction.class.php
   ```

2. Remove the plugin directory:
   ```bash
   sudo rm -rf /usr/share/nginx/atom/plugins/arMetadataExtractionPlugin/
   ```

3. Clear cache:
   ```bash
   cd /usr/share/nginx/atom/
   sudo -u www-data php symfony cc
   ```

## Development

### Plugin Structure

```
arMetadataExtractionPlugin/
├── config/
│   └── arMetadataExtractionPluginConfiguration.class.php  # Plugin configuration
├── lib/
│   └── arMetadataExtractor.class.php                      # Main extraction logic
├── modules/
│   ├── object/
│   │   └── actions/
│   │       └── addDigitalObjectAction.class.php           # Modified upload action
│   └── settings/
│       ├── actions/
│       │   └── metadataExtractionAction.class.php         # Settings action
│       └── templates/
│           └── metadataExtractionSuccess.php              # Settings template
└── README.md
```

### Extending the Plugin

To add support for additional metadata fields:

1. Modify `normalizeMetadata()` in `arMetadataExtractor.class.php`
2. Add mapping logic in `applyMetadata()`
3. Update the settings interface if needed

### Event System

The plugin uses Symfony events:
- `digital_object.post_create`: Triggered after a digital object is saved
- Can be extended to listen for other events like `digital_object.post_update`

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Test thoroughly with AtoM 2.9
4. Submit a pull request

## License

This plugin is released under the GNU Affero General Public License v3.0, the same license as AtoM.

## Credits

Developed by Johan Pieterse (johan@theahg.co.za) for The Archive and Heritage Group.

Based on the original AtoM digital object handling code by Artefactual Systems.

## Support

For issues or questions:
- Email: johan@theahg.co.za
- AtoM Forum: https://groups.google.com/forum/#!forum/ica-atom-users

## Version History

### 1.0.0 (2024-01)
- Initial release
- Support for EXIF, IPTC, and XMP metadata
- Admin settings interface
- Auto-keyword generation
- GPS coordinate extraction
