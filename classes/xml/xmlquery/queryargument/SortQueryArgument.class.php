<?php
	/**
	 * SortQueryArgument class
	 * @author Arnia (dev@karybu.org)
	 * @package /classes/xml/xmlquery/queryargument
	 * @version 0.1
	 */
	class SortQueryArgument extends QueryArgument{
			/**
			 * Change SortQueryArgument object to string
			 * @return string
			 */
            function toString(){
                $arg = sprintf("\n".'if (!isset($args->{\'%s\'})) {'."\n", $this->variable_name);
                $arg .= sprintf('$args->{\'%s\'} = null;'."\n", $this->variable_name);
                $arg .= '}';
                $arg .= sprintf("\n" . '${\'%s_argument\'} = new SortArgument(\'%s\', %s);' . "\n"
                                        , $this->argument_name
                                        , $this->argument_name
                                        , '$args->'.$this->variable_name);


                $arg .= $this->argument_validator->toString();

                $arg .= sprintf('if(!${\'%s_argument\'}->isValid()) return ${\'%s_argument\'}->getErrorMessage();' . "\n"
                                    , $this->argument_name
                                    , $this->argument_name
                                    );
                return $arg;
            }
        }
?>
