<?php

namespace VirtualQueue\PeopleForwarder;

use VirtualQueue\PeopleForwarder\Exception\SdkException;

/**
 * Client to verify virtual queue tokens.
 */
class PeopleForwarder
{
    /**
     * @var string
     */
    private $subdomain;

    /**
     * @var string
     */
    private $pk;

    /**
     * Constructor.
     *
     * @param string $subdomain personal subdomain in VirtualQueue
     */
    public function __construct(string $subdomain, string $pk)
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.use_strict_mode', 1);
            session_start();
        }
        
        if (empty($subdomain) || empty($pk)) {
            throw new SdkException('Subdomain and private key are required');
        }

        $this->subdomain = $subdomain;
        $this->pk = $pk;
    }

    /**
     * Forward a person to the queue.
     */
    public function forward()
    {
        if (isset($_SESSION['forward_to_vqueue'])) {
            if (ob_get_level()) ob_end_clean();
            
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('Location: https://' . $this->subdomain . '.virtual-queue.com');
            exit();
        }
    }

    /**
     * Activate the forward to the queue.
     *
     * @param string $signature
     * @param string $timestamp
     */
    public function activate(string $signature, string $timestamp)
    {
        if ($this->checkSignature($signature, $timestamp, 'activate')) {
            $_SESSION['forward_to_vqueue'] = true;
        } else {
            throw new SdkException('Invalid signature');
        }
    }

    /**
     * Deactivate the forward to the queue.
     *
     * @param string $signature
     * @param string $timestamp
     */
    public function deactivate(string $signature, string $timestamp)
    {
        if ($this->checkSignature($signature, $timestamp, 'deactivate')) {
            unset($_SESSION['forward_to_vqueue']);
        } else {
            throw new SdkException('Invalid signature');
        }
    }

    /**
     * Dispatch the action.
     *
     * @param string $action
     * @param string $signature
     * @param string $timestamp
     * @throws SdkException
     */
    public function dispatchAction(string $action, string $signature, string $timestamp)
    {
        if ($action == 'activate') {
            $this->activate($signature, $timestamp);
        } else if ($action == 'deactivate') {
            $this->deactivate($signature, $timestamp);
        }
        else {
            throw new SdkException('Invalid action');
        }
    }

    /**
     * Check if the signature is valid.
     *
     * @param string $signature
     * @param string $timestamp
     * @return bool
     */
    private function checkSignature(string $signature, string $timestamp, string $action)
    {
        if (time() - intval($timestamp) > 300) {
            return false;
        }
        
        $sign = hash_hmac('sha256', $timestamp . $action, $this->pk);
        return hash_equals($sign, $signature);
    }
}