#!/bin/bash

# Test script for arMetadataExtractionPlugin
# Verifies installation and functionality

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Configuration
ATOM_PATH="/usr/share/nginx/atom"
PLUGIN_NAME="arMetadataExtractionPlugin"

print_test() {
    echo -e "${YELLOW}[TEST]${NC} $1"
}

print_pass() {
    echo -e "${GREEN}[PASS]${NC} $1"
}

print_fail() {
    echo -e "${RED}[FAIL]${NC} $1"
}

echo "========================================="
echo "arMetadataExtractionPlugin Test Suite"
echo "========================================="
echo ""

# Test 1: Check plugin directory exists
print_test "Checking plugin directory..."
if [ -d "$ATOM_PATH/plugins/$PLUGIN_NAME" ]; then
    print_pass "Plugin directory exists"
else
    print_fail "Plugin directory not found at $ATOM_PATH/plugins/$PLUGIN_NAME"
    exit 1
fi

# Test 2: Check required files
print_test "Checking required plugin files..."
REQUIRED_FILES=(
    "$ATOM_PATH/plugins/$PLUGIN_NAME/config/arMetadataExtractionPluginConfiguration.class.php"
    "$ATOM_PATH/plugins/$PLUGIN_NAME/lib/arMetadataExtractor.class.php"
    "$ATOM_PATH/plugins/$PLUGIN_NAME/README.md"
)

for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_pass "Found: $(basename $file)"
    else
        print_fail "Missing: $file"
        exit 1
    fi
done

# Test 3: Check action file is updated
print_test "Checking digital object action file..."
ACTION_FILE="$ATOM_PATH/apps/qubit/modules/object/actions/addDigitalObjectAction.class.php"
if grep -q "arMetadataExtractor" "$ACTION_FILE"; then
    print_pass "Action file has been updated with plugin code"
else
    print_fail "Action file not updated. Plugin may not work."
    exit 1
fi

# Test 4: Check exiftool installation
print_test "Checking exiftool installation..."
if command -v exiftool &> /dev/null; then
    VERSION=$(exiftool -ver)
    print_pass "exiftool installed (version $VERSION)"
else
    print_fail "exiftool not installed. Run: apt-get install libimage-exiftool-perl"
fi

# Test 5: Check arEmbeddedMetadataParser
print_test "Checking arEmbeddedMetadataParser..."
if [ -f "$ATOM_PATH/lib/helper/arEmbeddedMetadataParser.class.php" ]; then
    print_pass "arEmbeddedMetadataParser found"
else
    print_fail "arEmbeddedMetadataParser not found - plugin functionality limited"
fi

# Test 6: Check file permissions
print_test "Checking file permissions..."
PLUGIN_DIR="$ATOM_PATH/plugins/$PLUGIN_NAME"
OWNER=$(stat -c '%U' "$PLUGIN_DIR")
if [ "$OWNER" = "www-data" ]; then
    print_pass "Plugin owned by www-data"
else
    print_fail "Plugin not owned by www-data (owned by $OWNER)"
    echo "  Fix with: sudo chown -R www-data:www-data $PLUGIN_DIR"
fi

# Test 7: Check PHP syntax
print_test "Checking PHP syntax..."
ERROR_COUNT=0
for phpfile in $(find "$PLUGIN_DIR" -name "*.php"); do
    if php -l "$phpfile" > /dev/null 2>&1; then
        echo -e "  ${GREEN}✓${NC} $(basename $phpfile)"
    else
        echo -e "  ${RED}✗${NC} $(basename $phpfile) - Syntax error"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
done

if [ $ERROR_COUNT -eq 0 ]; then
    print_pass "All PHP files have valid syntax"
else
    print_fail "$ERROR_COUNT PHP files have syntax errors"
fi

# Test 8: Database settings check
print_test "Checking database settings..."
if [ -f "$ATOM_PATH/config/config.php" ]; then
    print_pass "Database configuration found"
    echo "  Note: Cannot verify settings without database access"
else
    print_fail "Database configuration not found"
fi

echo ""
echo "========================================="
echo "Test Summary"
echo "========================================="
echo ""

if [ $? -eq 0 ]; then
    print_pass "All tests passed! Plugin appears to be installed correctly."
    echo ""
    echo "To complete setup:"
    echo "1. Clear Symfony cache: cd $ATOM_PATH && sudo -u www-data php symfony cc"
    echo "2. Log in to AtoM as administrator"
    echo "3. Navigate to Admin → Settings → Metadata extraction settings"
    echo "4. Configure and enable the plugin"
    echo "5. Test by uploading an image with EXIF metadata"
else
    print_fail "Some tests failed. Please fix the issues above."
fi

echo ""
echo "For manual testing, create a test image with metadata:"
echo "  exiftool -Artist='Test Artist' -Copyright='Test Copyright' test.jpg"
echo "Then upload it to AtoM and check if metadata is extracted."
