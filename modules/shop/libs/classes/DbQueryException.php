<?php
/**
 * File containing the DBException class
 */
/**
 * Exception returned by the database, when executing a query
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class DbQueryException extends Exception
{
	/**
	 * Logs the error message received
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($message, $code = 0, Exception $previous = NULL) {
		ShopLogger::log("DbQueryException: <a href='#' class='logger_message_details'>" . $message . '</a><div style="display:none">' . $this->getTraceAsString() . '</div>');
		parent::__construct($message, $code, $previous);
	}
}