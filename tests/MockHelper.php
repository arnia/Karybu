<?php

class MockHelper
{
    private $test = null;

    private $temp_class_name;
    private $temp_method_name;

    private $things_to_mock = array();

    public function __construct(PHPUnit_Framework_TestCase $test)
    {
        $this->test = $test;
    }

    public function method($class_name, $method_name)
    {
        if(!isset($this->things_to_mock[$class_name]))
            $this->things_to_mock[$class_name] = array();
        $this->things_to_mock[$class_name][$method_name] = null;

        $this->temp_class_name = $class_name;
        $this->temp_method_name = $method_name;

        return $this;
    }

    public function shouldReturn($value)
    {
        if($this->things_to_mock[$this->temp_class_name][$this->temp_method_name] == null)
            $this->things_to_mock[$this->temp_class_name][$this->temp_method_name] = new stdClass();

        $this->things_to_mock[$this->temp_class_name][$this->temp_method_name]->return = $value;

        return $this;
    }

    public function shouldReturnMockModuleController()
    {
        $moduleController = $this->test->getMock('moduleController', array('replaceDefinedLangCode'));
        $moduleController->expects($this->test->any())->method('replaceDefinedLangCode')
            ->will($this->test->returnCallback(function($name) { return $name;}));

        $this->shouldReturn($moduleController);

        return $this;
    }

    public function shouldBeCalledWith()
    {
        if($this->things_to_mock[$this->temp_class_name][$this->temp_method_name] == null)
            $this->things_to_mock[$this->temp_class_name][$this->temp_method_name] = new stdClass();

        $this->things_to_mock[$this->temp_class_name][$this->temp_method_name]->with = func_get_args();

        return $this;
    }

    /**
     * @param $times string once | never | any
     * @return MockHelper
     */
    public function shouldBeCalled($times)
    {
        if($this->things_to_mock[$this->temp_class_name][$this->temp_method_name] == null)
            $this->things_to_mock[$this->temp_class_name][$this->temp_method_name] = new stdClass();

        $this->things_to_mock[$this->temp_class_name][$this->temp_method_name]->expects = $times;

        return $this;
    }

    public function getMock($class_name, $constructor_args = null)
    {
        $mocked_methods = $this->things_to_mock[$class_name];
        $mocked_methods_names = $mocked_methods ? array_keys($mocked_methods) : array();
        /** @var $mock PHPUnit_Framework_MockObject_MockObject */
        $mock = null;
        if($constructor_args)
            $mock = $this->test->getMock($class_name, $mocked_methods_names, $constructor_args);
        else
            $mock = $this->test->getMock($class_name, $mocked_methods_names);

        if($mocked_methods) {
            foreach($mocked_methods as $mocked_method => $method_info)
            {
                if($method_info == null) continue;

                if(isset($method_info->expects))
                {
                    if($method_info->expects == 'any')
                        $expects = $this->test->any();
                    elseif($method_info->expects == 'once')
                        $expects = $this->test->once();
                    elseif($method_info->expects == 'never')
                        $expects = $this->test->never();
                    else
                        throw new InvalidArgumentException();
                }
                else {
                    $expects = $this->test->any();
                }

                /** @var $invocation_mocker PHPUnit_Framework_MockObject_Builder_InvocationMocker */
                $invocation_mocker = $mock->expects($expects)->method($mocked_method);

                if(isset($method_info->with))
                {
                    $args = array();
                    foreach($method_info->with as $value)
                    {
                        $args[] = $this->test->equalTo($value);
                    }

                    call_user_func_array(array($invocation_mocker, 'with'), $args);
                }

                if(isset($method_info->return))
                {
                    $invocation_mocker->will($this->test->returnValue($method_info->return));
                }
            }
        }

        return $mock;
    }




}