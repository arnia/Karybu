<?php
/**
 * File containing the AbstractPluginRepository class
 */
/**
 * Base class for repositories for plugin object
 *
 * Used for example for the Shipping methods and Payment methods
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
abstract class AbstractPluginRepository extends BaseRepository
{
	/**
	 * Returns path where plugins need to be placed in order to be loaded
	 *
	 * @return mixed
	 */
	abstract function getPluginsDirectoryPath();

	/**
	 * Name of the base class for a type of plugin; for example: ShippingMethodAbstract, PaymentMethodAbstract
	 *
	 * This is a class that all new plugins need to extend in order to be recognized
	 *
	 * @return mixed
	 */
	abstract function getClassNameThatPluginsMustExtend();

	/**
	 * Loads plugin properties from the database
	 *
	 * @param $name
	 * @param $module_srl
	 * @return mixed
	 */
	abstract protected function getPluginInfoFromDatabase($name, $module_srl);

	/**
	 * Updates plugin info
	 *
	 * @param $plugin
	 * @return mixed
	 */
	abstract protected function updatePluginInfo($plugin);

	/**
	 * Inserts a new plugin in the database
	 *
	 * @param AbstractPlugin $plugin
	 * @return mixed
	 */
	abstract protected function insertPluginInfo(AbstractPlugin $plugin);

	/**
	 * Deletes a plugin from the database
	 *
	 * @param $name
	 * @param $module_srl
	 * @return mixed
	 */
	abstract protected function deletePluginInfo($name, $module_srl);

	/**
	 * Returns a list of all plugins in the database
	 *
	 * @param $module_srl
	 * @param $args
	 * @return mixed
	 */
	abstract protected function getAllPluginsInDatabase($module_srl, $args);

	/**
	 * Returns a list of all enabled plugins in the database
	 *
	 * @param $module_srl
	 * @return mixed
	 */
	abstract protected function getAllActivePluginsInDatabase($module_srl);

	/**
	 * Method used for backwards compatibility - sets a module srl
	 * for plugins that don't have one
	 *
	 * @param $name
	 * @param $old_module_srl
	 * @param $new_module_srl
	 * @return mixed
	 */
	abstract protected function fixPlugin($name, $old_module_srl, $new_module_srl);

	/**
	 * Updates all plugins but one at once; used for making a module default,
	 * by first unsetting the default property of all plugins except the one updated
	 *
	 * @param $is_default
	 * @param $name
	 * @param $module_srl
	 * @return mixed
	 */
	abstract protected function updatePluginsAllButThis($is_default, $name, $module_srl);

	/**
	 * Returns a plugin instance given its name
	 *
	 * @param $plugin_name
	 * @param $module_srl
	 * @return mixed
	 * @throws ShopException
	 */
	protected function getPluginInstanceByName($plugin_name, $module_srl)
    {
        // Skip files (we are only interested in the folders)
        if(!is_dir($this->getPluginsDirectoryPath() . DIRECTORY_SEPARATOR . $plugin_name))
        {
            throw new ShopException("Given folder name is not a directory");
        }

        // Convert from under_scores to CamelCase in order to get class name
        $plugin_class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $plugin_name)));
        $plugin_class_path = $this->getPluginsDirectoryPath()
            . DIRECTORY_SEPARATOR . $plugin_name
            . DIRECTORY_SEPARATOR . $plugin_class_name . '.php';

        if(!file_exists($plugin_class_path)) {
            throw new ShopException("Plugin class was not found in given folder");
        };

        // Include class and check if it extends the required abstract class
        require_once $plugin_class_path;

        $plugin_instance = new $plugin_class_name;
        $class_name_that_plugin_must_extend = $this->getClassNameThatPluginsMustExtend();
        if(!($plugin_instance instanceof $class_name_that_plugin_must_extend))
        {
            throw new ShopException("Plugin class does not extend required $class_name_that_plugin_must_extend");
        };

        $plugin_instance->module_srl = $module_srl;
        return $plugin_instance;
    }

	/**
	 * Returns a list of all plugins in the plugins folder
	 *
	 * @return string[]
	 */
	private function getPluginsByFolder()
    {
        return FileHandler::readDir($this->getPluginsDirectoryPath());
    }

	/**
	 * Creates a new plugin instance and populates it with
	 * properties saved in the database
	 *
	 * @param $data
	 * @return mixed
	 */
	protected function getPluginInstanceFromProperties($data)
    {
        $data->properties = unserialize($data->props);
        unset($data->props);

        $plugin = $this->getPluginInstanceByName($data->name, $data->module_srl);
        $plugin->setProperties($data);
        return $plugin;
    }

	/**
	 * Returns a plugin instance
	 *
	 * @param $name
	 * @param $module_srl
	 * @return mixed
	 */
	public function getPlugin($name, $module_srl)
    {
        $data = $this->getPluginInfoFromDatabase($name, $module_srl);

        // Update code; add module srl to plugins that have module_srl = 0
        // TODO Remove this when releasing XE Shop
        if(!$data)
        {
            $data = $this->getPluginInfoFromDatabase($name, 0);
            if($data)
            {
                ShopLogger::log("Upgrading plugin $name - setting module_srl from 0 to $module_srl");
                $this->fixPlugin($data->name, 0, $module_srl);
                $data->module_srl = $module_srl;
            }
        }

        // If plugin exists in the database, return it as is
        if($data)
        {
            return $this->getPluginInstanceFromProperties($data);
        }

        // Otherwise, initialize it with info from the extension class and insert in database
        $plugin = $this->getPluginInstanceByName($name, $module_srl);

        $this->insertPlugin($plugin);

        return $this->getPlugin($name, $module_srl);
    }

	/**
	 * Installs a new plugin - that is saves its basic info in the database
	 * otherwise the plugin would just exist in disk
	 *
	 * @param $name
	 * @param $module_srl
	 * @return mixed
	 */
	public function installPlugin($name, $module_srl)
    {
        return $this->getPlugin($name, $module_srl);
    }

    /**
     * Returns all available plugins
     *
     * Looks in the database and also in the plugins folder to see
     * if any new extension showed up. If yes, also adds it in the database
     */
    public function getAvailablePlugins($module_srl)
    {
        // Scan through the plugins_shipping extension directory to retrieve available methods
        $extensions = $this->getPluginsByFolder();

        $plugins = array();
        foreach($extensions as $extension_name)
        {
            try
            {
                $plugins[] = $this->getPlugin($extension_name, $module_srl);
            }
            catch(Exception $e)
            {
                continue;
            }
        }

        return $plugins;
    }

    /**
     * Get all enabled plugins
     */
    public function getActivePlugins($module_srl)
    {
        $plugins_info = $this->getAllActivePluginsInDatabase($module_srl);

        $active_plugins = array();
        foreach($plugins_info as $data)
        {
            try
            {
				$plugin_instance = $this->getPluginInstanceFromProperties($data);
				if($plugin_instance->isConfigured())
				{
					$active_plugins[] = $plugin_instance;
				}
            }
            catch(Exception $e)
            {
                continue;
            }
        }

        return $active_plugins;
    }

	/**
	 *
	 * Updates a plugin
	 *
	 * Status: active = 1; inactive = 0
	 *
	 * @author Daniel Ionescu (dev@xpressengine.org)
	 * @param \AbstractPlugin $plugin
	 * @throws ShopException
	 * @return boolean
	 */
    public function updatePlugin(AbstractPlugin $plugin)
    {
        if(!isset($plugin->name))
        {
            throw new ShopException("Please provide the name of the element you want to update");
        }
        if(isset($plugin->properties) && !is_string($plugin->properties))
        {
            $serialized_properties = serialize($plugin->properties);
            $plugin->properties = $serialized_properties;
        }

        $this->updatePluginInfo($plugin);
    }

	/**
	 * Wrapper method
	 *
	 * @param $plugin
	 */
	public function insertPlugin($plugin)
    {
        $this->insertPluginInfo($plugin);
    }

	/**
	 * Wrapper method
	 *
	 * @param $name
	 * @param $module_srl
	 */
	public function deletePlugin($name, $module_srl)
    {
        $this->deletePluginInfo($name, $module_srl);
    }

	/**
	 * Sets a module as default
	 *
	 * @param $name
	 * @param $module_srl
	 * @return ArgumentException
	 */
	public function setDefault($name, $module_srl)
	{
		if(!isset($name) || !isset($module_srl))
		{
			return new ArgumentException("You must provide name and module_srl for making a plugin default.");
		}
		$plugin = $this->getPlugin($name, $module_srl);
		if(!$plugin->isActive())
		{
			return new ArgumentException("It is not allowed to set as default an inactive plugin");
		}

		// Update all other plugins with is_default = 0
		$this->updatePluginsAllButThis(0, $name, $module_srl);

		// Set this plugin is_default = 1
		$plugin->is_default = 1;
		$this->updatePlugin($plugin);
	}

	/**
	 * Returns the default plugin for a module_srl
	 *
	 * @param $module_srl
	 * @return mixed|null
	 */
	public function getDefault($module_srl)
	{
		$args = new stdClass();
		$args->is_default = 1;
		$plugin_info = $this->getAllPluginsInDatabase($module_srl, $args);
		if(!$plugin_info)
		{
			return NULL;
		}
		return $this->getPluginInstanceFromProperties($plugin_info[0]);
	}

    /**
     * Deletes plugins from DB if they do not have a folder with a corresponding name
     */
    public function sanitizePlugins($module_srl) {
        $pgByDatabase = $this->getAllPluginsInDatabase($module_srl, NULL);
        $pgByFolders = $this->getPluginsByFolder();

        foreach ($pgByDatabase as $obj) {
            if (!in_array($obj->name,$pgByFolders)) {
                $this->deletePlugin($obj->name, $module_srl);
            }
        }
    }
}

