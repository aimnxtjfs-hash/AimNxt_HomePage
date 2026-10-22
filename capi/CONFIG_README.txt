# AimNxt CAPI — PHP Configuration Reference
# ─────────────────────────────────────────────────────────────────────────────
# PHP does not use .env files like Node.js.
# Instead, open capi_webhook.php and replace the values in the
# CONFIGURATION section at the top of the file (lines 14–17).
#
# ─────────────────────────────────────────────────────────────────────────────
# WHAT TO REPLACE IN capi_webhook.php
# ─────────────────────────────────────────────────────────────────────────────

META_PIXEL_ID      → Your Dataset ID from Meta Events Manager
                     Example: 1234567890123456

META_ACCESS_TOKEN  → The access token you generated in Events Manager
                     Example: EAABsbCS... (very long string)
                     WHERE: Events Manager → your Dataset →
                            Generate an access token → copy it

WEBHOOK_SECRET     → Any random string you invent yourself
                     Example: aimnxt_secret_2025_xyz
                     MUST match exactly what you put in TeleCRM
                     webhook header: X-Webhook-Secret

# ─────────────────────────────────────────────────────────────────────────────
# WHERE TO UPLOAD capi_webhook.php
# ─────────────────────────────────────────────────────────────────────────────

Option A (Recommended — clean URL):
  1. Create a folder called 'capi' inside public_html on your hosting
  2. Upload capi_webhook.php into that folder
  3. Your webhook URL = https://aimnxt.org/capi/capi_webhook.php

Option B (Root):
  1. Upload directly to public_html
  2. Your webhook URL = https://aimnxt.org/capi_webhook.php

# ─────────────────────────────────────────────────────────────────────────────
# REQUIREMENTS
# ─────────────────────────────────────────────────────────────────────────────

PHP Version : 7.4 or higher (standard on all modern shared hosting)
PHP cURL    : Must be enabled (enabled by default on GoDaddy, Hostinger etc.)
HTTPS       : Your domain must have SSL certificate (aimnxt.org already has it)

# ─────────────────────────────────────────────────────────────────────────────
# LOG FILE
# ─────────────────────────────────────────────────────────────────────────────

The PHP file automatically creates a log file called capi_log.txt
in the same folder as the PHP file.

Check this log to see every event that was sent to Meta.
Format: [timestamp] event=EventName lead=lead_id http=200 response={...}

# ─────────────────────────────────────────────────────────────────────────────
# TELECRM WEBHOOK BODY — paste this in TeleCRM workflow
# ─────────────────────────────────────────────────────────────────────────────

{
  "event_type": "INTERESTED",
  "lead_id": "{{lead.id}}",
  "name": "{{lead.name}}",
  "phone": "{{lead.phone}}",
  "email": "{{lead.email}}",
  "course": "{{lead.course}}",
  "city": "{{lead.city}}",
  "state": "{{lead.state}}",
  "updated_at": "{{lead.updated_at}}"
}

Change "event_type" value for each workflow:
  Interested workflow  → "INTERESTED"
  Demo Attended        → "DEMO_ATTENDED"
  Enrolled             → "ENROLLED"
