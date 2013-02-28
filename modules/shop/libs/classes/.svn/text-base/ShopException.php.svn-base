<?php
/**
 * File containing the ShopException class
 */
/**
 * Base class for all Exceptions returned by XE Shop
 *
 * Automatically logs all exceptions to the shop log file in ./files/shop_log.txt
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class ShopException extends Exception
{
	/**
	 * Constructor
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($message, $code = 0, Exception $previous = NULL) {
		ShopLogger::log("ShopException: <a href='#' class='logger_message_details'>" . $message . '</a><div style="display:none">' . $this->getTraceAsString() . '</div>');
		parent::__construct($message, $code, $previous);
	}
}