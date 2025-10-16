#!/bin/bash
# Implementation Verification Script
# Checks all components of lead magnet and conversion tracking implementation

echo "=============================================="
echo "Izende Studio Web - Implementation Verification"
echo "=============================================="
echo ""

ERRORS=0
WARNINGS=0

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Check PHP syntax
echo "1. Checking PHP syntax..."
if php -l forms/lead-capture.php > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} forms/lead-capture.php syntax is valid"
else
    echo -e "${RED}✗${NC} forms/lead-capture.php has syntax errors"
    ((ERRORS++))
fi

# 2. Check download files exist
echo ""
echo "2. Checking downloadable assets..."
FILES=("downloads/website-launch-checklist.pdf" "downloads/seo-audit-template.xlsx" "downloads/hosting-comparison-guide.pdf")
for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        SIZE=$(du -h "$file" | cut -f1)
        echo -e "${GREEN}✓${NC} $file exists ($SIZE)"
    else
        echo -e "${RED}✗${NC} $file is missing"
        ((ERRORS++))
    fi
done

# 3. Check file permissions
echo ""
echo "3. Checking file permissions..."
for file in "${FILES[@]}"; do
    if [ -r "$file" ]; then
        echo -e "${GREEN}✓${NC} $file is readable"
    else
        echo -e "${RED}✗${NC} $file is not readable"
        ((ERRORS++))
    fi
done

# 4. Check conversion tracking in JavaScript
echo ""
echo "4. Checking conversion tracking events..."
EVENTS=("contact_form_submit" "quote_form_submit" "lead_magnet_conversion" "phone_click" "email_click" "cta_click" "scroll_depth")
for event in "${EVENTS[@]}"; do
    if grep -q "trackConversion('$event'" assets/js/main.js; then
        echo -e "${GREEN}✓${NC} $event tracking found"
    else
        echo -e "${RED}✗${NC} $event tracking missing"
        ((ERRORS++))
    fi
done

# 5. Check security helpers
echo ""
echo "5. Checking security infrastructure..."
SECURITY_FUNCTIONS=("validateCSRFToken" "checkRateLimit" "sanitizeInput" "validateEmail" "logSecurityEvent")
for func in "${SECURITY_FUNCTIONS[@]}"; do
    if grep -q "function $func" config/security.php; then
        echo -e "${GREEN}✓${NC} $func() is defined"
    else
        echo -e "${RED}✗${NC} $func() is missing"
        ((ERRORS++))
    fi
done

# 6. Check modal exists in HTML
echo ""
echo "6. Checking modal HTML..."
if grep -q "id=\"leadMagnetModal\"" index.php; then
    echo -e "${GREEN}✓${NC} Lead magnet modal found in index.php"
else
    echo -e "${RED}✗${NC} Lead magnet modal missing from index.php"
    ((ERRORS++))
fi

if grep -q "id=\"leadCaptureForm\"" index.php; then
    echo -e "${GREEN}✓${NC} Lead capture form found in index.php"
else
    echo -e "${RED}✗${NC} Lead capture form missing from index.php"
    ((ERRORS++))
fi

# 7. Check GTM installation
echo ""
echo "7. Checking Google Tag Manager..."
if grep -q "GTM-" index.php; then
    if grep -q "GTM-XXXXXXX" index.php; then
        echo -e "${YELLOW}⚠${NC} GTM container found but using placeholder ID"
        echo "    Action required: Update GTM-XXXXXXX with actual container ID"
        ((WARNINGS++))
    else
        echo -e "${GREEN}✓${NC} GTM container installed with custom ID"
    fi
else
    echo -e "${RED}✗${NC} GTM container not found"
    ((ERRORS++))
fi

# 8. Check .env file
echo ""
echo "8. Checking environment configuration..."
if [ -f ".env" ] || [ -f "config/.env" ] || [ -f "../.env" ]; then
    echo -e "${GREEN}✓${NC} .env file exists"
    
    # Check for email marketing keys (optional)
    if [ -f ".env" ]; then
        ENV_FILE=".env"
    elif [ -f "config/.env" ]; then
        ENV_FILE="config/.env"
    else
        ENV_FILE="../.env"
    fi
    
    if grep -q "MAILCHIMP_API_KEY" "$ENV_FILE" || grep -q "CONVERTKIT_API_KEY" "$ENV_FILE"; then
        echo -e "${GREEN}✓${NC} Email marketing API keys configured"
    else
        echo -e "${YELLOW}⚠${NC} Email marketing API keys not found in .env"
        echo "    Optional: Add MAILCHIMP_API_KEY or CONVERTKIT_API_KEY for list integration"
        ((WARNINGS++))
    fi
else
    echo -e "${YELLOW}⚠${NC} .env file not found"
    echo "    Using default environment values"
    ((WARNINGS++))
fi

# 9. Check logs directory
echo ""
echo "9. Checking logs directory..."
if [ -d "logs" ]; then
    if [ -w "logs" ]; then
        echo -e "${GREEN}✓${NC} logs/ directory exists and is writable"
    else
        echo -e "${YELLOW}⚠${NC} logs/ directory exists but is not writable"
        echo "    Run: chmod 750 logs"
        ((WARNINGS++))
    fi
else
    echo -e "${YELLOW}⚠${NC} logs/ directory not found (will be created automatically)"
fi

# 10. Check downloads .htaccess
echo ""
echo "10. Checking downloads .htaccess..."
if [ -f "downloads/.htaccess" ]; then
    echo -e "${GREEN}✓${NC} downloads/.htaccess exists"
    if grep -q "Content-Disposition" downloads/.htaccess; then
        echo -e "${GREEN}✓${NC} Content-Disposition headers configured"
    else
        echo -e "${YELLOW}⚠${NC} Content-Disposition headers not found"
        ((WARNINGS++))
    fi
else
    echo -e "${RED}✗${NC} downloads/.htaccess is missing"
    ((ERRORS++))
fi

# Summary
echo ""
echo "=============================================="
echo "Verification Summary"
echo "=============================================="
if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✓ All checks passed!${NC}"
    echo ""
    echo "Your implementation is complete and ready for testing."
    echo ""
    echo "Next steps:"
    echo "1. Update GTM container ID in index.php (if not done)"
    echo "2. Configure email marketing API keys in .env (optional)"
    echo "3. Test lead capture form submission"
    echo "4. Verify conversion tracking in browser console"
    echo "5. Set up GTM triggers and tags per docs/GTM_SETUP_QUICK_REFERENCE.md"
elif [ $ERRORS -eq 0 ]; then
    echo -e "${YELLOW}⚠ $WARNINGS warning(s) found${NC}"
    echo ""
    echo "Your implementation is functional but has some optional improvements."
    echo "Review warnings above for details."
else
    echo -e "${RED}✗ $ERRORS error(s) and $WARNINGS warning(s) found${NC}"
    echo ""
    echo "Please fix the errors above before proceeding."
    exit 1
fi

echo ""
echo "Documentation:"
echo "- Implementation Summary: IMPLEMENTATION_SUMMARY.md"
echo "- GTM Setup Guide: docs/GTM_SETUP_QUICK_REFERENCE.md"
echo "- Conversion Tracking: docs/CONVERSION_TRACKING_SETUP.md"
echo ""
