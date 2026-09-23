#!/bin/bash

# arIiifPlugin Installation Script for AtoM
# This script automates the installation of the IIIF plugin

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
ATOM_PATH="/usr/share/nginx/atom"
PLUGIN_NAME="arIiifPlugin"
PLUGIN_PATH="$ATOM_PATH/plugins/$PLUGIN_NAME"
OPENSEADRAGON_VERSION="4.1.0"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}arIiifPlugin Installation Script${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Error: This script must be run as root or with sudo${NC}"
    exit 1
fi

# Check if AtoM directory exists
if [ ! -d "$ATOM_PATH" ]; then
    echo -e "${RED}Error: AtoM directory not found at $ATOM_PATH${NC}"
    echo "Please specify the correct AtoM path by editing this script"
    exit 1
fi

echo -e "${YELLOW}Step 1: Copying plugin files...${NC}"
if [ -d "$PLUGIN_PATH" ]; then
    echo -e "${YELLOW}Plugin directory already exists. Backing up...${NC}"
    mv "$PLUGIN_PATH" "${PLUGIN_PATH}.backup.$(date +%Y%m%d_%H%M%S)"
fi

cp -r "$(dirname "$0")" "$PLUGIN_PATH"
echo -e "${GREEN}✓ Plugin files copied${NC}"

echo ""
echo -e "${YELLOW}Step 2: Setting permissions...${NC}"
chown -R www-data:www-data "$PLUGIN_PATH"
chmod -R 755 "$PLUGIN_PATH"
echo -e "${GREEN}✓ Permissions set${NC}"

echo ""
echo -e "${YELLOW}Step 3: Downloading OpenSeadragon...${NC}"
cd /tmp

if [ ! -f "openseadragon-bin-${OPENSEADRAGON_VERSION}.zip" ]; then
    wget -q "https://github.com/openseadragon/openseadragon/releases/download/v${OPENSEADRAGON_VERSION}/openseadragon-bin-${OPENSEADRAGON_VERSION}.zip"
    if [ $? -ne 0 ]; then
        echo -e "${YELLOW}Warning: Could not download OpenSeadragon automatically${NC}"
        echo "Please download it manually from: https://openseadragon.github.io/"
    else
        unzip -q "openseadragon-bin-${OPENSEADRAGON_VERSION}.zip"
        
        # Copy OpenSeadragon files
        mkdir -p "$PLUGIN_PATH/vendor/openseadragon/images"
        cp "openseadragon-bin-${OPENSEADRAGON_VERSION}/openseadragon.min.js" "$PLUGIN_PATH/vendor/openseadragon/"
        cp -r "openseadragon-bin-${OPENSEADRAGON_VERSION}/images/"* "$PLUGIN_PATH/vendor/openseadragon/images/"
        
        # Set permissions
        chown -R www-data:www-data "$PLUGIN_PATH/vendor"
        chmod -R 755 "$PLUGIN_PATH/vendor"
        
        echo -e "${GREEN}✓ OpenSeadragon installed${NC}"
    fi
else
    echo -e "${GREEN}✓ OpenSeadragon already downloaded${NC}"
fi

echo ""
echo -e "${YELLOW}Step 4: Enabling plugin in AtoM...${NC}"
cd "$ATOM_PATH"

# Check if plugin is already enabled
if sudo -u www-data php symfony tools:list-plugins | grep -q "$PLUGIN_NAME"; then
    echo -e "${GREEN}✓ Plugin already enabled${NC}"
else
    sudo -u www-data php symfony tools:enable-plugin "$PLUGIN_NAME"
    echo -e "${GREEN}✓ Plugin enabled${NC}"
fi

echo ""
echo -e "${YELLOW}Step 5: Clearing cache...${NC}"
sudo -u www-data php symfony cc
echo -e "${GREEN}✓ Cache cleared${NC}"

echo ""
echo -e "${YELLOW}Step 6: Restarting services...${NC}"

# Detect PHP-FPM version
PHP_FPM_SERVICE=$(systemctl list-units --type=service | grep -o 'php[0-9.]*-fpm' | head -1)

if [ -n "$PHP_FPM_SERVICE" ]; then
    systemctl restart "$PHP_FPM_SERVICE"
    echo -e "${GREEN}✓ $PHP_FPM_SERVICE restarted${NC}"
else
    echo -e "${YELLOW}Warning: Could not detect PHP-FPM service${NC}"
fi

if systemctl is-active --quiet nginx; then
    systemctl restart nginx
    echo -e "${GREEN}✓ Nginx restarted${NC}"
else
    echo -e "${YELLOW}Warning: Nginx service not found${NC}"
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Installation Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "1. Configure your IIIF server URL in:"
echo "   $ATOM_PATH/apps/qubit/config/app.yml"
echo ""
echo "2. Add the following configuration:"
echo "   ${GREEN}all:${NC}"
echo "   ${GREEN}  iiif:${NC}"
echo "   ${GREEN}    base_url: https://your-iiif-server.com/iiif/2${NC}"
echo "   ${GREEN}    enable_manifests: true${NC}"
echo ""
echo "3. Clear cache again:"
echo "   ${GREEN}sudo php $ATOM_PATH/symfony cc${NC}"
echo ""
echo "4. Use the component in your templates:"
echo "   ${GREEN}<?php include_component('arIiifPlugin', 'carousel', array('resource' => \$resource)); ?>${NC}"
echo ""
echo -e "For more information, see: ${GREEN}$PLUGIN_PATH/README.md${NC}"
echo ""
