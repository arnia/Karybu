<?php

namespace Flow;

use Flow\FlowException;
use Flow\FlowActionRegister;
use Flow\ACTION_TYPES;
use \stdClass;
use \DOMXPath;
use \DOMDocument;
use \Exception;
use \Context;

define (_FLOW_SCHEMA_PATH_, __DIR__.'\\..\\conf\\flow.xsd');

class FController implements FlowActionRegister{
	
	const FROM_PARAM = "FROM";
    const NAVIGATION_PARAM = "NAVIGATION";
	const NAVIGATION_NEXT_VALUE = "NEXT";
	const NAVIGATION_PREVIOUS_VALUE = "PREVIOUS";
	
	/** @var $confDom DOMDocument */
	private $confDom;
    /** @var $confDomXPath DOMXPath */
	private $confDomXPath;

    private $oView;
    private $formUrlPrefix;

	private $forms = array();

    private $nextActions = array();
    private $previousActions = array();

	public function __construct($configFile, $oView){
        if (!isset($oView)){
            throw new FlowException("Parent view must be set");
        }
        $this->oView = $oView;

        // $this->formUrlPrefix = $this->getSiteUrl();
		$this->confDom = new DOMDocument();
		$this->confDom->load($configFile);
		$validated = @$this->confDom->schemaValidate(_FLOW_SCHEMA_PATH_);
		if(!$validated){
			throw new FlowException("Specified flow doesn't comply with the schema");
		}
		$this->confDomXPath = new DOMXPath($this->confDom);
		
		// load form flow information
		$this->loadFlowInfo();
	}

    public function setPrefixToReachAForm($prefix){
        $this->formUrlPrefix = $prefix;
    }

    public function registerAction($ACTION_TYPE, $form, $action, $priority = 0)
    {
        if (!isset($this->forms[$form])){
            throw new FlowException("Specified form cannot be found in flow's configuration!");
        }
        if (!is_callable($action)){
            throw new FlowException("Specified action $action is not a callable!");
        }

        if ($ACTION_TYPE & ACTION_TYPES::ON_NEXT){
            $actionStructure = new stdClass();
            $actionStructure->action = $action;
            $actionStructure->priority = $priority;
            $this->nextActions[$form] = $actionStructure;
        }
        if ($ACTION_TYPE & ACTION_TYPES::ON_PREVIOUS){
            $actionStructure = new stdClass();
            $actionStructure->action = $action;
            $actionStructure->priority = $priority;
            $this->previousActions[$form] = $actionStructure;
        }
    }

    public function direct(){
		// where are we?
        $currentFormName = $this->getCurrentForm();
		
		// what direction (NEXT/ PREVIOUS)?
        $isNext = $this->isNext();
		
		// retrieve fields values
        $fieldValues = $this->getFieldsValues($currentFormName, $isNext);

        // TODO apply a ruleset mechanism (to be defined in conf file and used

        // compute following step
        $followingStep = $this->getFollowing($fieldValues, $currentFormName, $isNext);

		// call model actions
        $args = $this->callActions($fieldValues, $currentFormName, $isNext);

        // collect actions output, as input for the following form
        foreach(array_keys($args) as $key){
            Context::set($key, $args[$key]);
        }

		// go to the following step
        if(stristr($followingStep, 'URL|', true)===''){
            // following step is a generic URL
            $this->oView->setRedirectUrl(substr($followingStep, 4));
        }else{
            // following step is a form
            $this->oView->setTemplateFile($followingStep);
        }

	}
	
	// =========== PRIVATE AREA ==============
	
	private function loadFlowInfo(){
		/** @var $formNodes DOMNodeList */
		$formNodes = $this->confDomXPath->query("/qub:flow/form");
		foreach ($formNodes as $formNode){
			$form = new stdClass();
			$form->id = $formNode->getAttribute('id');
			// fields
			$form->fields = array();
			$fieldNodes = $formNode->getElementsByTagName('field');
			foreach ($fieldNodes as $fieldNode){
				$field = new stdClass();
				$field->name = $fieldNode->getAttribute('name');
				$field->required = $fieldNode->getAttribute('required');
				$form->fields[] = $field;
			}
			// navigation conditions...
			$form->nexts = array();
			$form->previouses = array();
			// ... next steps
			$nextNodes = $this->confDomXPath->query('navigation/next', $formNode);
			foreach($nextNodes as $nextNode){
				$next = new stdClass();
				$next->formId = $nextNode->getElementsByTagName('form')->item(0)->nodeValue;
				$next->url = $nextNode->getElementsByTagName('url')->item(0)->nodeValue;
				$next->condition = $nextNode->getElementsByTagName('condition')->item(0)->nodeValue;
				$form->nexts[] = $next;
			}
			// ... previous steps
			$prevNodes = $this->confDomXPath->query('navigation/previous', $formNode);
			foreach($prevNodes as $prevNode){
				$previous = new stdClass();
				$previous->formId = $prevNode->getElementsByTagName('form')->item(0)->nodeValue;
				$previous->url = $prevNode->getElementsByTagName('url')->item(0)->nodeValue;
				$previous->condition = $prevNode->getElementsByTagName('condition')->item(0)->nodeValue;
				$form->previouses[] = $previous;
			}
			$this->forms[$form->id] = $form;
		}
	}

