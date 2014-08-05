<?php

namespace Clock;

use Guzzle\Http\Message\Response;
use Sunra\PhpSimple\HtmlDomParser;

/**
 * Represents a month-long punch clock history.
 *
 * @see Clock\Timeclock::getPunchHistory()
 * @author Leonel Tomes <leonel.tomes@codingavenue.com>
 */
class PunchHistory
{
    /**
     * The response object that has the HTML content
     * of the Punch history page.
     *
     * @var Guzzle\Http\Message\Response
     */
    protected $response;

    /**
     * The DOM object for parsing the response body.
     *
     * @var Sunra\PhpSimple\HtmlDomParser
     */
    protected $dom;

    /**
     * Creates an instance using a response object. The response
     * object is from the request sent in getting the punch history.
     *
     * @param Guzzle\Http\Message\Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
        $this->dom = HtmlDomParser::str_get_html($response->getBody(true));
    }

    /**
     * Returns a full numeric representation of the year this data is from
     *
     * @return numeric
     */
    public function getYear()
    {
        return $this->dom->find('input[name=year]', 0)->value;
    }

    /**
     * Returns the numeric representation of a month, with leading zeros,
     * where  this data is from.
     *
     * @return numeric
     */
    public function getMonth()
    {
        return $this->dom->find('input[name=month]', 0)->value;
    }

    /**
     * Get the time worked as number of days, hours and minutes.
     *
     * @return string
     */
    public function getTimeWorked()
    {
        $text = $this->dom->find('text', 29);
        return trim($text, " \t\n\r\0\x0B:");
    }

    /**
     * Get the work days for the selected month and year, excluding holidays.
     *
     * @return integer
     */
    public function getWorkDays()
    {
        $text = $this->dom->find('text', 32);
        return trim($text, " \t\n\r\0\x0B:");
    }

    /**
     * Returns an array of every punch time. A punch time consists of
     * Punch In (datetime), Punch Out (datetime), Time Logged (time) and Client (string).
     *
     * @return array
     */
    public function getPunchHistory()
    {
        // Even if we specify 'tbody', this still returns all tr including from thead.
        // I also tried find('.punch_history')->find('tbody') but it does not work.
        $tr = $this->dom->find('.punch_history tbody tr');
        array_shift($tr); // remove the thead tr
        $columns = array('Punch In', 'Punch Out', 'Time Logged', 'Client');
        $transformed = array();

        foreach ($tr as $row) {
            $transformed_row = array();
            foreach ($columns as $idx => $column_name) {
                $transformed_row[$column_name] = trim($row->children($idx)->plaintext);
            }
            $transformed[] = $transformed_row;
        }

        return $transformed;
    }

    /**
     * Returns the HTML response (for debugging purposes)
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->response->getBody(true);
    }
}
