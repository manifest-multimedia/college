<?php

namespace App\Services\Communication\Email;

interface EmailServiceInterface
{
    /**
     * Send an email to a single recipient.
     *
     * @param  string|array  $message
     * @return array
     */
    public function sendSingle(string $recipient, string $subject, $message, array $options = []);

    /**
     * Send the same email to multiple recipients.
     *
     * @param  string|array  $message
     * @return array
     */
    public function sendBulk(array $recipients, string $subject, $message, array $options = []);

    /**
     * Send an email to a predefined group.
     *
     * @param  string|array  $message
     * @return array
     */
    public function sendToGroup(int $groupId, string $subject, $message, array $options = []);

    /**
     * Validate an email address format.
     */
    public function validateEmail(string $email): bool;
}
