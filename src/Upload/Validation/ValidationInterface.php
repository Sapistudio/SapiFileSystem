<?php
/**
 * original class : codeguy/upload
 *
 * @author      Josh Lockhart <info@joshlockhart.com>
 * @copyright   2012 Josh Lockhart
 * @link        http://www.joshlockhart.com
 * @version     2.0.0
 *
 * MIT LICENSE

 */
namespace SapiStudio\FileSystem\Upload\Validation;

interface ValidationInterface
{
    public function validate(\SapiStudio\FileSystem\FileInfo $fileInfo);
}