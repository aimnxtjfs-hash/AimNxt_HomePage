<?php
/**
 * AimNxt — TeleCRM → Meta CAPI Bridge
 * File: capi_webhook.php
 *
 * Upload this file to your aimnxt.org hosting (e.g. public_html/capi/)
 * Webhook URL for TeleCRM: https://aimnxt.org/capi/capi_webhook.php
 *
 * Requirements: PHP 7.4+ with cURL enabled (standard on all shared hosting)
 */

// ── CONFIGURATION ─────────────────────────────────────────────────────────────
// Replace these values with your actual credentials before uploading

define('META_PIXEL_ID',      '740604474553620');
define('META_ACCESS_TOKEN',  'EAAErjMD46GIBQZCxpFS5YjqoDtXjkyguE8a7SEmaNA0VSrkqY24IlvDcSxkBVCzMdNVpmidiN9bpn3CXUPzBtcsX7HmFJXgEZAoh7hzEr6uotU94ZAj50MiaZCDGQFaNeZBu8Raw2MyHYWeCRX2GbQoY9HfNpiABdFnxrYIykLTA1ZA81msCZAKC8wY20ZAxW8sRnAZDZD');
define('META_API_VERSION',   'v18.0');
define('WEBHOOK_SECRET',     'aimnxt_secret_2026');  // Must match TeleCRM header

// ── SECURITY — Verify request is from TeleCRM ────────────────────────────────
$incomingSecret = isset($_SERVER['HTTP_X_WEBHOOK_SECRET'])
    ? $_SERVER['HTTP_X_WEBHOOK_SECRET']
    : '';

if ($incomingSecret !== WEBHOOK_SECRET) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// ── READ INCOMING PAYLOAD ────────────────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$body    = json_decode($rawBody, true);

if (!$body || !isset($body['event_type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

// ── HELPERS ──────────────────────────────────────────────────────────────────

/**
 * SHA256 hash — required by Meta for all PII fields
 */
function hashValue($value) {
    if (empty($value)) return null;
    return hash('sha256', strtolower(trim((string)$value)));
}

/**
 * Normalise Indian phone number
 * Strips spaces/dashes, ensures 91 country code (no + before hashing)
 */
function normalisePhone($phone) {
    if (empty($phone)) return null;
    $p = preg_replace('/[\s\-\(\)\+]/', '', (string)$phone);
    $p = ltrim($p, '0');                          // remove leading zeros
    if (strlen($p) === 10) $p = '91' . $p;        // 10-digit → add country code
    if (substr($p, 0, 2) !== '91') $p = '91' . $p;
    return $p;                                    // no + before hashing
}

/**
 * Map TeleCRM event_type → Meta event_name
 */
function mapEvent($eventType) {
    $map = [
        'Interested'    => 'QualifiedLead',   // Custom event — quality signal
        'Demo Attended' => 'DemoAttended',    // Custom event — mid-funnel signal
        'Won'      => 'CompleteRegistration',        // Standard event — highest value
    ];
    return isset($map[$eventType]) ? $map[$eventType] : null;
}

/**
 * Course fee lookup — used for Purchase event value (INR)
 */
function getCourseValue($course) {
    $fees = [
        'networking' => 30000,
        'ccna'       => 20000,
        'ccnp'       => 20000,
        'cyber'      => 30000,
        'cissp'      => 50000,
        'java'       => 25000,
        'python'     => 25000,
        'devops'     => 30000,
        'aws'        => 30000,
        'azure'      => 30000,
        'datascience'=> 45000,
        'data'       => 45000,
        'digital'    => 25000,
    ];
    if (empty($course)) return 30000;
    $key = strtolower((string)$course);
    foreach ($fees as $keyword => $value) {
        if (strpos($key, $keyword) !== false) return $value;
    }
    return 30000; // default fallback
}

// ── MAP EVENT ────────────────────────────────────────────────────────────────
$metaEventName = mapEvent($body['event_type']);

if (!$metaEventName) {
    // Event type not mapped — ignore silently
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'reason' => 'no_mapping']);
    exit;
}

// ── BUILD user_data WITH HASHED PII ─────────────────────────────────────────
$phone     = normalisePhone(isset($body['phone']) ? $body['phone'] : null);
$nameParts = isset($body['name']) ? explode(' ', trim($body['name']), 2) : [];
$firstName = isset($nameParts[0]) ? $nameParts[0] : null;
$lastName  = isset($nameParts[1]) ? $nameParts[1] : null;

$userData = array_filter([
    'ph'      => $phone     ? hashValue($phone)                   : null,
    'em'      => isset($body['email']) ? hashValue($body['email']) : null,
    'fn'      => $firstName ? hashValue($firstName)               : null,
    'ln'      => $lastName  ? hashValue($lastName)                : null,
    'ct'      => isset($body['city'])  ? hashValue($body['city'])  : null,
    'zp'      => isset($body['state']) ? hashValue($body['state']) : null,
    'country' => hashValue('in'),    // India — always send
]);

// ── BUILD EVENT PAYLOAD ──────────────────────────────────────────────────────
$eventTime = isset($body['updated_at'])
    ? strtotime($body['updated_at'])
    : time();

// Unique event_id prevents duplicate counting
$eventId = ($body['lead_id'] ?? 'unknown')
         . '_' . $body['event_type']
         . '_' . $eventTime;

$customData = [
    'content_name'     => $body['course'] ?? 'AimNxt Course',
    'content_category' => 'EdTech',
];

// Add value + currency only for Purchase (Enrolled) events
if ($metaEventName === 'Purchase') {
    $customData['value']    = getCourseValue($body['course'] ?? '');
    $customData['currency'] = 'INR';
}

$event = [
    'event_name'    => $metaEventName,
    'event_time'    => $eventTime,
    'action_source' => 'system_generated',
    'event_id'      => $eventId,
    'user_data'     => $userData,
    'custom_data'   => $customData,
];

// ── TESTING ONLY ─────────────────────────────────────────────────────────────
// Uncomment the line below and replace with your code from Events Manager
// REMOVE this line completely before going live
// $event['test_event_code'] = 'TEST71511';
// ─────────────────────────────────────────────────────────────────────────────

// ── POST TO META CAPI ────────────────────────────────────────────────────────
$endpoint = sprintf(
    'https://graph.facebook.com/%s/%s/events?access_token=%s',
    META_API_VERSION,
    META_PIXEL_ID,
    META_ACCESS_TOKEN
);

$payload = json_encode(['data' => [$event]]);

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 10,
]);

$response   = curl_exec($ch);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError  = curl_error($ch);
curl_close($ch);

// ── LOG RESULT ───────────────────────────────────────────────────────────────
$logLine = sprintf(
    "[%s] event=%s lead=%s http=%d response=%s\n",
    date('Y-m-d H:i:s'),
    $metaEventName,
    $body['lead_id'] ?? 'unknown',
    $httpCode,
    $response
);
file_put_contents(__DIR__ . '/capi_log.txt', $logLine, FILE_APPEND);

// ── RESPOND ──────────────────────────────────────────────────────────────────
if ($curlError || $httpCode >= 400) {
    http_response_code(500);
    echo json_encode([
        'status'    => 'error',
        'curl_error'=> $curlError,
        'meta_response' => json_decode($response, true),
    ]);
} else {
    http_response_code(200);
    echo json_encode([
        'status'        => 'sent',
        'event'         => $metaEventName,
        'lead_id'       => $body['lead_id'] ?? 'unknown',
        'meta_response' => json_decode($response, true),
    ]);
}