	private function getCurrentForm(){
		$reqVars = Context::getRequestVars();
        $reqVars = get_object_vars($reqVars);
        if (isset($reqVars[self::FROM_PARAM])){
            $receivedFrom = $reqVars[self::FROM_PARAM];
            if (isset($this->forms[$receivedFrom])){
                return $receivedFrom;
            }else{
                throw new FlowException("Current form not acceptable");
            }
        }else{
            throw new FlowException("Current form not specified");
        }
	}

    private function isNext(){
        $reqVars = Context::getRequestVars();
        $reqVars = get_object_vars($reqVars);
        if ($reqVars[self::NAVIGATION_PARAM] == self::NAVIGATION_NEXT_VALUE){
            return true;
        }
        if ($reqVars[self::NAVIGATION_PARAM] == self::NAVIGATION_PREVIOUS_VALUE){
            return false;
        }
        throw new FlowException("Direction (NEXT/ PREVIOUS not specified");
    }

    private function getFieldsValues($currentFormName, $isNext){
        $result = array();
        $reqVars = get_object_vars(Context::getRequestVars());
        $form = $this->forms[$currentFormName];
        foreach($form->fields as $field){
            $receivedField = $reqVars[$field->name];
            if ($isNext && $field->required && !isset($receivedField)){
                throw new FlowException("Field $field->name is required, but isn't set");
            }
            $result[$field->name] = $receivedField;
        }
        return $result;
    }

    private function getFollowing($args, $currentFormName, $isNext){
        if ($isNext){
            $navigations = $this->forms[$currentFormName]->nexts;
        }else{
            $navigations = $this->forms[$currentFormName]->previouses;
        }

        foreach(array_keys($args) as $varName){
            $$varName = $args[$varName];
        }

        foreach($navigations as $navigation){
            // TODO make additional checks since eval is a dangerous function :-)
            $condResult = eval("return $navigation->condition;");
            if ($condResult){
                if (isset($navigation->formId)){
                    return $this->formUrlPrefix.$navigation->formId;
                }else if (isset($navigation->url)){
                    return 'URL|'.$navigation->url;
                }else{
                    throw new FlowException("The following step cannot be computed for $navigation->form");
                }
            }
        }
    }

    private function callActions($args, $currentFormName, $isNext ){
        try{
            $actions = $isNext ? $this->nextActions[$currentFormName] : $this->previousActions[$currentFormName];
            if(!is_array($actions)){
                $actions = array($actions);
            }
            $orderedActions = array();
            foreach($actions as $actionStruct){
                $orderedActions[$actionStruct->priority][] = $actionStruct->action;
            }
            krsort($orderedActions);
            foreach(array_keys($orderedActions) as $priority){
                if (isset($orderedActions[$priority])){
                    if (is_array($orderedActions[$priority])){
                        $callableActions = $orderedActions[$priority];
                    }else{
                        $callableActions = array($orderedActions[$priority]);
                    }
                    foreach($callableActions as $callableAction){
                        if(!is_callable($callableAction)){
                            continue;
                        }
                        $result = call_user_func($callableAction, $args);
                        if (!result){
                            throw new FlowException("An error occurred while calling $callableAction on current $currentFormName form");
                        }else{
                            $args = $result;
                        }
                    }
                }
            }
            return $args;
        }catch(FlowException $fe){
            throw $fe;
        }catch(Exception $e){
            throw new FlowException($e->getMessage());
        };
    }

    private function getSiteUrl() {
            $num_args = func_num_args();
            $args_list = func_get_args();

            if(!$num_args) return Context::getRequestUri();

            $domain = array_shift($args_list);
            $num_args = count($args_list);

            return Context::getUrl($num_args, $args_list, $domain);
    }
}

// small test
//function test(){
//    $args = array('a'=>5, 'b'=>10, 'c'=>'trei zeci si sase');
//    foreach(array_keys($args) as $varName){
//        $$varName = $args[$varName];
//    }
//    echo "\na=$a\nb=$b\nc=$c";
//}
//test();

//$q1Controller = new FController('.\\Flow\\testConf.xml', new stdClass());
