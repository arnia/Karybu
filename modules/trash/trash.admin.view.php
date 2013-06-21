<?php
/**
 * trashAdminView class
 * Admin view class of the trash module
 *
 * @author Arnia (dev@karybu.org)
 * @package /modules/trash
 * @version 0.1
 */
class trashAdminView extends trash {
	/**
	 * Initialization
	 * @return void
	 */
	function init() {
		// Specify the path to the template (tpl board administrator for collection of templates and release)
		$template_path = sprintf("%stpl/",$this->module_path);
		$this->setTemplatePath($template_path);
	}
    function getGrid(){
        global $lang;
        $grid = new \Karybu\Grid\Backend();
        $grid->setId('trashListTable');
        $grid->setMassSelectName('cart');
        $grid->addColumn('title', 'text', array(
            'index' => 'title',
            'header'=> $lang->document,
            'length'=>'40'
        ));
        $grid->addColumn('originModule', 'options', array(
            'index' => 'originModule',
            'header'=> $lang->origin_module_type,
            'options'=>array(
                'document'=>$lang->document,
                'comment'=>$lang->comment,
            ),
            'show_raw_value'=>true,
            'sort_index'=>'origin_module'
        ));
        $grid->addColumn('nickName', 'member', array(
            'index' => 'nickName',
            'header'=> $lang->nick_name,
            'masked'=>true,
            'title'=>'Info',
            'member_key'=>'removerSrl',
            'sort_index'=>'nick_name'
        ));
        $grid->addColumn('regdate', 'date', array(
            'index' => 'regdate',
            'header'=> $lang->trash_date,
            'format'=>'Y-m-d H:i:s'
        ));
        $grid->addColumn('ipaddress', 'text', array(
            'index' => 'ipaddress',
            'header'=> $lang->ipaddress,
        ));
        $grid->addColumn('description', 'text', array(
            'index' => 'description',
            'header'=> $lang->trash_description,
            'length'=>'40'
        ));
        return $grid;
    }
	/**
	 * Trash list
	 * @return void
	 */
	function dispTrashAdminList() {
        $args = new stdClass();
		$args->page = Context::get('page'); // /< Page
		$args->list_count = 10; // /< the number of posts to display on a single page
		$args->page_count = 10; // /< the number of pages that appear in the page navigation

		$args->search_target = Context::get('search_target'); // /< search (title, contents ...)
		$args->search_keyword = Context::get('search_keyword'); // /< keyword to search
        $grid = $this->getGrid();

        $sortIndex = Context::get('sort_index');
        $grid->setSortIndex($sortIndex);
        //$args->sort_index = 'list_order'; // /< Sorting values
        $args->sort_index = $grid->getSortIndex();
        if (empty($args->sort_index)){
            $args->sort_index = 'list_order';
        }
        $sortOrder = Context::get('sort_order');
        $grid->setSortOrder($sortOrder);
        $args->sort_order = $grid->getSortOrder();


		$oTrashModel = getModel('trash');
		$output = $oTrashModel->getTrashList($args);
        if (isset($output->data)){
            foreach ($output->data as $key=>$value){
                $output->data[$key]->mass_select_value = $value->trashSrl;
            }
        }

		// for no text comment language and for document manange language
		$oCommentModel = &getModel('comment');
		$oDocumentModel = &getModel('document');

		Context::set('trash_list', $output->data);
		Context::set('total_count', $output->total_count);
		Context::set('total_page', $output->total_page);
		Context::set('page', $output->page);
		Context::set('page_navigation', $output->page_navigation);
        $grid->setRows($output->data);
        $grid->setTotalCount($output->total_count);
        $grid->setTotalPages($output->total_page);
        $grid->setCurrentPage($output->page);
        Context::set('grid', $grid);
		// Specify the template file
		$this->setTemplateFile('trash_list');
	}
}
?>
