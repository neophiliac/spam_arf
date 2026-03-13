# rc_send_arf
Send the current email in ARF format

## Installation

### Requirements

- Roundcube 1.5 or later
- PHP mail delivery configured and working in Roundcube

### Via Composer (recommended)

From your Roundcube root directory:

```bash
composer require merlot/spam_arf
```

This will install the plugin into `plugins/spam_arf/` and register it automatically.

### Manual installation

1. Copy (or clone) this repository into your Roundcube plugins directory:

   ```bash
   git clone https://github.com/neophiliac/spam_arf plugins/spam_arf
   ```

2. Open `config/config.inc.php` (or `config/main.inc.php` on older installs) and add `spam_arf` to the plugins array:

   ```php
   $config['plugins'] = ['spam_arf'];
   ```

### Skin support

The plugin includes stylesheets for the `elastic` and `classic` Roundcube skins. No additional steps are needed for these skins. If you use a third-party skin, copy one of the existing skin directories as a starting point and place it at `plugins/spam_arf/skins/<skin-name>/`.

---

## Testing

### Test Plan

**1. Plugin loads correctly**
- Enable the plugin in Roundcube's `config.inc.php`
- Open the mail view and confirm the "Report Spam" button appears in the toolbar with the icon visible
- Repeat with both the `elastic` and `classic` skins active

**2. Report dialog**
- Select a message and click "Report Spam"
- Confirm that a dialog appears with the confirmation text and a "Report to address" input field
- Click Cancel and verify no report is sent and the UI returns to its normal state
- Reopen the dialog, leave the address blank, click Send Report, and confirm the dialog stays open without submitting
- Enter an invalid address (e.g. no `@`) and confirm the dialog stays open

**3. Successful report submission**
- Select a message, click "Report Spam", and enter the abuse contact address for the sending host (e.g. `abuse@sendinghost.example`)
- Verify the success notification appears in the UI
- Verify the report arrives at the target address as a `multipart/report` message with the correct MIME parts:
  - A `text/plain` human-readable part
  - A `message/feedback-report` part with `Feedback-Type: abuse`
  - A `message/rfc822` part containing the original message verbatim

**4. Delivery failure handling**
- Temporarily misconfigure the outgoing mail settings so delivery fails
- Attempt to send a report and confirm the error notification is displayed

**5. Skin appearance**
- Confirm the icon renders correctly at toolbar size in each skin
- Confirm the hover highlight appears on the button in both skins

---

### Edge Cases

**No message selected** — The JS guard (`if (!uid) return`) silently does nothing when no message is selected or when multiple messages are selected. Verify the button does not fire when the selection is empty, and consider whether a user-visible message would be appropriate.

**Very large messages** — `get_raw_headers()` and `get_raw_body()` load the full message into memory. Extremely large messages (e.g. with large attachments) could cause memory exhaustion. Test with a message near the PHP `memory_limit` to characterise the behaviour.

**Messages with no body** — Some malformed or DSN messages may have headers but an empty body. Confirm the ARF report is still well-formed and deliverable in this case.

**Special characters in headers** — If the original message contains non-ASCII or improperly encoded headers, verify they are passed through intact into the `message/rfc822` part without corruption.

**SMTP authentication / relay restrictions** — `deliver_message()` uses Roundcube's configured outbound transport. Because reports are always sent to external abuse contacts, the SMTP relay must permit outbound delivery to arbitrary external domains. If it does not, delivery will fail silently with no indication of the real cause.

**Concurrent reports** — Clicking "Report Spam" multiple times in quick succession (e.g. via keyboard) before the confirmation dialog closes could queue duplicate reports. Verify the busy-lock set by `rcmail.set_busy()` prevents this.

**Missing or wrong skin directory** — If the active Roundcube skin is neither `elastic` nor `classic`, `local_skin_path()` will return a path with no CSS file, causing a 404 for the stylesheet. Test with a third-party skin and confirm the plugin degrades gracefully.
