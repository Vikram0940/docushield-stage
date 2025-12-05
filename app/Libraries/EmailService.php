<?php

namespace App\Libraries;

use App\Models\EmailLog;

class EmailService
{
    protected $email;
    protected $view;
    protected $logModel;
    protected $config;

    public function __construct()
    {
        $emailConfig = [
            'protocol'    => 'smtp',
            'SMTPHost'    => 'localhost',
            'SMTPPort'    => 25,
            'mailType'    => 'html',
            'SMTPAuth'    => false,
            'SMTPTimeout' => 70,
            'fromEmail'   => 'donotreply@stage.docushieldtech.com',
            'fromName'    => env("COMPANY_NAME")
        ];    
        $this->email    = \Config\Services::email($emailConfig, false);
        $this->view     = service('renderer');
        $this->config   = config('Email');
        $this->logModel = new EmailLog();
    }

    /**
     * Send an email
     *
     * @param string|array $to
     * @param string       $subject
     * @param string       $message       Direct message body (used if no template)
     * @param string|null  $from
     * @param string|null  $fromName
     * @param string|null  $template      View name for template (e.g., "emails/welcome")
     * @param array        $templateData  Data passed to the view
     * @param array        $attachments   Array of file paths
     * @param array        $cc
     * @param array        $bcc
     * @param string|null  $replyTo
     * @param string|null  $replyToName
     *
     * @return bool
     */

    public function sendEmail(
        $to,
        string $subject,
        string $message = '',
        ?string $from = null,
        ?string $fromName = null,
        ?string $template = null,
        array $templateData = [],
        array $attachments = [],
        array $cc = [],
        array $bcc = [],
        ?string $replyTo = null,
        ?string $replyToName = null
    ): bool {
        $this->email->clear(true);

        // From
        if ($from) {
            $this->email->setFrom($from, $fromName ?? '');
        } else {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
        }

        // Recipients
        $this->email->setTo($to);
        if (!empty($cc)) {
            $this->email->setCC($cc);
        }
        if (!empty($bcc)) {
            $this->email->setBCC($bcc);
        }
        if ($replyTo) {
            $this->email->setReplyTo($replyTo, $replyToName ?? '');
        }

        // Subject
        $this->email->setSubject($subject);

        // Body
        if ($template) {
            $body = $this->view->setData($templateData)->render($template);
            $this->email->setMessage($body);
        } else {
            $this->email->setMessage($message);
        }

        // Attachments
        foreach ($attachments as $filePath) {
            if (is_file($filePath)) {
                $this->email->attach($filePath);
            }
        }

        // Send
        $success = $this->email->send();
        $error   = $success ? null : $this->email->printDebugger(['headers', 'subject', 'body']);

        // Log
        $this->logModel->insert([
            'to'            => is_array($to) ? implode(',', $to) : $to,
            'cc'            => !empty($cc) ? (is_array($cc) ? implode(',', $cc) : $cc) : null,
            'bcc'           => !empty($bcc) ? (is_array($bcc) ? implode(',', $bcc) : $bcc) : null,
            'reply_to'      => $replyTo,
            'subject'       => $subject,
            'template'      => $template,
            'status'        => $success ? 'success' : 'failed',
            'error_message' => $error,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        // Also log to file (optional)
        if (!$success) {
            log_message('error', 'Email failed: ' . $error);
        }

        return $success;
    }
}
