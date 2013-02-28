<?php
/**
 * File containing the ArgumentException class
 */
/**
 * Exception thrown when an invalid argument is supplied
 *
 * TODO Remove this class and use the standard library one instead
 *
 * @author Corin Udrescu (corina.udrescu@arnia.ro)
 */
class ArgumentException extends Exception
{
	/**
	 * Constructor; logs the error message to the shop log.
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($message, $code = 0, Exception $previous = NULL) {
		ShopLogger::log("ArgumentException: <a href='#' class='logger_message_details'>" . $message . '</a><div style="display:none">' . $this->getTraceAsString() . '</div>');
		parent::__construct($message, $code, $previous);
	}
}