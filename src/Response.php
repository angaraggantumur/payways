<?php

namespace Selmonal\Payways;

use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse as HttpRedirectResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

abstract class Response
{
    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_DECLINED  = 'declined';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * @var GatewayInterface
     */
    private $gateway;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var array
     */
    protected $data;

    /**
     * AbstractResponse constructor.
     *
     * @param Gateway $gateway
     * @param Transaction $transaction
     * @param array $data
     */
    public function __construct(Gateway $gateway, Transaction $transaction, array $data = [])
    {
        $this->gateway = $gateway;
        $this->transaction = $transaction;
        $this->data = $data;
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @return GatewayInterface
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * @return boolean
     */
    abstract public function isSuccessful();

    /**
     * @return boolean
     */
    public function isRedirect()
    {
        return false;
    }

    /**
     * @return string
     */
    abstract public function getStatus();

    /**
     * @return string|null
     */
    public function getCode()
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return null;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the id that generated by bank.
     *
     * @return string|null
     */
    public function getTransactionReference()
    {
        return null;
    }

    /**
     * Automatically perform any required redirect
     *
     * This method is meant to be a helper for simple scenarios. If you want to customize the
     * redirection page, just call the getRedirectUrl() and getRedirectData() methods directly.
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function redirect()
    {
        $this->getRedirectResponse()->send();
        exit;
    }

    /**
     * @return HttpRedirectResponse
     * @throws RuntimeException
     */
    public function getRedirectResponse()
    {
        if (! $this instanceof RedirectResponseInterface || ! $this->isRedirect()) {
            throw new RuntimeException('This response does not support redirection.');
        }

        if ('GET' === $this->getRedirectMethod()) {
            return HttpRedirectResponse::create($this->getRedirectUrl());
        }

        if ('POST' === $this->getRedirectMethod()) {
            $hiddenFields = '';
            foreach ($this->getRedirectData() as $key => $value) {
                $hiddenFields .= sprintf(
                        '<input type="hidden" name="%1$s" value="%2$s" />',
                        htmlentities($key, ENT_QUOTES, 'UTF-8', false),
                        htmlentities($value, ENT_QUOTES, 'UTF-8', false)
                    )."\n";
            }
            $output = '<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Redirecting...</title>
    </head>
    <body onload="document.forms[0].submit();">
        <form action="%1$s" method="post">
            <p>Redirecting to payment page...</p>
            <p>
                %2$s
                <input type="submit" value="Continue" />
            </p>
        </form>
    </body>
</html>';
            $output = sprintf(
                $output,
                htmlentities($this->getRedirectUrl(), ENT_QUOTES, 'UTF-8', false),
                $hiddenFields
            );
            return HttpResponse::create($output);
        }
        throw new RuntimeException('Invalid redirect method "'.$this->getRedirectMethod().'".');
    }
}
