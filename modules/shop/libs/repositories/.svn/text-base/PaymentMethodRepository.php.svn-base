<?php
/**
 * File containing the PaymentMethodRepository class
 */
/**
 * Manages the payment plugins
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class PaymentMethodRepository extends AbstractPluginRepository
{
	/**
	 * Folder where the payment plugins are located
	 *
	 * @return mixed|string
	 */
	public function getPluginsDirectoryPath()
    {
        return _XE_PATH_ . 'modules/shop/plugins_payment';
    }

	/**
	 * Class that all payment plugins must extend
	 *
	 * @return mixed|string
	 */
	public function getClassNameThatPluginsMustExtend()
    {
        return "PaymentMethodAbstract";
    }

	/**
	 * Returns plugin properties from the database
	 *
	 * @param $name
	 * @param $module_srl
	 * @return mixed
	 */
	protected function getPluginInfoFromDatabase($name, $module_srl)
    {
        $args = new stdClass();
        $args->name = $name;
        $args->module_srl = $module_srl;

        $output = $this->query('getPaymentMethod', array('name'=>$name, 'module_srl'=>$module_srl));
        return $output->data;
    }

	/**
	 * Updates the payment plugin module_srl
	 *
	 * @param $name
	 * @param $old_module_srl
	 * @param $new_module_srl
	 * @return mixed|void
	 */
	protected function fixPlugin($name, $old_module_srl, $new_module_srl)
    {
        $this->query('fixPaymentMethod', array('name' => $name, 'module_srl' => $new_module_srl, 'source_module_srl' => $old_module_srl));
    }

	/**
	 * Updates plugin properties
	 *
	 * @param $payment_method
	 * @return mixed|void
	 * @throws ShopException
	 */
	protected function updatePluginInfo($payment_method)
    {
        $output = executeQuery('shop.updatePaymentMethod', $payment_method);

        if(!$output->toBool()) {
            throw new ShopException($output->getMessage(), $output->getError());
        }
    }

	/**
	 * Inserts a plugin in the database
	 *
	 * @param AbstractPlugin $payment_method
	 * @return mixed|void
	 * @throws ShopException
	 */
	protected function insertPluginInfo(AbstractPlugin $payment_method)
    {
        $payment_method->id = getNextSequence();
        $output = executeQuery('shop.insertPaymentMethod', $payment_method);
        if(!$output->toBool()) {
            throw new ShopException($output->getMessage(), $output->getError());
        }
    }

	/**
	 * Deletes a plugin from the database
	 *
	 * @param $name
	 * @param $module_srl
	 * @return mixed
	 * @throws ShopException
	 */
	protected function deletePluginInfo($name, $module_srl)
    {
        $args = new stdClass();
        $args->name = $name;
        $args->module_srl = $module_srl;
        $output = executeQuery('shop.deletePaymentMethod',$args);
        if (!$output->toBool()) {
            throw new ShopException($output->getMessage(), $output->getError());
        }

        return $output->data;
    }

	/**
	 * Returns all plugins in the database
	 *
	 * @param $module_srl
	 * @param $args
	 * @return mixed
	 * @throws ShopException
	 */
	protected function getAllPluginsInDatabase($module_srl, $args)
    {
        if(!$args) $args = new stdClass();
        $args->module_srl = $module_srl;
        $output = executeQueryArray('shop.getPaymentMethods', $args);

        if (!$output->toBool())
        {
            throw new ShopException($output->getMessage(), $output->getError());
        }

        return $output->data;
    }

	/**
	 * Returns all enabled payment methods from the database
	 *
	 * @param $module_srl
	 * @return mixed
	 * @throws ShopException
	 */
	protected function getAllActivePluginsInDatabase($module_srl)
    {
        $args = new stdClass();
        $args->status = 1;
        $args->module_srl = $module_srl;
        $output = executeQueryArray('shop.getPaymentMethods', $args);

        if (!$output->toBool())
        {
            throw new ShopException($output->getMessage(), $output->getError());
        }

        return $output->data;
    }

	/**
	 * Gets a payment method instance
	 *
	 * @param $name
	 * @param $module_srl
	 * @return mixed
	 */
	public function getPaymentMethod($name, $module_srl)
    {
        return $this->getPlugin($name, $module_srl);
    }

	/**
	 * Installs a new payment method
	 *
	 * @param $name
	 * @param $module_srl
	 * @return mixed
	 */
	public function installPaymentMethod($name, $module_srl)
    {
        return $this->getPaymentMethod($name, $module_srl);
    }

    /**
     * Returns all available payment methods
     *
     * Looks in the database and also in the plugins_payment folder to see
     * if any new extension showed up. If yes, also adds it in the database
     */
    public function getAvailablePaymentMethods($module_srl)
    {
        return $this->getAvailablePlugins($module_srl);
    }

     /**
      * Updates a payment method
      *
      * Status: active = 1; inactive = 0
      *
      * @author Daniel Ionescu (dev@xpressengine.org)
      * @param  $payment_method
      * @throws exception
      * @return boolean
     */
    public function updatePaymentMethod($payment_method) {
       $this->updatePlugin($payment_method);
    }

    /**
     * Inserts a new payment method
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @param  args
     * @throws exception
     * @return boolean
     */
    public function insertPaymentMethod($args)
    {
        $this->insertPlugin($args);
    }

	/**
	 * Get active payment methods
	 *
	 * @author Daniel Ionescu (dev@xpressengine.org)
	 *
	 * @param $module_srl
	 * @return object
	 */
    public function getActivePaymentMethods($module_srl) {
        return $this->getActivePlugins($module_srl);
    }

    /**
     * Deletes a  payment method
     */
    public function deletePaymentMethod($name, $module_srl) {
        $this->deletePlugin($name, $module_srl);
    }

	/**
	 * Removes plugin info from the database if the plugin
	 * folder no longer exists
	 *
	 * @param $module_srl
	 */
	public function sanitizePaymentMethods($module_srl) {
        $this->sanitizePlugins($module_srl);
    }

	/**
	 * Updates properties of all modules but one
	 *
	 * @param $is_default
	 * @param $name
	 * @param $module_srl
	 * @return mixed|void
	 */
	protected function updatePluginsAllButThis($is_default, $name, $module_srl)
	{
		$args = new stdClass();
		$args->except_name = $name;
		$args->module_srl = $module_srl;
		$args->is_default = 0;
		$this->query('shop.updatePaymentMethods', $args);
	}

}
