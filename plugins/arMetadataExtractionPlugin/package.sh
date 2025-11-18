#!/bin/bash

# Package script for arMetadataExtractionPlugin
# Creates a distributable archive

PLUGIN_NAME="arMetadataExtractionPlugin"
VERSION="1.0.0"
ARCHIVE_NAME="${PLUGIN_NAME}-v${VERSION}.tar.gz"

echo "Creating package: $ARCHIVE_NAME"

# Create temporary directory
TEMP_DIR=$(mktemp -d)
PLUGIN_DIR="$TEMP_DIR/$PLUGIN_NAME"

# Copy plugin files
mkdir -p "$PLUGIN_DIR"
cp -r config lib modules README.md install.sh "$PLUGIN_DIR/"

# Create archive
cd "$TEMP_DIR"
tar czf "$ARCHIVE_NAME" "$PLUGIN_NAME"

# Move archive to original directory
mv "$ARCHIVE_NAME" "$OLDPWD/"

# Clean up
rm -rf "$TEMP_DIR"

echo "Package created: $ARCHIVE_NAME"
echo "Size: $(du -h $OLDPWD/$ARCHIVE_NAME | cut -f1)"
echo ""
echo "To install on your AtoM server:"
echo "1. Upload $ARCHIVE_NAME to your server"
echo "2. Extract: tar xzf $ARCHIVE_NAME"
echo "3. cd $PLUGIN_NAME"
echo "4. sudo ./install.sh"
