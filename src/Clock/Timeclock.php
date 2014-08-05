<?php

namespace Clock;

use Guzzle\Http\Client;
use Guzzle\Plugin\Cookie\CookiePlugin;
use Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar;

/**
 * This is a third-party (not official) PHP wrapper for getting data
 * from your Timeclock history. Sadly, the CA Timeclock does not offer RESTful
 * API so this uses a HTTP client and parses the HTML string
 * to get the value.
 *
 * @author Leonel Tomes <leonel.tomes@codingavenue.com>
 */
class Timeclock
{
    /**
     * The URL for accessing the time clock
     */
    const BASE_URL = 'http://codingavenue.com/clock';

    /**
     * The client object for accessing the resource
     *
     * @var Guzzle\Http\Client
     */
    protected $client;

    /**
     * A variable for determining whether authentication was done
     * and successful
     *
     * @var bool
     */
    protected $is_authenticated;

    /**
     * Prepares the client object with cookies support.
     *
     * @uses Guzzle\Http\Client;
     * @uses Guzzle\Plugin\Cookie\CookiePlugin;
     * @uses Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar;
     */
    public function __construct()
    {
        $this->client = new Client(self::BASE_URL);
        $this->client->setSslVerification(false);

        $cookie_jar = new ArrayCookieJar();
        $cookie_plugin = new CookiePlugin($cookie_jar);
        $this->client->addSubscriber($cookie_plugin);

        $this->is_authenticated = false;
    }

    /**
     * Returns a PunchHistory object that provides details
     * about the punch clock history for the given year
     * and month.
     *
     * @throws BadMethodCallException if not yet authenticated
     * @var int    $year  optional, defaults to current year
     * @var string $month optional, defaults to current month
     * @return Clock\PunchHistory
     */
    public function getPunchHistory($year = null, $month = null)
    {
        if (! $this->is_authenticated) {
            throw new BadMethodCallException('Must be authenticated first before calling method.');
        }

        $year = $year ?: date('Y');
        $month = $month ?: date('m');

        $get_data = array(
            'show'  => 'Show History', // input type submit
            'year'  => $year,
            'month' => $month
        );

        $uri = self::BASE_URL . '/punch_history';
        $request = $this->client->get($uri, array('query' => $get_data));
        $response = $request->send();

        return new PunchHistory($response);
    }

    /**
     * Logs in the email-password pair and return and sets the authentication status.
     *
     * @param  string $email_address
     * @param  string $password
     * @return boo
     */
    public function authenticate($email_address, $password)
    {
        $uri = self::BASE_URL . '/login';
        $post_data = array(
            'email_address' => $email_address,
            'password'      => $password
        );

        $request = $this->client->post($uri, array(), $post_data);
        $response = $request->send();

        $this->is_authenticated = $this->isAuthenticationSuccessful($response);

        return $this->is_authenticated;
    }

    /**
     * Checks if authentication was successful by comparing the effective URL
     * with the Home URL
     *
     * @return bool
     */
    protected function isAuthenticationSuccessful($response)
    {
        return $response->getEffectiveUrl() == $this->getHomeUrl();
    }

    /**
     * Return the URL after the user successfully logs in to the time clock.
     *
     * @return string
     */
    protected function getHomeUrl()
    {
        $url = self::BASE_URL . '/home';

        return $url;
    }
}
