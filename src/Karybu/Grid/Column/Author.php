<?php
namespace Karybu\Grid\Column;
use Karybu\Grid;
use Karybu\Grid\Column;

class Author extends Text
{
    public function render($row)
    {
        $prefix = $suffix = '';
        if ($authorKey = $this->getConfig('author')){
            if (!empty($row->$authorKey)){
                $authors = $row->$authorKey;
            }
            else{
                $authors = array();
            }
            foreach ($authors as $author){
                if (!empty($author->homepage) && !empty($author->name)){
                    $prefix = $prefix.'<a href="'.$author->homepage.'" target="_blank">'.$author->name.'<a>';
                }
                elseif(!empty($author->name)){
                    $prefix = '';
                }
            }
            $suffix = '';
        }
        return $prefix.parent::render($row).$suffix;
    }
}