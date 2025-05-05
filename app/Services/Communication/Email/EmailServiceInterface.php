<?php

namespace App\Services\Communication\Email;

interface EmailServiceInterface
{
    /**
     * Send an email to a single recipient.
     *
     * @param string $recipient
     * @param string $subject
     * @param string|array $message
     * @param array $options
     * @return array
     */
    public function sendSingle(string $recipient, string $subject, $message, array $options = []);

    /**
     * Send the same email to multiple recipients.
     *
     * @param array $recipients
     * @param string $subject
     * @param string|array $message
     * @param array $options
     * @return array
     */
    public function sendBulk(array $recipients, string $subject, $message, array $options = []);

    /**
     * Send an email to a predefined group.
     *
     * @param int $groupId
     * @param string $subject
     * @param string|array $message
     * @param array $options
     * @return array
     */
    public function sendToGroup(int $groupId, string $subject, $message, array $options = []);

    /**
     * Validate an email address format.
     *
     * @param string $email
     * @return bool
     */
    public function validateEmail(string $email): bool;
}