<?php

namespace Dblencowe\Wealth;

class Currency implements \JsonSerializable
{
    private $code;
    private $latestRate;
    private $balance = 0.0;
    private $lastBalance = 0.0;

    public function __construct(string $code, float $latestRate, float $balance = 0.0, float $lastBalance = null)
    {
        $this->code = $code;
        $this->latestRate = $latestRate;
        $this->lastBalance = $this->balance;
        if ($lastBalance) {
            $this->lastBalance = $lastBalance;
        }
        $this->balance = $balance;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return Currency
     */
    public function setCode(string $code): Currency
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return float
     */
    public function getLatestRate(): float
    {
        return $this->latestRate;
    }

    /**
     * @param float $latestRate
     *
     * @return Currency
     */
    public function setLatestRate(float $latestRate): Currency
    {
        $this->latestRate = $latestRate;

        return $this;
    }

    /**
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * @param float $balance
     *
     * @return Currency
     */
    public function setBalance(float $balance): Currency
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @param float $balance
     *
     * @return Currency
     */
    public function setLastBalance(float $balance): Currency
    {
        $this->lastBalance = $balance;

        return $this;
    }

    public function getLastBalance(): float
    {
        return $this->lastBalance;
    }

    /**
     * @return float
     */
    public function wealth()
    {
        return $this->balance / $this->latestRate;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'code' => $this->code,
            'balance' => $this->balance,
            'latestRate' => $this->latestRate,
        ];
    }
}