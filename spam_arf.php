<?php
class spam_arf extends rcube_plugin {
    public $task = 'mail';

    function init() {
        $rcmail = rcmail::get_instance();
        
        // Register the server-side action
        $this->register_action('plugin.send_arf', array($this, 'send_arf_report'));

        // Load JS and localized strings for the frontend
        if ($rcmail->task == 'mail') {
            $this->include_script('spam_arf.js');
            $this->add_texts('localization/', true);

            $this->include_stylesheet($this->local_skin_path() . '/spam_arf.css');
        }

        // Add the button to the toolbar
        $this->add_button(array(
            'type' => 'link',
            'label' => 'spam_arf.reportspam',
            'command' => 'plugin.send_arf',
            'class' => 'button report-spam',
            'innerclass' => 'inner',
            'title' => 'spam_arf.reportspam'
        ), 'toolbar');
    }

    function send_arf_report() {
        $rcmail = rcmail::get_instance();
        $uid = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_POST);
        
        // 1. Fetch the raw message parts
        $headers = $rcmail->storage->get_raw_headers($uid);
        $body = $rcmail->storage->get_raw_body($uid);
        $full_source = $headers . "\r\n\r\n" . $body;

        // 2. Build ARF MIME structure
        $boundary = "report_" . md5(microtime());
        $report_to = rcube_utils::get_input_value('_report_to', rcube_utils::INPUT_POST);

        if (empty($report_to) || !filter_var($report_to, FILTER_VALIDATE_EMAIL)) {
            $rcmail->output->show_message('spam_arf.reporterror', 'error');
            $rcmail->output->send();
            return;
        }

        $arf_body = "This is an automated ARF report.\r\n\r\n"
                  . "--$boundary\r\n"
                  . "Content-Type: text/plain; charset=utf-8\r\n\r\n"
                  . "The attached message was reported as spam by a user.\r\n\r\n"
                  . "--$boundary\r\n"
                  . "Content-Type: message/feedback-report\r\n\r\n"
                  . "Feedback-Type: abuse\r\n"
                  . "User-Agent: Roundcube-ARF/1.0\r\n"
                  . "Version: 1\r\n\r\n"
                  . "--$boundary\r\n"
                  . "Content-Type: message/rfc822\r\n\r\n"
                  . $full_source . "\r\n"
                  . "--$boundary--";

        $mail_headers = array(
            'Subject' => 'Spam report',
            'From'    => $rcmail->user->get_username(),
            'To'      => $report_to,
            'Content-Type' => "multipart/report; report-type=feedback-report; boundary=\"$boundary\""
        );

        // 3. Deliver and respond
        $sent = $rcmail->deliver_message(new Mail_mime(), $mail_headers['From'], $mail_headers['To'], $mail_headers, $arf_body);

        if ($sent) {
            $rcmail->output->show_message('spam_arf.reportsuccess', 'confirmation');
        } else {
            $rcmail->output->show_message('spam_arf.reporterror', 'error');
        }

        $rcmail->output->send();
    }
}