<?php

namespace Arc\Testing\Traits;

use WP_REST_Server;
use WP_REST_Request;
use WPAjaxDieContinueException;

/**
 * Use this trait for testing the wp-json API
 **/
trait TestsAPI
{
    /** @before */
    protected function prepareForAPITesting()
    {
        // Initialise the wordpress rest server
        global $wp_rest_server;
        $this->server = $wp_rest_server = new WP_REST_Server;
        do_action('rest_api_init');

        add_filter( 'wp_die_ajax_handler', array( $this, 'getDieHandler' ), 1, 1 );

        if (!defined('DOING_AJAX')) {
            define( 'DOING_AJAX', true );
            set_current_screen( 'ajax' );
        }
    }

    /** @after */
    public function undoAjaxPreparation()
    {
        global $wp_rest_server;
        $wp_rest_server = null;
    }

    /**
     * Return our callback handler
     * @return callback
     */
    public function getDieHandler()
    {
    	return array( $this, 'dieHandler' );
    }

    /**
    * Handler for wp_die()
    * Save the output for analysis, stop execution by throwing an exception.
    * Error conditions (no output, just die) will throw <code>WPAjaxDieStopException( $message )</code>
    * You can test for this with:
    * <code>
    * $this->setExpectedException( 'WPAjaxDieStopException', 'something contained in $message' );
    * </code>
    * Normal program termination (wp_die called at then end of output) will throw <code>WPAjaxDieContinueException( $message )</code>
    * You can test for this with:
    * <code>
    * $this->setExpectedException( 'WPAjaxDieContinueException', 'something contained in $message' );
    * </code>
    * @param string $message
    * @throws WPAjaxDieContinueException
    * @throws WPAjaxDieStopException
    */
    public function dieHandler($message)
    {

    }

    /**
     * Simulate a request to the wordpress json api
     * @param string $method GET or POST
     * @param string $uri The uri after the api prefix (which defaults to wp-json)
     * @param array $parameters (optional)
     **/
    protected function sendApiRequest($method, $uri, $parameters = [])
    {
        $request = new WP_REST_Request($method, $uri);

        collect($parameters)->each(function ($value, $key) use ($request) {
            $request->set_param($key, $value);
        });

        return $this->server->dispatch($request);
    }

}
