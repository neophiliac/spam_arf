# rc_send_arf
Send the current email in ARF format

## Testing

### Test Plan

**1. Plugin loads correctly**
- Enable the plugin in Roundcube's `config.inc.php`
- Open the mail view and confirm the "Report Spam" button appears in the toolbar with the icon visible
- Repeat with both the `elastic` and `classic` skins active

**2. Confirmation dialog**
- Select a message and click "Report Spam"
- Confirm that a dialog appears with the expected prompt text
- Click Cancel and verify no report is sent and the UI returns to its normal state

**3. Successful report submission**
- Configure `$report_to` in `spam_arf.php` to a mailbox you control
- Select a message, click "Report Spam", and confirm the dialog
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

**SMTP authentication / relay restrictions** — `deliver_message()` uses Roundcube's configured outbound transport. If the abuse address is on an external domain and the SMTP relay blocks outbound mail to non-local recipients, delivery will silently fail. Ensure the configured transport can reach the target address.

**Concurrent reports** — Clicking "Report Spam" multiple times in quick succession (e.g. via keyboard) before the confirmation dialog closes could queue duplicate reports. Verify the busy-lock set by `rcmail.set_busy()` prevents this.

**Missing or wrong skin directory** — If the active Roundcube skin is neither `elastic` nor `classic`, `local_skin_path()` will return a path with no CSS file, causing a 404 for the stylesheet. Test with a third-party skin and confirm the plugin degrades gracefully.
