if (window.rcmail) {
    rcmail.addEventListener('init', function() {
        // Register the command so the UI knows it's active
        rcmail.register_command('plugin.send_arf', function() {
            var uid = rcmail.get_single_uid();
            if (!uid) return;

            var title = rcmail.gettext('reportspam', 'spam_arf');

            // Build a dialog with a reporting address input
            var input = $('<input>').attr({
                type: 'email',
                id: 'spam-arf-address',
                style: 'width:100%;margin-top:8px;box-sizing:border-box'
            });
            var content = $('<div>').append(
                $('<p>').text(rcmail.gettext('reportconfirm', 'spam_arf')),
                $('<label>').attr('for', 'spam-arf-address')
                            .text(rcmail.gettext('reportaddress', 'spam_arf')),
                input
            );

            rcmail.show_popup_dialog(content, title, [
                {
                    text: rcmail.gettext('send', 'spam_arf'),
                    'class': 'mainaction',
                    click: function() {
                        var address = input.val().trim();
                        if (!address || !address.match(/^[^@\s]+@[^@\s]+\.[^@\s]+$/)) {
                            input.focus();
                            return;
                        }
                        $(this).dialog('close');
                        var lock = rcmail.set_busy(true, rcmail.gettext('reporting', 'spam_arf'));
                        rcmail.http_post('plugin.send_arf', {
                            _uid: uid,
                            _mbox: rcmail.env.mailbox,
                            _report_to: address
                        }, lock);
                    }
                },
                {
                    text: rcmail.gettext('cancel', 'spam_arf'),
                    click: function() { $(this).dialog('close'); }
                }
            ]);
        }, true);
    });
}