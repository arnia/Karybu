<?php
/**
 * File containing the AbstractPlugin abstract class
 */
/**
 * Defines blueprint for plugins, be it shipping, payment, or something else
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 *
 */
abstract class AbstractPlugin extends BaseItem
{
	/** @var null Plugin instance id */
    public $id = NULL;
	/** @var int Module srl to which instance belongs */
    public $module_srl = 0;
	/** @var string User friendly name for instance */
    public $display_name;
	/** @var mixed Unique name = folder name */
    public $name;
	/** @var int Status - whether plugin is enabled or not */
    public $status = 0;
	/** @var int Whether plugin is set as default */
	public $is_default = 0;
	/** @var Plugin custom properties - contains a name-value array serialized */
    public $properties;

	/**
	 * Checks if custom plugin parameters are set and valid;
	 * If no validation is needed, just return true;
	 *
	 * @param string $error_message
	 * @return mixed
	 */
	public abstract function isConfigured(&$error_message = 'msg_invalid_request');

	/**
	 * Constructor
	 */
	public function __construct()
    {
        $this->name = $this->getName();
        $this->display_name = $this->getDisplayName();
    }

    /**
     * Returns the plugin's name
     * Defaults: Splits folder name into words and makes them uppercase
	 *
     * @return string
     */
    public function getDisplayName()
    {
        if(!isset($this->display_name))
        {
            $name = $this->getName();
            $this->display_name = ucwords(str_replace('_', ' ', $name));
        }
        return $this->display_name;
    }

    /**
     * Returns unique identifier for plugin
     * Represents the folder name where the plugin class is found
     */
    final public function getName()
    {
        if(!isset($this->name))
        {
            $plugin_class_directory_path = $this->getPluginDir();
            $folders = explode(DIRECTORY_SEPARATOR, $plugin_class_directory_path);
            $this->name = array_pop($folders);
        }
        return $this->name;
    }

    /**
     * Sets all properties at once
     *
     * @param $data
     */
    public function setProperties($data)
    {
        foreach($data as $property_name => $property_value)
        {
            $this->{$property_name} = $property_value; // If given property does not exist, __set and __get will be called
        }

		if($this->properties && !$data->properties)
		{
			$all_custom_properties_names = array_keys(get_object_vars($this->properties));
			foreach($all_custom_properties_names as $custom_property_name)
			{
				if(!isset($data->$custom_property_name))
				{
					unset($this->properties->$custom_property_name);
				}
			}
		}
    }

    /**
     * Check if plugin is enabled or not
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status ? TRUE : FALSE;
    }

	/**
	 * Check if plugin is marked as default
	 *
	 * @return bool
	 */
	public function isDefault()
	{
		return $this->is_default ? TRUE : FALSE;
	}

    /**
     * All custom plugin properties different than name, status etc. will
     * be saved ina generic array
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->properties->$name = $value;
    }

    /**
     * Get generic properties
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->properties->$name;
    }

	/**
	 * Properties
	 *
	 * @param $name
	 * @return mixed
	 */
	public function __isset($name)
	{
		return isset($this->properties->$name);
	}

    /**
     * Returns the current plugin directory
     *
     * @return string
     */
    protected function getPluginDir()
    {
        $reflector = new ReflectionClass(get_class($this));
        return dirname($reflector->getFileName());
    }

	/**
	 * Set current plugin as default
	 */
	public function makeDefault()
	{
		$this->repo->setDefault($this->name, $this->module_srl);
	}
}