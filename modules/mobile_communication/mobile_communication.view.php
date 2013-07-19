<?php
    /**
     * @class  memberView
     * @author Arnia (dev@karybu.org)
     * @brief View class of member module
     **/

    class mobile_communicationView extends mobile_communication {

        function dispMobile_communicationManageCheckedDocumentResponse(){
            header('Content-Type: text/xml');
            echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
            echo "<response>\n";
            echo "<error>0</error>";
            echo "<message>success</message>";
            echo "</response>\n";
            exit();
        }
                
    }
?>
