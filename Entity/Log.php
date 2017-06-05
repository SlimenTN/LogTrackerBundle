<?php

namespace SBC\LogTrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Log entity
 * This entity will contain log details
 */
class Log implements \JsonSerializable
{
    function jsonSerialize()
    {
        return get_object_vars($this);
    }


    public static $STATUS = array(
        'INFO' => 'indigo darken-4',
        'DEBUG' => 'teal darken-4',
        'ERROR' => 'red lighten-2',
        'CRITICAL' => 'red darken-4',
        'WARNING' => 'yellow darken-3',
    );

    /**
     * Source of log (security, request, ...)
     * @var string
     */
    private $logSource;

    /**
     * @var string
     *
     */
    private $status;

    /**
     * @var string
     *
     */
    private $description;

    /**
     * @var \DateTime
     *
     */
    private $dateLog;

    /**
     * @var string
     */
    private $statusColor;

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Log
     */
    public function setStatus($status)
    {
        $this->status = $status;

        $this->setStatusColor(self::$STATUS[$this->status]);

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set dateLog
     *
     * @param \DateTime $dateLog
     *
     * @return Log
     */
    public function setDateLog($dateLog)
    {
        $this->dateLog = $dateLog;
    
        return $this;
    }

    /**
     * Get dateLog
     *
     * @return \DateTime
     */
    public function getDateLog()
    {
        return $this->dateLog;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Log
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set logSource
     *
     * @param string $logSource
     *
     * @return Log
     */
    public function setLogSource($logSource)
    {
        $this->logSource = $logSource;

        return $this;
    }

    /**
     * Get logSource
     *
     * @return string
     */
    public function getLogSource()
    {
        return $this->logSource;
    }

    /**
     * Set statusColor
     *
     * @param string $statusColor
     *
     * @return Log
     */
    public function setStatusColor($statusColor)
    {
        $this->statusColor = $statusColor;

        return $this;
    }

    /**
     * Get statusColor
     *
     * @return string
     */
    public function getStatusColor()
    {
        return $this->statusColor;
    }
}

