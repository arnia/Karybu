<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Term.php 23775 2011-03-01 17:25:24Z ralph $
 */


/** Zend_Search_Lucene_Search_Query_Processing */
require_once XE_LUCENE_PATH . '/Search/Query/Preprocessing.php';


/**
 * It's an internal abstract class intended to finalize ase a query processing after query parsing.
 * This type of query is not actually involved into query execution.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @internal
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_Query_Preprocessing_Term extends Zend_Search_Lucene_Search_Query_Preprocessing
{
    /**
     * word (query parser lexeme) to find.
     *
     * @var string
     */
    private $_word;

    /**
     * Word encoding (field name is always provided using UTF-8 encoding since it may be retrieved from index).
     *
     * @var string
     */
    private $_encoding;


    /**
     * Field name.
     *
     * @var string
     */
    private $_field;

    /**
     * Class constructor.  Create a new preprocessing object for prase query.
     *
     * @param string $word       Non-tokenized word (query parser lexeme) to search.
     * @param string $encoding   Word encoding.
     * @param string $fieldName  Field name.
     */
    public function __construct($word, $encoding, $fieldName)
    {
        $this->_word     = $word;
        $this->_encoding = $encoding;
        $this->_field    = $fieldName;
    }

    /**
     * Re-write query into primitive queries in the context of specified index
     *
     * @param Zend_Search_Lucene_Interface $index
     * @return Zend_Search_Lucene_Search_Query
     */
    public function rewrite(Zend_Search_Lucene_Interface $index)
    {
        if ($this->_field === null) {
            require_once XE_LUCENE_PATH . '/Search/Query/MultiTerm.php';
            $query = new Zend_Search_Lucene_Search_Query_Boolean();

            $hasInsignificantSubqueries = false;

            require_once XE_LUCENE_PATH . '.php';
            if (Zend_Search_Lucene::getDefaultSearchField() === null) {
                $searchFields = $index->getFieldNames(true);
            } else {
                $searchFields = array(Zend_Search_Lucene::getDefaultSearchField());
            }

            require_once XE_LUCENE_PATH . '/Search/Query/Preprocessing/Term.php';
            foreach ($searchFields as $fieldName) {
                $subquery = new Zend_Search_Lucene_Search_Query_Preprocessing_Term($this->_word,
                                                                                   $this->_encoding,
                                                                                   $fieldName);
                $rewrittenSubquery = $subquery->rewrite($index);
                if ( !($rewrittenSubquery instanceof Zend_Search_Lucene_Search_Query_Insignificant  ||
                    $rewrittenSubquery instanceof Zend_Search_Lucene_Search_Query_Empty) ) {
                    $query->addSubquery($rewrittenSubquery);
                }

                if ($rewrittenSubquery instanceof Zend_Search_Lucene_Search_Query_Insignificant) {
                    $hasInsignificantSubqueries = true;
                }
            }

            $subqueries = $query->getSubqueries();

            if (count($subqueries) == 0) {
                $this->_matches = array();
                if ($hasInsignificantSubqueries) {
                    require_once XE_LUCENE_PATH . '/Search/Query/Insignificant.php';
                    return new Zend_Search_Lucene_Search_Query_Insignificant();
                } else {
                    require_once XE_LUCENE_PATH . '/Search/Query/Empty.php';
                    return new Zend_Search_Lucene_Search_Query_Empty();
                }
            }

            if (count($subqueries) == 1) {
                $query = reset($subqueries);
            }

            $query->setBoost($this->getBoost());

            $this->_matches = $query->getQueryTerms();
            return $query;
        }

        // -------------------------------------
        // Recognize exact term matching (it corresponds to Keyword fields stored in the index)
        // encoding is not used since we expect binary matching
        require_once XE_LUCENE_PATH . '/Index/Term.php';
        $term = new Zend_Search_Lucene_Index_Term($this->_word, $this->_field);
        if ($index->hasTerm($term)) {
            require_once XE_LUCENE_PATH . '/Search/Query/Term.php';
            $query = new Zend_Search_Lucene_Search_Query_Term($term);
            $query->setBoost($this->getBoost());

            $this->_matches = $query->getQueryTerms();
            return $query;
        }


        // -------------------------------------
        // Recognize wildcard queries

        /** @todo check for PCRE unicode support may be performed through Zend_Environment in some future */
        if (@preg_match('/\pL/u', 'a') == 1) {
            $word = iconv($this->_encoding, 'UTF-8', $this->_word);
            $wildcardsPattern = '/[*?]/u';
            $subPatternsEncoding = 'UTF-8';
            $utf8Support = 1;
        } else {
            $word = $this->_word;
            $wildcardsPattern = '/[*?]/';
            $subPatternsEncoding = $this->_encoding;
            $utf8Support = 0;
        }

        $subPatterns = preg_split($wildcardsPattern, $word, -1, PREG_SPLIT_OFFSET_CAPTURE);

        if (count($subPatterns) > 1) {
            // Wildcard query is recognized

            $patterns   = array('');
            $subqueries = array();
            $position   = 0;

            require_once XE_LUCENE_PATH . '/Analysis/Analyzer.php';
            foreach ($subPatterns as $id => $subPattern) {
                // Append corresponding wildcard character to the pattern before each sub-pattern (except first)
                if ($id != 0) {
                    foreach ($patterns as $key => $pattern) {
                        $patterns[$key] .= $word[ $subPattern[1] - 1 ];
                    }
                }

                // Check if each subputtern is a single word in terms of current analyzer
                $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($subPattern[0], $subPatternsEncoding);

                // Find out if start of subpattern is also a forced token boundary
                // (i.e. non-indexed character) in terms of current analyzer
                /** @todo check for PCRE unicode support may be performed through Zend_Environment in some future */
                $references=array();
                if ($utf8Support) {
                    preg_match('/^./u',$subPattern[0],$references);
                } else {
                    preg_match('/^./',$subPattern[0],$references);
                }
                if (!count($references)) {
                    $references = array('');
                }
                // $references[0] is supposed to hold the first (possibly multi byte) character of $subPattern[0]
                // if it is tokenized empty, (but the current subpattern is not empty) then the
                // current subpattern begins with a word boundary character in terms of current analyzer
                // in this case, the pattern needs to be split at this point. Example: foo*_bar
                $tokentest = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($references[0], $subPatternsEncoding);
                if ($subPattern[0] != '' && count($tokentest) == 0) {
                    // There might be several alternative patterns for the token string so far.
                    // Save them all as queries in array
                    foreach ($patterns as $pattern) {
                        $term  = new Zend_Search_Lucene_Index_Term($pattern, $this->_field);
                        $query = new Zend_Search_Lucene_Search_Query_Wildcard($term);
                        $query->setBoost($this->getBoost());
                        $subqueries[] = array($query, $position);
                    }
                    $patterns = array('');
                    $position++;
                }

                // Add to array of search-tokens for each token in analyzed string
                foreach ($tokens as $num => $token) {
                    if ($num == 0) {
                        // The very first token, only adds to pattern string(s) as normal
                        $oldpatterns = $patterns;
                        foreach ($patterns as $key => $pattern) {
                            $patterns[$key] .= $token->getTermText();
                        }
                    } else {
                        // On additional tokens check whether they are marked as alternatives (positionIcrement==0)
                        if ($token->getPositionIncrement() == 0) {
                            // If so, create alternative patterns for this alternative
                            foreach($oldpatterns as $oldpattern) {
                                $patterns[] = $oldpattern.$token->getTermText();
                            }
                        } else {
                            // Otherwise store all previous patterns as wildcard queries in array
                            foreach ($patterns as $pattern) {
                                $term  = new Zend_Search_Lucene_Index_Term($pattern, $this->_field);
                                $query = new Zend_Search_Lucene_Search_Query_Wildcard($term);
                                $query->setBoost($this->getBoost());
                                $subqueries[] = array($query, $position);
                            }
                            // Increase the position in the phrase query
                            $position += $token->getPositionIncrement();
                            // And store this very current token as only alternative in pattern array
                            $oldpatterns = array('');
                            $patterns = array($token->getTermText());
                        }
                    }
                }
            }


            // If we ended up with more than one (sub)query, we need to assemble a phrase request.
            if (count($subqueries) || count($patterns)>1) {
                // The latest wildcard patterns have not been added to the array yet
                foreach ($patterns as $pattern) {
                    $term  = new Zend_Search_Lucene_Index_Term($pattern, $this->_field);
                    $query = new Zend_Search_Lucene_Search_Query_Wildcard($term);
                    $query->setBoost($this->getBoost());
                    $subqueries[] = array($query, $position);
                }

            require_once XE_LUCENE_PATH . '/Index/Term.php';
            require_once XE_LUCENE_PATH . '/Search/Query/Wildcard.php';
            $query->setBoost($this->getBoost());

                // Create a new Phrase query
                $query = new Zend_Search_Lucene_Search_Query_Phrase();
                $query->setBoost($this->getBoost());
                $maxPosition = 0;
                $accountedPositions = array();

                // Add all found terms for each wildcard into the corresponding positions of the phrase query
                foreach ($subqueries as &$subarray) {
                    list($subquery, $position)=$subarray;
                    $terms = $subquery->rewrite($index)->getQueryTerms();
                    foreach($terms as $term) {
                        $query->addTerm($term, $position);
                        $accountedPositions[$position] = 1;
                    }
                    // account for all needed positions
                    if ($position > $maxPosition) $maxPosition = $position;
                }

                // If no tokens were found for a required pattern,the entire query is insignificant.
                if (count($accountedPositions) <= $maxPosition) {
                    $this->_matches = array();
                    return new Zend_Search_Lucene_Search_Query_Insignificant();
                }
            } else {
                // Single wildcard query. Behave as normal.
                $term  = new Zend_Search_Lucene_Index_Term($patterns[0], $this->_field);
                $query = new Zend_Search_Lucene_Search_Query_Wildcard($term);
                $query->setBoost($this->getBoost());
            }

            // Get rewritten query. Important! It also fills terms matching container.
            $rewrittenQuery = $query->rewrite($index);
            $this->_matches = $query->getQueryTerms();

            return $rewrittenQuery;
        }


        // -------------------------------------
        // Recognize one-term multi-term and "insignificant" queries
        require_once XE_LUCENE_PATH . '/Analysis/Analyzer.php';
        $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($this->_word, $this->_encoding);

        if (count($tokens) == 0) {
            $this->_matches = array();
            require_once XE_LUCENE_PATH . '/Search/Query/Insignificant.php';
            return new Zend_Search_Lucene_Search_Query_Insignificant();
        }

        if (count($tokens) == 1) {
            require_once XE_LUCENE_PATH . '/Index/Term.php';
            $term  = new Zend_Search_Lucene_Index_Term($tokens[0]->getTermText(), $this->_field);
            require_once XE_LUCENE_PATH . '/Search/Query/Term.php';
            $query = new Zend_Search_Lucene_Search_Query_Term($term);
            $query->setBoost($this->getBoost());

            $this->_matches = $query->getQueryTerms();
            return $query;
        }

        //It's not insignificant or one term query
        require_once XE_LUCENE_PATH . '/Search/Query/MultiTerm.php';
        $query = new Zend_Search_Lucene_Search_Query_Phrase();
        $query->setBoost($this->getBoost());
        $position = 0;

        require_once XE_LUCENE_PATH . '/Index/Term.php';
        foreach ($tokens as $token) {
            // Find matching terms to individual token fuzzy searches
            $term  = new Zend_Search_Lucene_Index_Term($token->getTermText(), $this->_field);
            $position += $token->getPositionIncrement();
            $query->addTerm($term, $position);
        }
        // Get rewritten query. Important! It also fills terms matching container.
        $rewrittenQuery = $query->rewrite($index);

        $this->_matches = $query->getQueryTerms();
        return $rewrittenQuery;
    }

    /**
     * Query specific matches highlighting
     *
     * @param Zend_Search_Lucene_Search_Highlighter_Interface $highlighter  Highlighter object (also contains doc for highlighting)
     */
    protected function _highlightMatches(Zend_Search_Lucene_Search_Highlighter_Interface $highlighter)
    {
        /** Skip fields detection. We don't need it, since we expect all fields presented in the HTML body and don't differentiate them */

        /** Skip exact term matching recognition, keyword fields highlighting is not supported */

        // -------------------------------------
        // Recognize wildcard queries
        /** @todo check for PCRE unicode support may be performed through Zend_Environment in some future */
        if (@preg_match('/\pL/u', 'a') == 1) {
            $word = iconv($this->_encoding, 'UTF-8', $this->_word);
            $wildcardsPattern = '/[*?]/u';
            $subPatternsEncoding = 'UTF-8';
        } else {
            $word = $this->_word;
            $wildcardsPattern = '/[*?]/';
            $subPatternsEncoding = $this->_encoding;
        }
        $subPatterns = preg_split($wildcardsPattern, $word, -1, PREG_SPLIT_OFFSET_CAPTURE);
        if (count($subPatterns) > 1) {
            // Wildcard query is recognized

            $pattern = '';

            require_once XE_LUCENE_PATH . '/Analysis/Analyzer.php';
            foreach ($subPatterns as $id => $subPattern) {
                // Append corresponding wildcard character to the pattern before each sub-pattern (except first)
                if ($id != 0) {
                    $pattern .= $word[ $subPattern[1] - 1 ];
                }

                // Check if each subputtern is a single word in terms of current analyzer
                $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($subPattern[0], $subPatternsEncoding);
                if (count($tokens) > 1) {
                    // Do nothing (nothing is highlighted)
                    return;
                }
                foreach ($tokens as $token) {
                    $pattern .= $token->getTermText();
                }
            }

            require_once XE_LUCENE_PATH . '/Index/Term.php';
            $term  = new Zend_Search_Lucene_Index_Term($pattern, $this->_field);
            require_once XE_LUCENE_PATH . '/Search/Query/Wildcard.php';
            $query = new Zend_Search_Lucene_Search_Query_Wildcard($term);

            $query->_highlightMatches($highlighter);
            return;
        }


        // -------------------------------------
        // Recognize one-term multi-term and "insignificant" queries
        require_once XE_LUCENE_PATH . '/Analysis/Analyzer.php';
        $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($this->_word, $this->_encoding);

        if (count($tokens) == 0) {
            // Do nothing
            return;
        }

        if (count($tokens) == 1) {
            $highlighter->highlight($tokens[0]->getTermText());
            return;
        }

        //It's not insignificant or one term query
        $words = array();
        foreach ($tokens as $token) {
            $words[] = $token->getTermText();
        }
        $highlighter->highlight($words);
    }

    /**
     * Print a query
     *
     * @return string
     */
    public function __toString()
    {
        // It's used only for query visualisation, so we don't care about characters escaping
        if ($this->_field !== null) {
            $query = $this->_field . ':';
        } else {
            $query = '';
        }

        $query .= $this->_word;

        if ($this->getBoost() != 1) {
            $query .= '^' . round($this->getBoost(), 4);
        }

        return $query;
    }
}
