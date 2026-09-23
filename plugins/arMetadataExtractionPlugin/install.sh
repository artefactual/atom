#!/bin/bash

# arMetadataExtractionPlugin Installation Script for AtoM 2.9
# Author: Johan Pieterse
# Version: 1.0.0

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
ATOM_PATH="/usr/share/nginx/atom_psis"
PLUGIN_NAME="arMetadataExtractionPlugin"
WEB_USER="www-data"
WEB_GROUP="www-data"

# Function to print colored messages
print_message() {
    echo -e "${2}${1}${NC}"
}

# Function to check if running as root
check_root() {
    if [[ $EUID -ne 0 ]]; then
        print_message "This script must be run as root (use sudo)" "$RED"
        exit 1
    fi
}

# Function to check if AtoM directory exists
check_atom_installation() {
    if [ ! -d "$ATOM_PATH" ]; then
        print_message "AtoM installation not found at $ATOM_PATH" "$RED"
        read -p "Enter your AtoM installation path: " ATOM_PATH
        if [ ! -d "$ATOM_PATH" ]; then
            print_message "Invalid AtoM path. Exiting." "$RED"
            exit 1
        fi
    fi
    print_message "Found AtoM installation at: $ATOM_PATH" "$GREEN"
}

# Function to check prerequisites
check_prerequisites() {
    print_message "\nChecking prerequisites..." "$YELLOW"
    
    # Check for exiftool
    if ! command -v exiftool &> /dev/null; then
        print_message "exiftool is not installed. Installing..." "$YELLOW"
        apt-get update && apt-get install -y libimage-exiftool-perl
    else
        print_message "exiftool is installed" "$GREEN"
    fi
    
    # Check for arEmbeddedMetadataParser
    if [ ! -f "$ATOM_PATH/lib/helper/arEmbeddedMetadataParser.class.php" ]; then
        print_message "Warning: arEmbeddedMetadataParser.class.php not found" "$YELLOW"
        print_message "The plugin may not work without this file" "$YELLOW"
    else
        print_message "arEmbeddedMetadataParser found" "$GREEN"
    fi
}

# Function to backup original files
backup_files() {
    print_message "\nBacking up original files..." "$YELLOW"
    
    ACTION_FILE="$ATOM_PATH/apps/qubit/modules/object/actions/addDigitalObjectAction.class.php"
    
    if [ -f "$ACTION_FILE" ]; then
        if [ ! -f "${ACTION_FILE}.backup" ]; then
            cp "$ACTION_FILE" "${ACTION_FILE}.backup"
            print_message "Backed up addDigitalObjectAction.class.php" "$GREEN"
        else
            print_message "Backup already exists, skipping" "$YELLOW"
        fi
    fi
}

# Function to install plugin
install_plugin() {
    print_message "\nInstalling plugin..." "$YELLOW"
    
    # Get current directory (where the plugin is)
    PLUGIN_SOURCE="$(pwd)"
    
    # Check if we're in the plugin directory
    if [ ! -f "$PLUGIN_SOURCE/config/arMetadataExtractionPluginConfiguration.class.php" ]; then
        print_message "Error: Not in plugin directory. Please run from arMetadataExtractionPlugin folder" "$RED"
        exit 1
    fi
    
    # Copy plugin to AtoM plugins directory
    PLUGIN_DEST="$ATOM_PATH/plugins/$PLUGIN_NAME"
    
    if [ -d "$PLUGIN_DEST" ]; then
        print_message "Plugin directory already exists. Removing old version..." "$YELLOW"
        rm -rf "$PLUGIN_DEST"
    fi
    
    cp -r "$PLUGIN_SOURCE" "$PLUGIN_DEST"
    print_message "Plugin files copied to $PLUGIN_DEST" "$GREEN"
    
    # Copy the modified action file
    cp "$PLUGIN_DEST/modules/object/actions/addDigitalObjectAction.class.php" \
       "$ATOM_PATH/apps/qubit/modules/object/actions/addDigitalObjectAction.class.php"
    print_message "Updated addDigitalObjectAction.class.php" "$GREEN"
    
    # Set permissions
    chown -R ${WEB_USER}:${WEB_GROUP} "$PLUGIN_DEST"
    chmod -R 755 "$PLUGIN_DEST"
    print_message "Set correct permissions" "$GREEN"
}

