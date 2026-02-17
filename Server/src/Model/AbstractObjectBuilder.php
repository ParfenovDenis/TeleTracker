<?php
/**
 * Copyright (c) 2021.
 */

namespace App\Model;

use App\Entity\CanBus\InvalidValueException;
use App\Entity\CanBus\Log\Line;
use App\Entity\CanBus\Message;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;
use App\Model\CanBus\MessageBuilder;
use App\Model\Exception\MinLengthBytesException;
use App\Model\Exception\NotEnoughBytesException;
use App\Model\GPS\GpsBuilder;
use App\Model\Modem\ModemBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\HTTP\Request;

/**
 *
 */
abstract class AbstractObjectBuilder
{
    protected $imei;

    protected $fp;

    protected $request;

    protected $logLine;

    protected $message;

    /**
     * @var GPS[]
     */
    protected $gps;

    protected $modem;

    protected $bytes;

    const VERSION = '1';

    const MIN_BYTES_LENGTH = 62;


    const LOG_LINE_LENGTH = 53;

    const POST_DATA_SIZE = 9973;

    const IMEI_LENGTH = 5;

    const FP_LENGTH = 4;

    const IMEI_PREFIX = '86';

    const OFFSET_MILLIS = 0;

    const START_MESSAGE = 4;

    const FINISH_MESSAGE = 18;

    const START_GPS = 19;

    const FINISH_GPS = 44;

    const START_MODEM = 45;

    const FINISH_MODEM = 52;

    const THEAD = [
        'tank' => 'tank',
        'distance' => 'distance',
        'fuel_economy' => 'fuel_economy1',
        'rpm' => 'rpm',
        'temperature' => 'temperature',
        'speedometer' => 'speedometer',
    ];

    protected $offset;


    protected $errors = [];

    /**
     * @param string $bytes
     */
    public function __construct(string $bytes)
    {
        static::checkLengthBytes($bytes);
        $this->bytes = $bytes;
        $this->initOffset();
    }


    /**
     * @return $this
     */
    public function build(): self
    {
        $request = $this->getRequest();
        if (!count($request->getLogs())) {
            $length = \strlen($this->bytes);
            while ($this->offset + static::LOG_LINE_LENGTH <= $length) {

                    $logLine = new Line();
                    $logLine->setMillis($this->getMillis());
                    $logLine->setRequestId($request);
                    $messageBuilder = $this->getMessageBuilder();
                    $message = $messageBuilder->getMessage($logLine);
                    $gpsBuilder = $this->getGpsBuilder();
                    $gps = $gpsBuilder->getGPS($logLine);
                    $modemBuilder = $this->getModemBuilder();
                    $modem = $modemBuilder->getModem($logLine);

                    $logLine
                        ->setMessage($message)
                        ->setGps($gps)
                        ->setModem($modem);

                    $request->addLog($logLine);

                $this->offset += static::LOG_LINE_LENGTH;
            }
        }

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
    /**
     * @return string
     */
    abstract public function getContentBytes(): string;

    /**
     * @return string
     */
    abstract public function getImei(): string;

    /**
     * @param GPS $GPS
     *
     * @return void
     */
    public function addGPS(GPS $GPS)
    {
        $this->gps[] = $GPS;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        if (!$this->request) {
            $this->request = new Request();
            $this->request->setImei($this->getImei())->setFp($this->getFp())->setVersion(static::VERSION);
            $this->request->setContent($this->bytes);
        }

        return $this->request;
    }

    /**
     * @param Request $request
     *
     * @return $this
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return int
     */
    public function getFp(): int
    {
        $i0 = static::IMEI_LENGTH;
        $i1 = static::IMEI_LENGTH + 1;
        $i2 = static::IMEI_LENGTH + 2;
        $i3 = static::IMEI_LENGTH + 4;
        $v0 = ord($this->bytes[static::IMEI_LENGTH]);
        $v1 = ord($this->bytes[static::IMEI_LENGTH + 1]);
        $v2 = ord($this->bytes[static::IMEI_LENGTH + 2]);
        $v3 = ord($this->bytes[static::IMEI_LENGTH + 3]);
        $fp = ord($this->bytes[static::IMEI_LENGTH]) << 24 | ord($this->bytes[static::IMEI_LENGTH + 1]) << 16 | ord($this->bytes[static::IMEI_LENGTH + 2]) << 8 | ord($this->bytes[static::IMEI_LENGTH + 3]);

        return $fp;
    }

    /**
     * @param string $bytes
     *
     * @return void
     */
    static protected function checkLengthBytes(string $bytes): void
    {
        if (($lengthBytes = \strlen($bytes)) < static::MIN_BYTES_LENGTH) {
            throw new MinLengthBytesException(sprintf('Minimal length bytes is %d got %d', static::MIN_BYTES_LENGTH, $lengthBytes));
        }
    }

    /**
     * @return int
     */
    protected function getMillis(): int
    {
        $i = $this->offset + static::OFFSET_MILLIS;
        $millis = Converter::convertCharsToLong($this->bytes[$i] . $this->bytes[++$i] . $this->bytes[++$i] . $this->bytes[++$i]);

        return $millis;
    }

    /**
     * @return string
     */
    protected function getMessageBytes(): string
    {
        $messageBytes = '';
        for ($i = static::START_MESSAGE; $i <= static::FINISH_MESSAGE; $i++) {
            $messageBytes .= $this->bytes[$this->offset + $i];
        }

        return $messageBytes;
    }

    /**
     * @return MessageBuilder
     */
    protected function getMessageBuilder(): MessageBuilder
    {
        return new MessageBuilder($this->getMessageBytes());
    }

    /**
     * @return GpsBuilder
     */
    protected function getGpsBuilder(): GpsBuilder
    {
        return new GpsBuilder($this->getGpsBytes());
    }

    /**
     * @return string
     */
    protected function getGpsBytes(): string
    {
        $gpsBytes = '';
        if ($this->offset + static::FINISH_GPS >= strlen($this->bytes)) {
            $exceptionMessage = "Number of bytes exceeded. 
                offset: " . $this->offset . "
                START_GPS: " . ($this->offset + static::START_GPS) . "
                FINISH_GPS: " . ($this->offset + static::FINISH_GPS) . "
                String length: " . strlen($this->bytes);
            throw new \OutOfRangeException($exceptionMessage);
        }
        for ($i = static::START_GPS; $i <= static::FINISH_GPS; $i++) {
            $gpsBytes .= $this->bytes[$this->offset + $i];
        }

        return $gpsBytes;
    }

    /**
     * @return ModemBuilder
     */
    protected function getModemBuilder(): ModemBuilder
    {
        return new ModemBuilder($this->getModemBytes());
    }

    /**
     * @return string
     */
    protected function getModemBytes(): string
    {
        $needBytes = static::FINISH_MODEM;
        if ($this->offset + $needBytes >= strlen($this->bytes)) {
            $code = static::class . '::' . __METHOD__ . '()';
            throw new NotEnoughBytesException($this->offset, $needBytes, strlen($this->bytes), $code);
        }
        $modemBytes = '';
        for ($i = static::START_MODEM; $i <= static::FINISH_MODEM; $i++) {
            $modemBytes .= $this->bytes[$this->offset + $i];
        }

        return $modemBytes;
    }

    /**
     * @return void
     */
    abstract protected function initOffset(): void;

}
