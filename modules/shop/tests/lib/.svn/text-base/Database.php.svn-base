<?php

/**
 * Thin wrapper for a Mysql database
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class Database
{

	private static $mysqli;

	/**
	 * Connect to the database
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @static
	 * @throws Exception
	 */
	private static function connect()
	{
		if(!isset(self::$mysqli))
		{
			$db_info = include dirname(__FILE__) . '/../config/db.config.php';
			self::$mysqli = new mysqli(
								$db_info['db_hostname']
								, $db_info['db_userid']
								, $db_info['db_password']
								, $db_info['db_database']
								, (int)$db_info['db_port']
			);
		}

		/* check connection */
		if(self::$mysqli->connect_errno)
		{
			throw new Exception(sprintf("Connect failed: %s\n", self::$mysqli->connect_error));
		}
	}

	/**
	 * Executes a query than returns a data set - SELECT statements
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @static
	 * @param $query string
	 * @return array
	 */
	public static function executeQuery($query)
	{
		self::connect();

		$result_data = array();
		if($result = self::$mysqli->query($query))
		{
			while($obj = $result->fetch_object())
			{
				$result_data[] = $obj;
			}

			/* free result set */
			$result->close();
		}

		return $result_data;
	}

	/**
	 * Executes a query that does not return anything
	 * e.g. INSERT, UPDATE, DELETE, CREATE TABLE, TRUNCATE
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @static
	 * @param $query string
	 * @return bool
	 */
	public static function executeNonQuery($query)
	{
		self::connect();

		if(self::$mysqli->query($query) === TRUE)
		{
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Closes the connection to the database
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function __destruct()
	{
		self::$mysqli->close();
	}
}

/* End of file Database.php */
/* Location: ./modules/shop/tests/lib/Database.php */
