<?php

require_once(_KARYBU_PATH_ . 'libs/Zend/Zend_Search_Lucene_includer.php');

/**
 * @class  searchController
 * @author Florin Ercus
 * @brief The controller class for the search module
 **/
class searchController extends search {

    const
        INDEX_PATH_COMMENTS = 'search_comments',
        INDEX_PATH_DOCUMENTS = 'search_documents',
        RESULTS_LIMIT = 0;

    public $documentsIndex, $commentsIndex, $resultSetLimit=0;

    /**
     * @brief Initialization
     **/
    function init() {}


    function triggerInsertDocument(&$obj) {
        $this->addDocumentToIndex($obj);
        return new Object();
    }
    function triggerUpdateDocument(&$obj) {
        $this->deleteDocumentFromIndex($obj);
        $this->addDocumentToIndex($obj);
        return new Object();
    }
    function triggerDeleteDocument(&$obj) {
        $this->deleteDocumentFromIndex($obj);
        return new Object();
    }


    function triggerInsertComment(&$obj) {
        $this->addCommentToIndex($obj);
        return new Object();
    }
    function triggerUpdateComment(&$obj) {
        $this->deleteCommentFromIndex($obj);
        $this->addCommentToIndex($obj);
        return new Object();
    }
    function triggerDeleteComment(&$obj) {
        $this->deleteCommentFromIndex($obj);
    }


    function triggerInsertTrackback(&$obj) {
        return new Object();
    }
    function triggerDeleteTrackback(&$obj) {
        return new Object();
    }


    //UTILS

    /**
     * Returns document tags into an array of strings
     * @param $obj document
     * @return array
     */
    function getDocumentTags($obj) {
        $tagModel = getModel('tag');
        $tagObjects = $tagModel->getDocumentsTagList($obj);
        $tags = array();
        foreach ($tagObjects->data as $t) $tags[] = $t->tag;
        return $tags;
    }

