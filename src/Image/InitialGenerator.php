<?php
/**
 * original class : laravolt/avatar
 *
 * "authors": [
        {
            "name": "Bayu Hendra Winata",
            "email": "uyab.exe@gmail.com",
            "homepage": "http://id-laravel.com",
            "role": "Developer"
        }
    ]
 *
 * MIT LICENSE
 *
 */

namespace SapiStudio\FileSystem\Image;

use Illuminate\Support\Collection;
use Stringy\Stringy;

class InitialGenerator
{
    protected $name         = '';
    protected $length       = 2;
    protected $ascii        = false;
    protected $uppercase    = false;

    /**
     * InitialGenerator::__construct()
     * 
     * @return
     */
    public function __construct($name = '', $length = 2)
    {
        $this->setName($name);
        $this->length = $length;
    }

    /**
     * InitialGenerator::setName()
     * 
     * @return
     */
    public function setName($name)
    {
        if (is_array($name)) {
            throw new \InvalidArgumentException('Passed value cannot be an array');
        }elseif (is_object($name) && !method_exists($name, '__toString')) {
            throw new \InvalidArgumentException( 'Passed object must have a __toString method');
        }
        $name = Stringy::create($name)->collapseWhitespace();
        if (filter_var($name, FILTER_VALIDATE_EMAIL)) {
            $name = current($name->split('@', 1))->replace('.', ' ');
        }
        if ($this->ascii) {
            $name = $name->toAscii();
        }
        $this->name = $name;
        return $this;
    }

    /**
     * InitialGenerator::setLength()
     * 
     * @return
     */
    public function setLength($length = 2)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * InitialGenerator::setAscii()
     * 
     * @return
     */
    public function setAscii($ascii)
    {
        $this->ascii = $ascii;
        if ($this->ascii)
            $this->name = $this->name->toAscii();
        return $this;
    }

    /**
     * InitialGenerator::setUppercase()
     * 
     * @return
     */
    public function setUppercase($uppercase)
    {
        $this->uppercase = $uppercase;
        return $this;
    }

    /**
     * InitialGenerator::getInitial()
     * 
     * @return
     */
    public function getInitial()
    {
        $words = new Collection(explode(' ', $this->name));
        if ($words->count() === 1) {
            $initial = (string)$words->first();
            if ($this->name->length() >= $this->length) {
                $initial = (string)$this->name->substr(0, $this->length);
            }
        } else {
            $initials = new Collection();
            $words->each(function ($word) use ($initials) {
                $initials->push(Stringy::create($word)->substr(0, 1));
            });
            $initial = $initials->slice(0, $this->length)->implode('');
        }
        if ($this->uppercase) {
            $initial = strtoupper($initial);
        }
        return $initial;
    }
}