# Function to enable plugin in ProjectConfiguration
enable_plugin() {
    print_message "\nEnabling plugin in ProjectConfiguration..." "$YELLOW"
    
    CONFIG_FILE="$ATOM_PATH/config/ProjectConfiguration.class.php"
    
    if grep -q "$PLUGIN_NAME" "$CONFIG_FILE"; then
        print_message "Plugin already enabled in ProjectConfiguration" "$GREEN"
    else
        # Add plugin to enabled plugins list
        # This is a simplified approach - may need manual editing for complex configurations
        print_message "Please manually add '$PLUGIN_NAME' to the enabled plugins in:" "$YELLOW"
        print_message "$CONFIG_FILE" "$YELLOW"
        print_message "Add this line in the setup() method:" "$YELLOW"
        print_message "\$this->enablePlugins('$PLUGIN_NAME');" "$YELLOW"
    fi
}

# Function to clear cache
clear_cache() {
    print_message "\nClearing Symfony cache..." "$YELLOW"
    
    cd "$ATOM_PATH"
    sudo -u $WEB_USER php symfony cc
    
    print_message "Cache cleared" "$GREEN"
}

# Function to run post-installation tasks
post_installation() {
    print_message "\n========================================" "$GREEN"
    print_message "Installation completed successfully!" "$GREEN"
    print_message "========================================" "$GREEN"
    
    print_message "\nNext steps:" "$YELLOW"
    print_message "1. Log in to AtoM as an administrator" "$NC"
    print_message "2. Navigate to Admin → Settings" "$NC"
    print_message "3. Click on 'Metadata extraction settings'" "$NC"
    print_message "4. Configure the plugin according to your needs" "$NC"
    
    print_message "\nTo test the plugin:" "$YELLOW"
    print_message "1. Upload an image with EXIF/IPTC/XMP metadata" "$NC"
    print_message "2. Check if metadata was extracted to the information object" "$NC"
    
    print_message "\nTo uninstall, run:" "$YELLOW"
    print_message "sudo ./install.sh --uninstall" "$NC"
}

# Function to uninstall plugin
uninstall_plugin() {
    print_message "\nUninstalling plugin..." "$YELLOW"
    
    # Restore backup
    ACTION_FILE="$ATOM_PATH/apps/qubit/modules/object/actions/addDigitalObjectAction.class.php"
    if [ -f "${ACTION_FILE}.backup" ]; then
        mv "${ACTION_FILE}.backup" "$ACTION_FILE"
        print_message "Restored original addDigitalObjectAction.class.php" "$GREEN"
    fi
    
    # Remove plugin directory
    PLUGIN_DEST="$ATOM_PATH/plugins/$PLUGIN_NAME"
    if [ -d "$PLUGIN_DEST" ]; then
        rm -rf "$PLUGIN_DEST"
        print_message "Removed plugin directory" "$GREEN"
    fi
    
    # Clear cache
    clear_cache
    
    print_message "\nPlugin uninstalled successfully" "$GREEN"
    print_message "Remember to remove '$PLUGIN_NAME' from ProjectConfiguration.class.php manually" "$YELLOW"
}

# Main execution
main() {
    print_message "========================================" "$GREEN"
    print_message "arMetadataExtractionPlugin Installer" "$GREEN"
    print_message "========================================" "$NC"
    
    check_root
    
    # Check for uninstall flag
    if [ "$1" == "--uninstall" ]; then
        check_atom_installation
        uninstall_plugin
        exit 0
    fi
    
    # Normal installation
    check_atom_installation
    check_prerequisites
    backup_files
    install_plugin
    enable_plugin
    clear_cache
    post_installation
}

# Run the script
main "$@"
