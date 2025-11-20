<?php

namespace App\Helpers;

use DeviceDetector\DeviceDetector as MatomoDeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Illuminate\Support\Facades\Log;

class DeviceDetector
{
    private $deviceDetector;

    public function __construct()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->deviceDetector = new MatomoDeviceDetector($userAgent);

        // Set discardBotInformation to false to detect bots
        AbstractDeviceParser::setVersionTruncation(AbstractDeviceParser::VERSION_TRUNCATION_NONE);

        try {
            $this->deviceDetector->parse();
        } catch (\Exception $e) {
            Log::error('Error parsing user agent', [
                'user_agent' => $userAgent,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the browser name
     *
     * @return string
     */
    public function browser()
    {
        $client = $this->deviceDetector->getClient();

        return $client['name'] ?? 'Unknown';
    }

    /**
     * Get the platform/OS name
     *
     * @return string
     */
    public function platform()
    {
        $os = $this->deviceDetector->getOs();

        return $os['name'] ?? 'Unknown';
    }

    /**
     * Get the device name
     *
     * @return string
     */
    public function device()
    {
        $device = $this->deviceDetector->getDeviceName();

        return $device ?: 'Unknown';
    }

    /**
     * Get device type
     *
     * @return string
     */
    public function deviceType()
    {
        return $this->deviceDetector->getDeviceName() ?: 'desktop';
    }

    /**
     * Check if the device is a mobile
     *
     * @return bool
     */
    public function isMobile()
    {
        return $this->deviceDetector->isMobile();
    }

    /**
     * Check if the device is a tablet
     *
     * @return bool
     */
    public function isTablet()
    {
        return $this->deviceDetector->isTablet();
    }

    /**
     * Check if the device is a desktop
     *
     * @return bool
     */
    public function isDesktop()
    {
        return $this->deviceDetector->isDesktop();
    }

    /**
     * Check if the request comes from a bot
     *
     * @return bool
     */
    public function isBot()
    {
        return $this->deviceDetector->isBot();
    }

    /**
     * Get the bot info
     *
     * @return array|null
     */
    public function getBot()
    {
        return $this->deviceDetector->getBot();
    }

    /**
     * Get a detailed array with all device information
     *
     * @return array
     */
    public function getDeviceInfo()
    {
        return [
            'browser' => $this->browser(),
            'platform' => $this->platform(),
            'device' => $this->device(),
            'ip' => request()->ip(),
            'is_mobile' => $this->isMobile(),
            'is_tablet' => $this->isTablet(),
            'is_desktop' => $this->isDesktop(),
            'is_bot' => $this->isBot(),
        ];
    }
}
