<?php
    /**
     * Admin model class of addon module
     * @author Arnia (dev@karybu.org)
     **/
    class addonAdminModel extends addon {

        /**
         * Initialization
		 *
		 * @return void
         **/
        function init() {
        }

        /**
         * Returns a path of addon
		 *
		 * @param string $addon_name Name to get path
		 * @return string Returns a path
         **/
        function getAddonPath($addon_name) {
            $class_path = sprintf('./addons/%s/', $addon_name);
            if(is_dir($class_path)) return $class_path;
            return "";
        }

		/**
		 * Get addon list for super admin
		 *
		 * @return Object
		 **/
		function getAddonListForSuperAdmin()
		{
			$addonList = $this->getAddonList(0, 'site');

			$oAutoinstallModel = &getModel('autoinstall');
			foreach($addonList as $key => $addon)
			{
				// get easyinstall remove url
				$packageSrl = $oAutoinstallModel->getPackageSrlByPath($addon->path);
				$addonList[$key]->remove_url = $oAutoinstallModel->getRemoveUrlByPackageSrl($packageSrl);

				// get easyinstall need update
				$package = $oAutoinstallModel->getInstalledPackages($packageSrl);
				$addonList[$key]->need_update = isset($package[$packageSrl]->need_update) ? $package[$packageSrl]->need_update : null;

				// get easyinstall update url
				if ($addonList[$key]->need_update == 'Y')
				{
					$addonList[$key]->update_url = $oAutoinstallModel->getUpdateUrlByPackageSrl($packageSrl);
				}
			}

			return $addonList;
		}

        /**
         * Returns addon list
		 *
		 * @param int $site_srl Site srl
		 * @param string $gtype site or global
		 * @return array Returns addon list
         **/
        function getAddonList($site_srl = 0, $gtype = 'site') {
            // Wanted to add a list of activated
            $inserted_addons = $this->getInsertedAddons($site_srl, $gtype);
            // Downloaded and installed add-on to the list of Wanted
            $searched_list = FileHandler::readDir('./addons','/^([a-zA-Z0-9-_]+)$/');
            $searched_count = count($searched_list);
            if(!$searched_count) return;
            sort($searched_list);

			$oAddonAdminController = &getAdminController('addon');

            for($i=0;$i<$searched_count;$i++) {
                // Add the name of
                $addon_name = $searched_list[$i];
				if($addon_name == "smartphone") continue;
                // Add the path (files/addons precedence)
                $path = $this->getAddonPath($addon_name);
                // Wanted information on the add-on
                unset($info);
                $info = $this->getAddonInfoXml($addon_name, $site_srl, $gtype);

                $info->addon = $addon_name;
                $info->path = $path;
                $info->activated = false;
				$info->mactivated = false;
				$info->fixed = false;
                // Check if a permossion is granted entered in DB
                if(!in_array($addon_name, array_keys($inserted_addons))) {
                    // If not, type in the DB type (model, perhaps because of the hate doing this haneungeo .. ㅡ. ㅜ)
                    $oAddonAdminController->doInsert($addon_name, $site_srl, $type);
                // Is activated
                } else {
                    if($inserted_addons[$addon_name]->is_used=='Y') $info->activated = true;
                    if($inserted_addons[$addon_name]->is_used_m=='Y') $info->mactivated = true;
					if ($gtype == 'global' && $inserted_addons[$addon_name]->is_fixed == 'Y') $info->fixed = true;
                }

                $list[] = $info;
            }
            return $list;
        }

        /**
         * Returns a information of addon
		 *
		 * @param string $addon Name to get information
		 * @param int $site_srl Site srl
		 * @param string $gtype site or global
		 * @return object Returns a information
         **/
        function getAddonInfoXml($addon, $site_srl = 0, $gtype = 'site') {
            // Get a path of the requested module. Return if not exists.
            $addon_path = $this->getAddonPath($addon);
            if(!$addon_path) {
                return;
            }
            // Read the xml file for module skin information
            $xml_file = sprintf("%sconf/info.xml", $addon_path);
            if(!file_exists($xml_file)) {
                return;
            }

            $oXmlParser = new XmlParser();
            $tmp_xml_obj = $oXmlParser->loadXmlFile($xml_file);
            $xml_obj = $tmp_xml_obj->addon;

            if(!$xml_obj) return;


            // DB is set to bring history
            $db_args = new stdClass();
            $db_args->addon = $addon;
            if($gtype == 'global') $output = executeQuery('addon.getAddonInfo',$db_args);
            else {
                $db_args->site_srl = $site_srl;
                $output = executeQuery('addon.getSiteAddonInfo',$db_args);
            }
            if (!empty($output->data->extra_vars)) {
                $extra_vals = @unserialize($output->data->extra_vars);
            }
            else {
                $extra_vals = new stdClass();
            }
            $addon_info = new stdClass();
            if(!empty($extra_vals->mid_list)) {
                $addon_info->mid_list = $extra_vals->mid_list;
            } else {
                $addon_info->mid_list = array();
            }

			if(!empty($extra_vals->xe_run_method))
			{
				$addon_info->xe_run_method = $extra_vals->xe_run_method;
			}


            // Add information
            if(!empty($xml_obj->version) && !empty($xml_obj->attrs->version) && $xml_obj->attrs->version == '0.2') {
                // addon format v0.2
                if (isset($xml_obj->date->body)) {
                    list($y, $m, $d) = sscanf($xml_obj->date->body, '%d-%d-%d');
                    $addon_info->date = sprintf('%04d%02d%02d', $y, $m, $d);
                }
                else {
                    $addon_info->date = null;
                }

                $addon_info->addon_name = $addon;
                $addon_info->title = isset($xml_obj->title->body) ? $xml_obj->title->body : '';
                $addon_info->description = isset($xml_obj->description->body) ? trim($xml_obj->description->body) : null;
                $addon_info->version = isset($xml_obj->version->body) ? $xml_obj->version->body : null;
                $addon_info->homepage = isset($xml_obj->link->body) ? $xml_obj->link->body : null;
                $addon_info->license = isset($xml_obj->license->body) ? $xml_obj->license->body : null;
                $addon_info->license_link = isset($xml_obj->license->attrs->link) ? $xml_obj->license->attrs->link : null;
                if (isset($xml_obj->author)) {
                    if(!is_array($xml_obj->author)) {
                        $author_list[] = $xml_obj->author;
                    }
                    else {
                        $author_list = $xml_obj->author;
                    }
                }
                else {
                    $author_list = array();
                }
                $addon_info->author = array();
                foreach($author_list as $author) {
                    unset($author_obj);
                    $author_obj = new stdClass();
                    $author_obj->name = isset($author->name->body) ? $author->name->body : null;
                    $author_obj->email_address = isset($author->attrs->email_address) ? $author->attrs->email_address : null;
                    $author_obj->homepage = isset($author->attrs->link) ? $author->attrs->link : null;
                    $addon_info->author[] = $author_obj;
                }

                // Expand the variable order
                if(!empty($xml_obj->extra_vars)) {
                    $extra_var_groups = isset($xml_obj->extra_vars->group) ? $xml_obj->extra_vars->group : null;
                    if(!$extra_var_groups) {
                        $extra_var_groups = $xml_obj->extra_vars;
                    }
                    if(!is_array($extra_var_groups)) {
                        $extra_var_groups = array($extra_var_groups);
                    }

                    foreach($extra_var_groups as $group) {
                        $extra_vars = isset($group->var) ? $group->var : null;
                        if(!is_null($extra_vars) && !is_array($group->var)) {
                            $extra_vars = array($group->var);
                        }
                        if (!is_null($extra_vars)) {
                            foreach($extra_vars as $key => $val) {
                                unset($obj);
                                $obj = new stdClass();
                                if(empty($val->attrs->type)) {
                                    $val->attrs->type = 'text';
                                }

                                $obj->group = isset($group->title->body) ? $group->title->body : null;
                                $obj->name = isset($val->attrs->name) ? $val->attrs->name : null;
                                $obj->title = isset($val->title->body) ? $val->title->body : null;
                                $obj->type = isset($val->attrs->type) ? $val->attrs->type : null;
                                $obj->description = isset($val->description->body) ? $val->description->body : null;
                                if($obj->name && isset($extra_vals->{$obj->name})) {
                                    $obj->value = $extra_vals->{$obj->name};
                                }
                                else {
                                    $obj->value = null;
                                }
                                if(strpos($obj->value, '|@|') != false) {
                                    $obj->value = explode('|@|', $obj->value);
                                }
                                if($obj->type == 'mid_list' && !is_array($obj->value)) {
                                    $obj->value = array($obj->value);
                                }

                                // 'Select'type obtained from the option list.
                                if(!empty($val->options) && !is_array($val->options))
                                {
                                    $val->options = array($val->options);
                                }
                                else {
                                    $val->options = array();
                                }
                                if (count($val->options)) {
                                    $obj->options = array();
                                }
                                for($i = 0, $c = count($val->options); $i < $c; $i++) {
                                    $obj->options[$i] = new stdClass();
                                    $obj->options[$i]->title = $val->options[$i]->title->body;
                                    $obj->options[$i]->value = $val->options[$i]->attrs->value;
                                }

                                $addon_info->extra_vars[] = $obj;
                            }
                        }
                    }
                }

                // history
                if(!empty($xml_obj->history)) {
                    if(!is_array($xml_obj->history)) {
                        $history[] = $xml_obj->history;
                    }
                    else {
                        $history = $xml_obj->history;
                    }

                    foreach($history as $item) {
                        unset($obj);
                        $obj = new stdClass();
                        if(!empty($item->author)) {
                            if (!is_array($item->author)) {
                                $obj->author_list[] = $item->author;
                            }
                            else {
                                $obj->author_list = $item->author;
                            }
                            if (count($obj->author_list)) {
                                $obj->author[] = array();
                                foreach($obj->author_list as $author) {
                                    unset($author_obj);
                                    $author_obj = new stdClass();
                                    $author_obj->name = isset($author->name->body) ? $author->name->body : null;
                                    $author_obj->email_address = isset($author->attrs->email_address) ? $author->attrs->email_address : null;
                                    $author_obj->homepage = isset($author->attrs->link) ? $author->attrs->link : null;
                                    $obj->author[] = $author_obj;
                                }
                            }
                        }

                        $obj->name = isset($item->name->body) ? $item->name->body : null;
                        $obj->email_address = isset($item->attrs->email_address) ? $item->attrs->email_address : null;
                        $obj->homepage = isset($item->attrs->link) ? $item->attrs->link : null;
                        $obj->version = isset($item->attrs->version) ? $item->attrs->version : null;
                        $obj->date = isset($item->attrs->date) ? $item->attrs->date : null;
                        $obj->description = isset($item->description->body) ? $item->description->body : null;

                        if(!empty($item->log)) {
                            if (!is_array($item->log)) {
                                $obj->log[] = $item->log;
                            }
                            else {
                                $obj->log = $item->log;
                            }

                            foreach($obj->log as $log) {
                                unset($log_obj);
                                $log_obj = new stdClass();
                                $log_obj->text = isset($log->body) ? $log->body : null;
                                $log_obj->link = isset($log->attrs->link) ? $log->attrs->link : null;
                                $obj->logs[] = $log_obj;
                            }
                        }

                        $addon_info->history[] = $obj;
                    }
                }


            } else {
                // addon format 0.1
                $addon_info->addon_name = $addon;
                $addon_info->title = $xml_obj->title->body;
                $addon_info->description = trim($xml_obj->author->description->body);
                $addon_info->version = $xml_obj->attrs->version;
                sscanf($xml_obj->author->attrs->date, '%d. %d. %d', $date_obj->y, $date_obj->m, $date_obj->d);
                $addon_info->date = sprintf('%04d%02d%02d', $date_obj->y, $date_obj->m, $date_obj->d);
                $author_obj->name = $xml_obj->author->name->body;
                $author_obj->email_address = $xml_obj->author->attrs->email_address;
                $author_obj->homepage = $xml_obj->author->attrs->link;
                $addon_info->author[] = $author_obj;

                if($xml_obj->extra_vars) {
                    // Expand the variable order
                    $extra_var_groups = $xml_obj->extra_vars->group;
                    if(!$extra_var_groups) $extra_var_groups = $xml_obj->extra_vars;
                    if(!is_array($extra_var_groups)) $extra_var_groups = array($extra_var_groups);
                    foreach($extra_var_groups as $group) {
                        $extra_vars = $group->var;
                        if(!is_array($group->var)) $extra_vars = array($group->var);

                        foreach($extra_vars as $key => $val) {
                            unset($obj);

                            $obj->group = $group->title->body;
                            $obj->name = $val->attrs->name;
                            $obj->title = $val->title->body;
                            $obj->type = $val->type->body ? $val->type->body : 'text';
                            $obj->description = $val->description->body;
							if($obj->name)
							{
                            	$obj->value = $extra_vals->{$obj->name};
							}
                            if(strpos($obj->value, '|@|') != false) { $obj->value = explode('|@|', $obj->value); }
                            if($obj->type == 'mid_list' && !is_array($obj->value)) { $obj->value = array($obj->value); }
                            // 'Select'type obtained from the option list.
                            if($val->options && !is_array($val->options))
							{
								$val->options = array($val->options);
							}

							for($i = 0, $c = count($val->options); $i < $c; $i++) {
								$obj->options[$i]->title = $val->options[$i]->title->body;
								$obj->options[$i]->value = $val->options[$i]->value->body;
							}

                            $addon_info->extra_vars[] = $obj;
                        }
                    }
                }

            }



            return $addon_info;
        }

        /**
         * Returns activated addon list
		 *
		 * @param int $site_srl Site srl
		 * @param string $gtype site or global
		 * @return array Returns list
         **/
        function getInsertedAddons($site_srl = 0, $gtype = 'site') {
            $args = new stdClass();
            $args->list_order = 'addon';
            if($gtype == 'global') $output = executeQuery('addon.getAddons', $args);
            else {
                $args->site_srl = $site_srl;
                $output = executeQuery('addon.getSiteAddons', $args);
            }
            if(!$output->data) return array();
            if(!is_array($output->data)) $output->data = array($output->data);

            $activated_count = count($output->data);
            $addon_list = array();
            for($i=0;$i<$activated_count;$i++) {
                $addon = $output->data[$i];
                $addon_list[$addon->addon] = $addon;
            }
            return $addon_list;
        }

        /**
         * Returns whether to activate
		 *
		 * @param string $addon Name to check
		 * @param int $site_srl Site srl
		 * @param string $type pc or mobile
		 * @param string $gtype site or global
		 * @return bool If addon is activated returns true. Otherwise returns false.
         **/
        function isActivatedAddon($addon, $site_srl = 0, $type = "pc", $gtype = 'site') {
            $args->addon = $addon;
            if($gtype == 'global') {
				if($type == "pc") $output = executeQuery('addon.getAddonIsActivated', $args);
				else $output = executeQuery('addon.getMAddonIsActivated', $args);
			}
            else {
                $args->site_srl = $site_srl;
				if($type == "pc") $output = executeQuery('addon.getSiteAddonIsActivated', $args);
				else $output = executeQuery('addon.getSiteMAddonIsActivated', $args);
            }
            if($output->data->count>0) return true;
            return false;
        }

    }
?>