    /**
     * Gets the proper index (singleton)
     * @param string $index
     * @return Zend_Search_Lucene_Proxy
     * @throws Exception
     */
    function createOrRetrieveIndex($index = self::INDEX_PATH_COMMENTS, $refresh=false) {
        if (!in_array($index, array(self::INDEX_PATH_COMMENTS, self::INDEX_PATH_DOCUMENTS))) throw new Exception("Bad index type '$index'.");
        if (!$refresh) {
            if ($index == self::INDEX_PATH_DOCUMENTS && $this->documentsIndex instanceof Zend_Search_Lucene_Proxy) return $this->documentsIndex;
            if ($index == self::INDEX_PATH_COMMENTS  && $this->commentsIndex  instanceof Zend_Search_Lucene_Proxy) return $this->commentsIndex;
        }
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());
        Zend_Search_Lucene_Search_QueryParser::suppressQueryParsingExceptions();
        Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(2);
        Zend_Search_Lucene::setTermsPerQueryLimit(50);
        Zend_Search_Lucene::setResultSetLimit(self::RESULTS_LIMIT);
        $path = _KARYBU_PATH_ . 'files/search/';
        if (!is_dir($path)) mkdir($path);
        if (!is_writable($path)) chmod($path, 0707);
        $path = $path . $index;
        try {
            $myIndex = Zend_Search_Lucene::open( $path );
        }
        catch (Zend_Search_Lucene_Exception $e) {
            $myIndex = Zend_Search_Lucene::create( $path );
        }
        if ($index == self::INDEX_PATH_DOCUMENTS) return $this->documentsIndex = $myIndex;
        if ($index == self::INDEX_PATH_COMMENTS) return $this->commentsIndex = $myIndex;
    }

    function addDocumentToIndex($obj) {
        $index = $this->createOrRetrieveIndex(self::INDEX_PATH_DOCUMENTS);
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::keyword('srl', $obj->document_srl));
        $doc->addField(Zend_Search_Lucene_Field::keyword('update_order', $obj->update_order));
        $doc->addField(Zend_Search_Lucene_Field::text('content', $obj->content));
        $doc->addField(Zend_Search_Lucene_Field::unStored('title', $obj->title));
        $doc->addField(Zend_Search_Lucene_Field::unStored('tags', implode(', ', $this->getDocumentTags($obj))));
        $index->addDocument($doc);
        $index->optimize();
    }

    function addCommentToIndex($obj) {
        $index = $this->createOrRetrieveIndex(self::INDEX_PATH_COMMENTS);
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::keyword('srl', $obj->comment_srl));
        $doc->addField(Zend_Search_Lucene_Field::keyword('update_order', $obj->update_order));
        $doc->addField(Zend_Search_Lucene_Field::text('content', $obj->content));
        $index->addDocument($doc);
        $index->optimize();
    }

    function deleteDocumentFromIndex($obj) {
        $index = $this->createOrRetrieveIndex(self::INDEX_PATH_DOCUMENTS);
        $hits = $index->find('srl: ' . ( is_numeric($obj) ? $obj : $obj->document_srl ));
        foreach ($hits as $hit) $index->delete($hit->id);
        $index->optimize();
    }
    function deleteCommentFromIndex($obj) {
        $index = $this->createOrRetrieveIndex(self::INDEX_PATH_COMMENTS);
        $hits = $index->find('srl: ' . ( is_numeric($obj) ? $obj : $obj->comment_srl ));
        foreach ($hits as $hit) $index->delete($hit->id);
        $index->optimize();
    }

    /**
     * @param $query string
     * @return Zend_Search_Lucene_Search_Query
     */
    function prepareQuery($query) {
        $query = preg_replace('/[\s]?[\*]?([^*]+)[\*]?[\s]?/', '${1}*', $query);
        $luceneQuery = Zend_Search_Lucene_Search_QueryParser::parse($query);
        return $luceneQuery;
    }

    function retrieveDocumentsFromIndex($query, $searchTarget='title', $offset=0, $count=null, $justHits=false) {
        if (!in_array($searchTarget, array('title','content','title_content','tag'))) $searchTarget = 'title';
        $index = $this->createOrRetrieveIndex(self::INDEX_PATH_DOCUMENTS);
        if (in_array($searchTarget, array('title', 'content', 'tag'))) {
            $searchField = ( $searchTarget == 'tag' ? 'tags' : $searchTarget );
            $index->setDefaultSearchField($searchField);
        } // else search all
        $queryObject = $this->prepareQuery($query);
        try { $hits = $index->find($queryObject); }
        catch (Zend_Search_Lucene_Exception $e) { return $e; }
        if ($justHits) return $hits;
        $rez = array();
        $n = ($count ? $offset + $count : count($hits));
        for ($i = $offset; $i < $n; $i++) {
            if (count($hits) < $i) break;
            if (!isset($hits[$i])) continue;
            $rez[$i] = $hits[$i]->getDocument();
        }
        return $rez;
    }

    function retrieveCommentsFromIndex($query, $offset=0, $count=null, $justHits=false) {
        $index = $this->createOrRetrieveIndex(self::INDEX_PATH_COMMENTS);
        $queryObject = $this->prepareQuery($query);
        try { $hits = $index->find($queryObject); }
        catch (Zend_Search_Lucene_Exception $e) { return $e; }
        if ($justHits) return $hits;
        $rez = array();
        $n = ($count ? $offset + $count : count($hits));
        for ($i = $offset; $i < $n; $i++) {
            if (count($hits) < $i) break;
            if (!isset($hits[$i])) continue;
            $rez[$i] = $hits[$i]->getDocument();
        }
        return $rez;
    }

    /**
     * Rebuilds indexes. First selects all ids, then indexes them in chunks of $chunks
     * @param string $what what index to rebuild (comments ,documents or all (both))
     * @param int $frequency how many documents/comments to select in one select
     * @param int $chunks how many documents/comments to select in one select
     * @param int $commentChunks how many comments to select in one select. if ommited, $chunks will be used instead
     */
    function reIndex($what='all', $chunks=200, $commentChunks=400, $timeLimit=600)
    {
        set_time_limit($timeLimit);
        if ($what == 'documents' || $what == 'all') {
            $emptied = $this->emptyIndex(self::INDEX_PATH_DOCUMENTS);
            $output = executeQuery('search.getAllDocumentsSrls');
            $bunchOfSerials = array();
            foreach ($output->data as $i=>$obj) {
                $bunchOfSerials[] = $obj->document_srl;
                if ($i && ( ($i+1) % $chunks == 0 || $i+1 == count($output->data) )) {
                    $args = new stdClass();
                    $args->document_srls = $bunchOfSerials;
                    $out = executeQuery('search.getDocumentsBySrls', $args);
                    foreach ($out->data as $o) $this->addDocumentToIndex($o);
                    $bunchOfSerials = array();
                }
            }
        }
        if ($what == 'comments' || $what == 'all') {
            $commentChunks = ( is_numeric($commentChunks) ? $commentChunks : $chunks);
            $this->emptyIndex(self::INDEX_PATH_COMMENTS);
            $output = executeQuery('search.getAllCommentsSrls');
            $bunchOfSerials = array();
            foreach ($output->data as $i=>$obj) {
                $bunchOfSerials[] = $obj->comment_srl;
                if ($i && ( ($i+1) % $commentChunks == 0 || $i+1 == count($output->data) )) {
                    $args = new stdClass();
                    $args->comment_srls = $bunchOfSerials;
                    $out = executeQuery('search.getCommentsBySrls', $args);
                    foreach ($out->data as $o) $this->addCommentToIndex($o);
                    $bunchOfSerials = array();
                }
            }
        }
    }

    /**
     * Empties an index
     * @param $path - comments index or documents index path
     * @return bool|Zend_Search_Lucene_Proxy returns emptied index or false in case or failure
     * @throws Exception
     */
    function emptyIndex($path) {
        if (!in_array($path, array(self::INDEX_PATH_DOCUMENTS, self::INDEX_PATH_COMMENTS))) {
            throw new Exception("invalid path '$path' for index");
        }
        $absolute = _KARYBU_PATH_ . 'files/search/' . $path;
        return !is_dir($absolute) || $this->rrmdir($absolute) ? $this->createOrRetrieveIndex($path) : false;
    }

    function countDocuments($searchQuery, $searchTarget)
    {
        $entities = $this->retrieveDocumentsFromIndex($searchQuery, $searchTarget, 0, null, true);
        return count($entities);
    }

    function countComments($searchQuery)
    {
        $entities = $this->retrieveCommentsFromIndex($searchQuery, 0, null, true);
        return count($entities);
    }

    function getCommentSerials($searchQuery, $page=1, $itemsPerPage=5)
    {
        $docs = $this->retrieveCommentsFromIndex($searchQuery, ($page-1) * $itemsPerPage, $itemsPerPage);
        if ($docs instanceof Exception) return $docs;
        $srls = array();
        foreach ($docs as $i=>$doc) $srls[$i] = $doc->srl;
        return $srls;
    }

    function getDocumentSerials($searchQuery, $page=1, $itemsPerPage=5)
    {
        $docs = $this->retrieveDocumentsFromIndex($searchQuery, ($page-1) * $itemsPerPage, $itemsPerPage);
        if ($docs instanceof Exception) return $docs;
        $srls = array();
        foreach ($docs as $i=>$doc) $srls[$i] = $doc->srl;
        return $srls;
    }

    /**
     * recursively removes a directory
     * @param $dir
     * @return bool success or not
     */
    function rrmdir($dir) {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) return $this->rrmdir($file);
            else unlink($file);
        }
        return rmdir($dir);
    }

}