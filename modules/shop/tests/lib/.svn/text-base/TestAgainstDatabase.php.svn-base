<?php
require_once "PHPUnit/Extensions/Database/TestCase.php";
require dirname(__FILE__) . '/Database.php';

/**
 * Base class for tests using a database
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
abstract class TestAgainstDatabase extends PHPUnit_Extensions_Database_TestCase
{

	protected $backupGlobals = FALSE;
	protected $backupStaticAttributes = FALSE;
	protected $preserveGlobalState = FALSE;

	/**
	 * Prepare runtime context - tell DB class about current db connection info
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	protected function setUp()
	{
		parent::setUp();

		$oContext = &Context::getInstance();

		$db_info = include dirname(__FILE__) . '/../config/db.config.php';

		$db = new stdClass();
		$db->master_db = $db_info;
		$db->slave_db = array($db_info);
		$oContext->setDbInfo($db);

		DB::getParser(TRUE);
	}

	/**
	 * Free resources - reset static DB and QueryParser
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	protected function tearDown()
	{
		unset($GLOBALS['__DB__']);

		parent::tearDown();
	}
}

/* End of file TestAgainstDatabase.php */
/* Location: ./modules/shop/tests/lib/TestAgainstDatabase.php */
