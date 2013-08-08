<?php
define('XE_LUCENE_PATH', __DIR__ . '/Search/Lucene');

require_once('Exception.php');
require_once('Search/Exception.php');
require_once('Search/Lucene.php');

function isCli() {

    if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
        return true;
    } else {
        return false;
    }
}

if (isCli()) {

    function randword($minLen, $maxLen) {
        $arr = str_split('ABCDEFGHIJKLMNOPabcdefghijklmnopqrstuvxyz');
        shuffle($arr);
        $len = rand($minLen, $maxLen);
        $arr = array_slice($arr, 0, $len);
        $str = implode('', $arr);
        return $str;
    }

    function randText($wordsNumber=100, $minLen=3, $maxLen=12) {
        $words = array();
        for ($i = 0; $i < $wordsNumber; $i++) {
            $words[] = randword($minLen, $maxLen);
        }
        return implode(' ', $words);
    }


    $dir = '/tmp/search_comments';

    Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8());

    if (false) {

        $index = Zend_Search_Lucene::create($dir);
        for ($i=0; $i<20; $i++) {
            //echo "$i\n";
            $doc = new Zend_Search_Lucene_Document();
            $doc->addField(Zend_Search_Lucene_Field::Text('title', randText(5, 3, 8)));
            $doc->addField(Zend_Search_Lucene_Field::Unstored('content', $txt = randText(130, 3, 11)));
            $index->addDocument($doc);
        }

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Text('title', 'documentul 1'));
        $doc->addField(Zend_Search_Lucene_Field::Unstored('content', 'behehe alala uiuiuiuiui hyhyhyhyhy'));
        $index->addDocument($doc);

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Text('title', 'o alta chestie'));
        $doc->addField(Zend_Search_Lucene_Field::Unstored('content', 'hdashdjksh ajkd jaskd ghjk asjkd jk'));
        $index->addDocument($doc);

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Text('title', '文件编辑器 chestie'));
        $doc->addField(Zend_Search_Lucene_Field::Unstored('content', 'hdashdjksh ajkd 编码格式 jaskd ghjk asjkd 文件编辑器 jk'));
        $index->addDocument($doc);

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Text('title', '编码格式 ceva'));
        $doc->addField(Zend_Search_Lucene_Field::Unstored('content', 'hdash 文件编辑器 djksh ajkd 编码格式 jaskd ghjk asjkd jk'));
        $index->addDocument($doc);

        unset($doc, $i);
    }
    else {
        $index = Zend_Search_Lucene::open($dir);
        $hits = $index->find('comentariu');
        foreach ($hits as $hit) {
            echo "{$hit->srl}\n";
        }
        $index->optimize();
    }

}
/*

class mySearchIndex
{

    public static function getIndexLocation()
    {
        $index_location = SF_ROOT_DIR.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'search_index';
        return $index_location;
    }

    public static function getIndexAnalyzer()
    {
        $index_analyzer = new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num();
        return $index_analyzer;
    }

    public static function BuildIndex()
    {

        $index = Zend_Search_Lucene::create(self::getIndexLocation());
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(self::getIndexAnalyzer());
        $my_symfony_objects = mySymfonyObjectPeer::doSelect(new Criteria());
        foreach ($my_symfony_objects AS $my_symfony_object)
        {
            $doc = self::createIndexDocument($my_symfony_object);
            $index->addDocument($doc);
        }

    }

    public static function updateIndexDocument($id)
    {

        $index = Zend_Search_Lucene::open(self::getIndexLocation());
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(self::getIndexAnalyzer());

        $my_symfony_object = mySymfonyObjectPeer::retrieveByPk($id);

        //first delete existing index entries for this my_symfony_object
        $term =  new Zend_Search_Lucene_Index_Term($my_symfony_object->getId(), 'my_symfony_object_id');
        $query = new Zend_Search_Lucene_Search_Query_Term($term);
        $hits = array();
        $hits  = $index->find($query);

        foreach ($hits AS $hit)
        {
            $index->delete($hit->id);
        }

        //create and add document to index
        $doc = self::createIndexDocument($my_symfony_object);

        $index->addDocument($doc);

    }

    private static function createIndexDocument($my_symfony_object)
    {
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('mysymfonyobject_id', $my_symfony_object->getId()));
        $doc->addField(Zend_Search_Lucene_Field::Unstored('title', strtolower($my_symfony_object->getTitle())));
        $doc->addField(Zend_Search_Lucene_Field::Unstored('author', strtolower($my_symfony_object->getAuthor())));
        $doc->addField(Zend_Search_Lucene_Field::Unstored('description', strtolower($my_symfony_object->getDescription())));
        $doc->addField(Zend_Search_Lucene_Field::Unstored('subjects', strtolower($my_symfony_object->getSubjects())));

        //add unindexed, case-sensitive copies of fields for use in hit display
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('display_title', $my_symfony_object->getTitle(), 'utf-8'));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('display_subjects', $my_symfony_object->getSubjects(), 'utf-8'));

        return $doc;

    }

    public static function deleteIndexDocument($my_symfony_object_id)
    {
        $index = Zend_Search_Lucene::open(self::getIndexLocation());
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(self::getIndexAnalyzer());
        $term =  new Zend_Search_Lucene_Index_Term($my_symfony_object_id, 'mysymfonyobject_id');
        $query = new Zend_Search_Lucene_Search_Query_Term($term);
        $hits = array();
        $hits  = $index->find($query);
        foreach ($hits as $hit)
        {
            $index->delete($hit->id);
        }

    }

}
*/
