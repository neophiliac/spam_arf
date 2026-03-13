if (window.rcmail) {
    rcmail.addEventListener('init', function() {
        // Register the command so the UI knows it's active
        rcmail.register_command('plugin.send_arf', function() {
            var uid = rcmail.get_single_uid();
            if (!uid) return;

            // Use localized strings exported from PHP
            var message = rcmail.gettext('reportconfirm', 'spam_arf');
            var title = rcmail.gettext('reportspam', 'spam_arf');

            rcmail.confirm_dialog(message, title, function() {
                var lock = rcmail.set_busy(true, rcmail.gettext('reporting', 'spam_arf'));
                
                rcmail.http_post('plugin.send_arf', {
                    _uid: uid,
                    _mbox: rcmail.env.mailbox
                }, lock);
            });
        }, true);
    });
}