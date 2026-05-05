# Message Service Documentation

## Overview

The Message Service is a comprehensive messaging system for the CHIBO BRAND Sales application that handles three main types of communication:

1. **Contact Messages** - Messages from website visitors/customers
2. **Message Templates** - Pre-defined SMS message templates for bulk messaging
3. **Notifications** - In-app notifications for users

This system integrates SMS capabilities via Beem Africa API, email delivery through Laravel queues, and in-app notification management.

---

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                    Message Service                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────────┐  ┌──────────────────┐               │
│  │ Contact Messages │  │ Message Templates│               │
│  │ (Incoming)       │  │ (Bulk SMS)       │               │
│  └────────┬─────────┘  └────────┬─────────┘               │
│           │                     │                         │
│           ├─────────────────────┼──────────┐              │
│           ▼                     ▼          ▼              │
│      ┌─────────────────────────────────────────┐          │
│      │   SmsApiService (Beem Africa)           │          │
│      │   - Send SMS Messages                   │          │
│      │   - Phone Number Formatting             │          │
│      │   - Error Handling & Retry Logic        │          │
│      └────────────┬────────────────────────────┘          │
│                   │                                       │
│                   ▼                                       │
│      ┌──────────────────────────┐                        │
│      │   Queue Jobs             │                        │
│      │ - SendContactReplyEmail   │                        │
│      └──────────────────────────┘                        │
│           │                     │                        │
│           ▼                     ▼                        │
│      ┌──────────────┐    ┌──────────────┐              │
│      │ Email        │    │ Notifications│              │
│      │ Notification │    │ System       │              │
│      └──────────────┘    └──────────────┘              │
└─────────────────────────────────────────────────────────────┘
```

---

## Database Schema

### 1. Contact Messages Table
```sql
CREATE TABLE contact_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(255) NULLABLE,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    admin_reply TEXT NULLABLE,
    replied_at TIMESTAMP NULLABLE,
    replied_by BIGINT UNSIGNED NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX (status),
    INDEX (created_at)
);
```

**Fields:**
- `id` - Unique message identifier
- `name` - Contact person's name
- `email` - Contact person's email (required for reply)
- `phone` - Contact person's phone (optional)
- `subject` - Message subject
- `message` - Main message content
- `status` - Message status (new/read/replied)
- `admin_reply` - Admin's response to the message
- `replied_at` - Timestamp when message was replied to
- `replied_by` - User ID of admin who replied

### 2. Message Templates Table
```sql
CREATE TABLE message_templates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(255) NULLABLE,
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```

**Fields:**
- `id` - Unique template identifier
- `title` - Template name
- `content` - SMS message content
- `category` - Template organization category
- `is_active` - Whether template is available for use
- `created_by` - User ID of template creator

### 3. Notifications Table
```sql
CREATE TABLE notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('read', 'unread') DEFAULT 'unread',
    related_id BIGINT UNSIGNED NULLABLE,
    related_type VARCHAR(255) NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX (user_id, status),
    INDEX (type),
    INDEX (related_type, related_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Fields:**
- `id` - Unique notification identifier
- `user_id` - Target user ID
- `type` - Notification type/category
- `message` - Notification content
- `status` - Read/unread status
- `related_id` - ID of related entity (e.g., message ID)
- `related_type` - Type of related entity (e.g., ContactMessage)

---

## Models

### ContactMessage Model

**Location:** `app/Models/ContactMessage.php`

```php
class ContactMessage extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message',
        'status', 'admin_reply', 'replied_at', 'replied_by'
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function repliedBy() // User who replied
    public function scopeNew($query) // Filter new messages
    public function scopeRead($query) // Filter read messages
}
```

**Key Methods:**
- `repliedBy()` - Returns the User model of who replied
- `scopeNew()` - Query scope for unread messages
- `scopeRead()` - Query scope for read messages

**Usage Examples:**
```php
// Get all new messages
$newMessages = ContactMessage::new()->get();

// Get message with replier info
$message = ContactMessage::with('repliedBy')->find($id);

// Get read messages
$readMessages = ContactMessage::read()->get();
```

### MessageTemplate Model

**Location:** `app/Models/MessageTemplate.php`

```php
class MessageTemplate extends Model
{
    protected $fillable = [
        'title', 'content', 'category', 'is_active', 'created_by'
    ];

    // Relationships
    public function creator() // User who created template
    public function scopeActive($query) // Filter active templates
}
```

**Key Methods:**
- `creator()` - Returns the User model who created the template
- `scopeActive()` - Query scope for active templates only

**Usage Examples:**
```php
// Get all active templates
$templates = MessageTemplate::active()->get();

// Get template with creator info
$template = MessageTemplate::with('creator')->find($id);
```

### Notification Model

**Location:** `app/Models/Notification.php`

```php
class Notification extends Model
{
    protected $fillable = [
        'user_id', 'sender_id', 'type', 'message',
        'status', 'related_id', 'related_type'
    ];

    // Relationships
    public function user() // Recipient user
    public function sender() // Sender user
    public function scopeUnread($query) // Filter unread notifications
    public function scopeRead($query) // Filter read notifications
}
```

**Key Methods:**
- `user()` - Returns the recipient User model
- `sender()` - Returns the sender User model
- `scopeUnread()` - Query scope for unread notifications
- `scopeRead()` - Query scope for read notifications

**Usage Examples:**
```php
// Get unread notifications for user
$unread = Notification::where('user_id', $userId)->unread()->get();

// Get notification count
$count = Notification::where('user_id', $userId)->unread()->count();
```

---

## Controllers

### ContactMessageController

**Location:** `app/Http/Controllers/Admin/ContactMessageController.php`

**Endpoints:**

#### `index(Request $request)`
Display paginated list of contact messages with role-based filtering.

**Query Parameters:**
- `search` - Search in name, email, subject, message
- `status` - Filter by message status (new/read/replied)

**Features:**
- Role-based access: Admin/super_admin/manager see all; receptionist/designer see only their email/phone matches
- Automatic "new" message counting
- Pagination (20 per page)

**Response:**
```php
return view('admin.contact-messages.index', [
    'messages' => Paginator,
    'newCount' => int
]);
```

#### `show($id)`
Display a single contact message details.

**Features:**
- Role-based access control
- Auto-marks message as "read" if it was "new"
- Shows admin reply if exists
- Shows replier information

#### `update(Request $request, $id)` - Update Message Status
Update message with admin reply (internal note).

**Validation:**
```php
'admin_reply' => 'required|string|max:5000'
```

**Response:**
- Marks status as 'replied'
- Records reply time and replier
- Returns: 302 redirect with success message

#### `sendEmailReply(Request $request, $id)` - Send Email to Customer
Send async email reply to customer using queue system.

**Validation:**
```php
'email_reply' => 'required|string|max:5000',
'email_subject' => 'required|string|max:255'
```

**Process:**
1. Saves reply to database (atomic operation)
2. Dispatches `SendContactReplyEmail` job to queue
3. Returns success with background processing confirmation

**Response:**
- 302 redirect with success message: "Reply saved! Email is being sent in the background"
- Email sent asynchronously

### MessageTemplateController

**Location:** `app/Http/Controllers/MessageTemplateController.php`

**Endpoints:**

#### `index(Request $request)`
Display all message templates.

**Response:**
```php
return view('admin.message-templates.index', [
    'templates' => Collection,
    'customers' => Collection
]);
```

#### `store(Request $request)`
Create a new message template.

**Validation:**
```php
'title' => 'required|string|max:255',
'content' => 'required|string',
'category' => 'nullable|string|max:255'
```

**Process:**
- Creates template as active by default
- Associates with authenticated user

#### `update(Request $request, $id)`
Update existing message template.

**Validation:**
Same as store - title, content, category

**Mutable Fields:**
- title, content, category, is_active

#### `destroy($id)`
Delete a message template.

#### `send(Request $request)` - Send Template via SMS
Send template SMS to customers (bulk messaging).

**Validation:**
```php
'recipient_type' => 'required|in:selected,assigned_leads,all_leads,all_customers',
'template_id' => 'nullable|exists:message_templates,id',
'custom_content' => 'nullable|string',
'customer_ids' => 'required_if:recipient_type,selected|array'
```

**Recipient Types:**
1. **selected** - Send to selected customer IDs
2. **assigned_leads** - Send to customers matching leads assigned to current user
3. **all_leads** - Send to customers matching any lead
4. **all_customers** - Send to all customers with phone numbers

**Process:**
1. Retrieves message content (template or custom)
2. Gets recipient list based on type
3. Sends SMS to each customer
4. Returns summary with success/failure counts

**Response:**
```php
// Success
'success', "Message sent to {$count} customer(s)"

// Partial
'success', "Sent to {$success} customers. Failed for {$failed}"
'error_list', [...error details...]

// Failure
'error', "Failed to send messages."
'error_list', [...error details...]
```

---

## Services

### SmsApiService

**Location:** `app/Services/SmsApiService.php`

**Purpose:** Handle all SMS sending via Beem Africa API with comprehensive error handling and multiple authentication format support.

#### Constructor Configuration

```php
public function __construct(
    $apiKey = null,
    $apiSecret = null,
    $senderId = null,
    $apiUrl = null
)
```

**Configuration Sources (Priority Order):**
1. Direct file read from `.env` (production)
2. `env()` helper
3. `config()` helper
4. Fallback defaults

**Environment Variables:**
```env
SMS_API_KEY=2b5add88144ffb3b          # Beem Africa API Key
SMS_API_SECRET=your_secret_key        # Beem Africa Secret Key
SMS_API_URL=https://apisms.beem.africa/v1/send
SMS_SENDER_ID=CHIBOBRAND              # Approved sender ID
```

#### `sendSMS($to, $message): array`

Send an SMS message to a phone number.

**Parameters:**
- `$to` (string) - Phone number (auto-formatted to international format)
- `$message` (string) - SMS message content

**Returns:**
```php
[
    'success' => bool,
    'message' => string,
    'data' => array|null // API response data if successful
]
```

**Error Handling:**
- Validates API credentials before sending
- Tries multiple secret key formats (raw, base64-decoded, URL-decoded)
- 401 errors: Provides detailed credential troubleshooting
- Non-401 errors: Returns specific error messages
- Logs detailed diagnostics for debugging

**Request Format (Beem Africa):**
```php
POST https://apisms.beem.africa/v1/send
Authorization: Basic base64(api_key:secret_key)
Content-Type: application/json

{
    "source_addr": "CHIBOBRAND",
    "schedule_time": "",
    "encoding": 0,
    "message": "Your message text",
    "recipients": [
        {
            "recipient_id": "1",
            "dest_addr": "+255XX XXXXXXX"
        }
    ]
}
```

#### `formatPhoneNumber($phone): string`

Format phone number to international format.

**Supports:**
- Local format: 0654123456 → +255654123456
- International format: +255654123456 (passed through)
- Parentheses: (065) 412-3456 (cleaned)
- Spaces and dashes: 065 412-3456 (cleaned)

#### Internal Methods

**`getEnvOrConfig($envKey, $configKey, $default): mixed`**
- Reads environment/configuration values with multiple fallback strategies
- Cleans values of quotes and non-printable characters
- Logs diagnostic information
- Handles config caching in production

**Key Features:**
```php
private function getEnvOrConfig($envKey, $configKey, $default = null)
{
    // Strategy 1: Direct .env file read (production/cached)
    // Strategy 2: env() helper
    // Strategy 3: config() helper
    // Strategy 4: Default fallback
}
```

---

## Queue Jobs

### SendContactReplyEmail Job

**Location:** `app/Jobs/SendContactReplyEmail.php`

**Purpose:** Asynchronously send email replies to contact message submissions.

**Class Definition:**
```php
class SendContactReplyEmail implements ShouldQueue
{
    public $message;        // ContactMessage instance
    public $emailSubject;   // Email subject line
    public $emailReply;     // Reply content
    public $adminName;      // Admin name for signature
}
```

#### Constructor
```php
public function __construct(
    ContactMessage $message,
    $emailSubject,
    $emailReply,
    $adminName
)
```

#### `handle(): void`
Execute the email sending job.

**Process:**
1. Validates message and email address exist
2. Sends email using `contact-reply` template
3. Logs success/failure
4. Throws exception on failure (queues for retry)

**Email Template Variables:**
```php
[
    'customerName' => $message->name,
    'originalMessage' => $message->message,
    'originalSubject' => $message->subject,
    'replyMessage' => $emailReply,
    'adminName' => $adminName
]
```

**Email Subject Template:** User-provided subject

**Email Template:** `emails.contact-reply`

**Error Handling:**
- Catches exceptions
- Logs detailed error information
- Re-throws for queue retry mechanism

**Configuration:**
- Uses `config('mail.from.address')` and `config('mail.from.name')`
- Default sender: "CHIBO BRAND"
- Queue: Default queue configured in app

**Logging:**
```php
// Success
Log::info('Contact reply email sent successfully', ['message_id' => $id])

// Failure
Log::error('Failed to send contact reply email', [
    'message_id' => $id,
    'error' => $exception->getMessage()
])
```

**Dispatch Usage:**
```php
SendContactReplyEmail::dispatch(
    $message,
    'Re: Your Inquiry',
    'Thank you for contacting us...',
    auth()->user()->name
);
```

---

## Email Templates

### Contact Reply Email Template

**Location:** `resources/views/emails/contact-reply.blade.php`

**Design:**
- Responsive HTML email layout
- CHIBO BRAND branding (red/white theme)
- Professional styling

**Template Variables:**
- `$customerName` - Recipient's name
- `$originalSubject` - Original inquiry subject
- `$originalMessage` - Original inquiry content
- `$replyMessage` - Admin's reply
- `$adminName` - Replying admin's name

**Output:**
- Professionally styled HTML email
- Mobile responsive
- Includes footer with contact information

---

## Routes

### Contact Messages Routes

```php
// List all messages (with filtering)
GET /admin/contact-messages
    → ContactMessageController@index
    → Name: contact-messages.index

// Show single message details
GET /admin/contact-messages/{id}
    → ContactMessageController@show
    → Name: contact-messages.show

// Update message (add internal reply)
PUT /admin/contact-messages/{id}
    → ContactMessageController@update
    → Name: contact-messages.update

// Delete message
DELETE /admin/contact-messages/{id}
    → ContactMessageController@destroy
    → Name: contact-messages.destroy

// Send email reply to customer
POST /admin/contact-messages/{id}/send-email
    → ContactMessageController@sendEmailReply
    → Name: contact-messages.send-email
```

### Message Templates Routes

```php
// List all templates
GET /admin/message-templates
    → MessageTemplateController@index
    → Name: message-templates.index

// Create new template (POST)
POST /admin/message-templates
    → MessageTemplateController@store
    → Name: message-templates.store

// Update existing template
PUT /admin/message-templates/{id}
    → MessageTemplateController@update
    → Name: message-templates.update

// Delete template
DELETE /admin/message-templates/{id}
    → MessageTemplateController@destroy
    → Name: message-templates.destroy

// Send template SMS to customers
POST /admin/message-templates/send
    → MessageTemplateController@send
    → Name: message-templates.send
```

### Notifications Routes

```php
// List notifications
GET /admin/notifications
    → AdminController@notifications
    → Name: notifications.index

// Get unread count
GET /admin/notifications/refresh-count
    → AdminController@getNotificationCount
    → Name: notifications.refresh-count

// Show single notification
GET /admin/notifications/{notification}
    → AdminController@showNotification
    → Name: notifications.show

// Mark notification as read
POST /admin/notifications/{notification}/read
    → AdminController@markNotificationRead
    → Name: notifications.read

// Mark all notifications as read
POST /admin/notifications/mark-all-read
    → AdminController@markAllNotificationsRead
    → Name: notifications.mark-all-read

// Delete notification
DELETE /admin/notifications/{notification}
    → AdminController@deleteNotification
    → Name: notifications.delete
```

---

## Authentication & Authorization

### Role-Based Access Control

**Contact Message Access:**

| Role | Access |
|------|--------|
| admin | All messages |
| super_admin | All messages |
| manager | All messages |
| receptionist | Messages matching their email/phone |
| designer | Messages matching their email/phone |
| operator | Messages matching their email/phone |
| customer | None |

**Phone Matching Logic:**
- Normalizes phone numbers (removes special characters)
- Matches by partial or full phone number
- Compares both directions (user phone contains message phone, or vice versa)

### Template Permissions

- **Creation:** All authenticated users
- **Editing:** Any authenticated user (no ownership restriction in current implementation)
- **Deletion:** Any authenticated user
- **Sending:** Authenticated users only

### Notification Access

- Users can only access their own notifications
- Enforced via `user_id` foreign key constraint

---

## Common Use Cases & Examples

### 1. Handling Incoming Contact Messages

**Scenario:** Customer submits contact form

**Flow:**
```php
// 1. Message received (typically from web form submission)
$message = ContactMessage::create([
    'name' => $request->name,
    'email' => $request->email,
    'phone' => $request->phone,
    'subject' => $request->subject,
    'message' => $request->message,
    'status' => 'new'
]);

// 2. Admin views in dashboard (admin/contact-messages)
// Status: "new" - highlighted for attention

// 3. Admin clicks to view details
GET /admin/contact-messages/{id}
// - Message auto-marked as "read"
// - Display form for reply

// 4. Admin can add internal reply (won't email customer)
PUT /admin/contact-messages/{id}
- admin_reply: "Forwarded to sales team"

// 5. Admin can optionally send email to customer
POST /admin/contact-messages/{id}/send-email
- email_subject: "Re: Your Inquiry"
- email_reply: "Thank you for your interest..."
// - Job queued for background sending
// - Email sent asynchronously
```

### 2. Sending Bulk SMS with Templates

**Scenario:** Send promotional SMS to customers

**Setup Steps:**

**Step 1: Create Template**
```php
POST /admin/message-templates
{
    "title": "Spring Sale",
    "content": "🎉 CHIBO BRAND Spring Sale! Get 30% off all items. Valid until March 31. Shop now!",
    "category": "promotions",
    "is_active": true
}
```

**Step 2: Send to Customers**
```php
POST /admin/message-templates/send
{
    "recipient_type": "selected",
    "template_id": 1,
    "customer_ids": [1, 2, 3, 5]
}

// Alternative: Send to all customers
POST /admin/message-templates/send
{
    "recipient_type": "all_customers",
    "template_id": 1
}

// Alternative: Custom message instead of template
POST /admin/message-templates/send
{
    "recipient_type": "selected",
    "custom_content": "Welcome back! New products just arrived.",
    "customer_ids": [1, 2]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Message sent to 4 customer(s).",
    "details": {
        "success": 4,
        "failed": 0
    }
}
```

### 3. Creating System Notifications

**Scenario:** Notify user of important event

**Usage:**
```php
// In your service/controller
Notification::create([
    'user_id' => $userId,
    'type' => 'order_completed',
    'message' => 'Order #1234 has been completed',
    'status' => 'unread',
    'related_id' => 1234,
    'related_type' => 'Order'
]);

// Optional: Add sender (if from another user)
Notification::create([
    'user_id' => $recipientId,
    'sender_id' => $senderUserId,
    'type' => 'message_received',
    'message' => 'You have a new message from ' . $sender->name,
    'status' => 'unread',
    'related_id' => $messageId,
    'related_type' => 'ContactMessage'
]);
```

**User Views Notifications:**
```php
// Get unread count
$unreadCount = Notification::where('user_id', auth()->id())
    ->unread()->count();

// Get notifications
$notifications = Notification::where('user_id', auth()->id())
    ->latest()->paginate(15);

// Mark as read
POST /admin/notifications/{id}/read

// Mark all as read
POST /admin/notifications/mark-all-read

// Delete notification
DELETE /admin/notifications/{id}
```

---

## Error Handling & Troubleshooting

### SMS Sending Errors

#### Error: "SMS API Key not configured"
```
Log: Beem Africa SMS API Key not configured
```
**Solution:**
1. Check `.env` file has `SMS_API_KEY` set
2. Verify value doesn't have extra quotes or spaces
3. Ensure value matches Beem Africa dashboard exactly
4. Check file permissions are readable

#### Error: "SMS API Secret Key not configured"
```
Log: Beem Africa SMS API Secret Key not configured
```
**Solution:**
1. Set `SMS_API_SECRET` in `.env`
2. Try copying directly from Beem Africa dashboard
3. If 88 characters, likely base64 encoded - service will auto-decode
4. Remove any quotes or newlines

#### Error: "Invalid Authentication Parameters" (401)
```
Log: Invalid Authentication Parameters after trying all formats
```
**Causes:**
- API credentials don't match dashboard
- Secret key format incorrect
- Sender ID not approved
- Credentials lack SMS credits

**Solutions:**
1. Copy credentials directly from Beem Africa dashboard
2. Check sender ID is approved and matches exactly (case-sensitive)
3. Verify SMS credits available
4. Check for hidden characters in `.env`:
   ```bash
   # Check for hidden characters
   xxd .env | grep SMS_API
   
   # Or use cat to view directly
   cat .env | grep SMS_API
   ```
5. Try removing quotes from `.env` values
6. If secret is base64, service will auto-decode it

#### Error: "No valid recipients found"
```
Log: No valid recipients found
```
**Causes:**
- No customers have phone numbers
- Recipient type selection yielded no results
- Customer list is filtered out

**Solutions:**
1. Verify customers have phone numbers in database
2. Check recipient type selection
3. For "assigned_leads": Verify leads are assigned to user

### Email Sending Errors

#### Error: "Failed to send contact reply email"
```
Log: Failed to send contact reply email
```
**Causes:**
- Mail configuration incorrect
- Queue system not running
- Invalid email template

**Solutions:**
1. Check `config/mail.php` is properly configured
2. Ensure queue worker is running: `php artisan queue:work`
3. Verify email template exists: `resources/views/emails/contact-reply.blade.php`
4. Check Laravel logs for detailed error

#### Message appears saved but email never sent
**Cause:** Queue job failed silently

**Solution:**
```bash
# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry {id}

# Clear all failed jobs
php artisan queue:flush
```

### Access Control Errors

#### Error: "You do not have access to this message"
**Cause:** Role-based filtering preventing access

**Reasons:**
- Non-admin trying to access message not matching their email/phone
- Phone number not properly normalized

**Solutions:**
1. Check user role and email/phone in users table
2. Ensure message email/phone matches user info
3. Normalize phone numbers:
   ```php
   // Remove all non-digit characters except +
   $normalized = preg_replace('/[^\d+]/', '', '(065) 412-3456');
   // Result: 065412345
   ```

---

## Performance Considerations

### SMS Sending Optimization

1. **Batch Processing:** Use `recipient_type: 'all_customers'` instead of multiple single sends
2. **Async**: SendContactReplyEmail already uses queues automatically
3. **Phone Validation**: Consider adding phone validation before bulk sending
4. **Rate Limiting**: Beem Africa may have rate limits - no local throttling implemented

### Database Optimization

1. **Contact Messages:** Indexed on `status` and `created_at` for filtering
2. **Notifications:** Indexed on `(user_id, status)` and `type`
3. **Message Templates:** No search-heavy usage - consider adding index if filtering becomes common

### Recommended Indexes

```sql
-- Already present
CREATE INDEX idx_contact_status ON contact_messages(status);
CREATE INDEX idx_contact_created ON contact_messages(created_at);

-- Add if needed
CREATE INDEX idx_notifications_user_status ON notifications(user_id, status);
CREATE INDEX idx_templates_active ON message_templates(is_active);
```

---

## Security Considerations

### API Credentials

1. **Never commit `.env`** - Add to `.gitignore`
2. **Rotate credentials regularly** -Changes in Beem Africa dashboard
3. **Clean environment values** - Service removes quotes/spaces automatically
4. **Production-specific handling** - Direct `.env` file read in production to bypass config cache

### Data Protection

1. **Contact Messages:** 
   - Contains personal/contact information
   - Restrict access by role
   - Consider audit logging for sensitive data

2. **Message Templates:**
   - No sensitive data typically
   - Template content should be reviewed before sending

3. **Notifications:**
   - User isolation via `user_id`
   - No sensitive data typically

### Email Sending

1. **Queue Processing:** 
   - Emails sent asynchronously - delay before delivery
   - Failed jobs stored and retriable
   - Consider rate limiting if integrating with external systems

2. **Reply Validation:**
   - Max 5000 characters per reply
   - HTML not stripped - potential for XSS if displayed in raw HTML
   - Consider sanitization in templates

---

## Testing

### Unit Testing SMS Service

```php
use App\Services\SmsApiService;

class SmsApiServiceTest extends TestCase
{
    public function test_send_sms_successfully()
    {
        $service = new SmsApiService();
        $result = $service->sendSMS('+255654123456', 'Test message');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
    }
    
    public function test_format_phone_number()
    {
        $service = new SmsApiService();
        
        // Test local format
        $this->assertEquals('+255654123456', $service->formatPhoneNumber('0654123456'));
        
        // Test international format
        $this->assertEquals('+255654123456', $service->formatPhoneNumber('+255654123456'));
    }
}
```

### Feature Testing Contact Messages

```php
class ContactMessageTest extends TestCase
{
    public function test_admin_can_see_all_messages()
    {
        $admin = User::factory()->admin()->create();
        $messages = ContactMessage::factory(5)->create();
        
        $response = $this->actingAs($admin)
            ->get(route('contact-messages.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('messages');
    }
    
    public function test_receptionist_only_sees_own_messages()
    {
        $receptionist = User::factory()
            ->create(['role' => 'receptionist', 'email' => 'test@example.com']);
        
        ContactMessage::factory()->create(['email' => 'test@example.com']);
        ContactMessage::factory()->create(['email' => 'other@example.com']);
        
        $response = $this->actingAs($receptionist)
            ->get(route('contact-messages.index'));
        
        $response->assertStatus(200);
        // Should only see 1 message
    }
}
```

---

## Configuration Files

### Services Configuration

**`config/services.php`:**
```php
'sms' => [
    'api_key' => env('SMS_API_KEY', ''),
    'api_secret' => env('SMS_API_SECRET', ''),
    'api_url' => env('SMS_API_URL', 'https://apisms.beem.africa/v1/send'),
    'sender_id' => env('SMS_SENDER_ID', 'CHIBOBRAND'),
],
```

### Mail Configuration

**`config/mail.php`:**
```php
'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
    'name' => env('MAIL_FROM_NAME', 'CHIBO BRAND'),
],
```

### Queue Configuration

**`config/queue.php`:**
```php
'default' => env('QUEUE_CONNECTION', 'sync'),

'connections' => [
    'sync' => [...], // For testing/development
    'database' => [...], // Recommended for production
    'redis' => [...], // Alternative for production
]
```

---

## Maintenance & Monitoring

### Regular Tasks

1. **Check Failed Jobs** (Weekly)
   ```bash
   php artisan queue:failed
   php artisan queue:retry all
   ```

2. **Archive Old Messages** (Monthly)
   ```php
   // Consider archiving messages older than 6 months
   ContactMessage::where('created_at', '<', now()->subMonths(6))
       ->whereIn('status', ['read', 'replied'])
       ->delete();
   ```

3. **Verify SMS Credits** (As Needed)
   - Check Beem Africa dashboard for remaining credits
   - Monitor SMS failure rates

4. **Review Failed Emails** (Weekly)
   - Check Laravel logs for email delivery failures
   - Monitor queue processing

### Monitoring & Logging

**Key Metrics:**
- SMS success rate
- Average email delivery time
- Failed job count
- Active message template usage

**Log Locations:**
- Laravel logs: `storage/logs/laravel-*.log`
- Queue logs: Check error_log in same directory

**Important Log Entries to Monitor:**
```
- "SMS sent successfully"
- "Failed to send contact reply email"
- "Invalid Authentication Parameters"
- "No valid recipients found"
```

---

## Extensions & Future Enhancements

### Potential Improvements

1. **Message Scheduling:**
   - Schedule messages for future delivery
   - Recurring message campaigns

2. **Message Templates Enhancement:**
   - Template variables ({{name}}, {{phone}})
   - Dynamic content injection
   - A/B testing support

3. **Notification Center:**
   - Notification grouping/aggregation
   - Notification read status tracking
   - Delete old notifications (auto-cleanup)

4. **Analytics & Reporting:**
   - SMS delivery reports
   - Message template usage analytics
   - Email open/click tracking

5. **Two-Way Messaging:**
   - SMS reply support
   - Conversation threading
   - Customer SMS to support

6. **Multi-Channel:**
   - WhatsApp integration
   - Telegram integration
   - Push notifications

7. **Bulk Operations:**
   - Bulk message deletion
   - Export messages to CSV
   - Import customer lists

---

## Quick Reference

### Key Files Structure

```
app/
├── Models/
│   ├── ContactMessage.php
│   ├── MessageTemplate.php
│   └── Notification.php
├── Http/Controllers/
│   ├── Admin/ContactMessageController.php
│   └── MessageTemplateController.php
├── Services/
│   └── SmsApiService.php
└── Jobs/
    └── SendContactReplyEmail.php

database/
├── migrations/
│   ├── *_create_contact_messages_table.php
│   ├── *_create_message_templates_table.php
│   ├── *_create_notifications_table.php
│   └── *_add_related_fields_to_notifications_table.php
└── schemas/ (optional)

resources/views/
├── emails/
│   └── contact-reply.blade.php
└── admin/
    ├── contact-messages/
    │   ├── index.blade.php
    │   └── show.blade.php
    └── message-templates/
        └── index.blade.php

routes/
└── web.php (message-related routes defined here)

config/
├── services.php (SMS configuration)
├── mail.php (Email configuration)
└── queue.php (Queue configuration)
```

### Essential Environment Variables

```env
# SMS Configuration (Beem Africa)
SMS_API_KEY=your_api_key_here
SMS_API_SECRET=your_secret_key_here
SMS_SENDER_ID=CHIBOBRAND
SMS_API_URL=https://apisms.beem.africa/v1/send

# Mail Configuration
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=585
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@chibobrand.com
MAIL_FROM_NAME="CHIBO BRAND"

# Queue Configuration
QUEUE_CONNECTION=database
```

---

## Support & Troubleshooting

For issues or questions:

1. **Check Laravel logs:** `storage/logs/laravel-*.log`
2. **Review database:** Check tables for data consistency
3. **Test SMS service:** 
   ```php
   Artisan::call('tinker');
   $service = app(SmsApiService::class);
   $result = $service->sendSMS('+255XXXXXXXXX', 'Test');
   dd($result);
   ```
4. **Check queue status:** `php artisan queue:failed`
5. **Verify credentials:** Double-check Beem Africa dashboard

---

**Documentation Version:** 1.0  
**Last Updated:** March 2026  
**Application:** CHIBO BRAND Sales System
