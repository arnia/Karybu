<?php
/**
 * File containing the IThumbnailable interface
 */
/**
 * Interface for objects that can generate a thumbnail
 *
 * @author Corina Udrescu (corina.udrescu@gmail.com)
 */
interface IThumbnailable
{
    /**
     * Path to generated thumbnail
     *
     * @param int $width
     * @param int $height
     * @param string $thumbnail_type
     * @return mixed
     */
    function getThumbnailPath($width = 80, $height = 0, $thumbnail_type = '');
}