<?php

namespace App\Services\Communication\SMS;

interface SmsServiceInterface
{
    /**
     * Send an SMS message to a single recipient.
     *
     * @param string $recipient
     * @param string $message
     * @param array $options
     * @return array
     */
    public function sendSingle(string $recipient, string $message, array $options = []);

    /**
     * Send the same SMS message to multiple recipients.
     *
     * @param array $recipients
     * @param string $message
     * @param array $options
     * @return array
     */
    public function sendBulk(array $recipients, string $message, array $options = []);

    /**
     * Send an SMS message to a predefined group.
     *
     * @param int $groupId
     * @param string $message
     * @param array $options
     * @return array
     */
    public function sendToGroup(int $groupId, string $message, array $options = []);

    /**
     * Validate a phone number format.
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function validatePhoneNumber(string $phoneNumber): bool;
}