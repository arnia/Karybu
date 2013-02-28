<?php
/**
 * File containing the APIExpcetion class
 */
/**
 * Base class for exceptions returned by API calls
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class APIException extends Exception
{
	/**
	 * Constructor; logs the error message and the stack trace to the shop log
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($message, $code = 0, Exception $previous = NULL) {
		$log_message = "APIException: <a href='#' class='logger_message_details'>" . $message . '</a>';
		$log_message .='<div style="display:none">' . $this->getTraceAsString() . '</br>';
		$log_message .= print_r($_REQUEST, TRUE) . '</div>';

		ShopLogger::log($log_message);

		parent::__construct($message, $code, $previous);
	}
}