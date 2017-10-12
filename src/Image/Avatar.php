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

use Illuminate\Support\Arr;
use Intervention\Image\AbstractFont;
use Intervention\Image\AbstractShape;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic;
use Illuminate\Config\Repository;

class Avatar
{
    protected $name;
    protected $config;
    protected $chars;
    protected $shape;
    protected $width;
    protected $height;
    protected $availableBackgrounds;
    protected $availableForegrounds;
    protected $fonts;
    protected $fontSize;
    protected $borderSize = 0;
    protected $borderColor;
    protected $ascii = false;
    protected $image;
    protected $font = null;
    protected $background = '#cccccc';
    protected $imgBackground = null;
    protected $foreground = '#ffffff';
    protected $initials = '';
    protected $initialGenerator;
    protected $fontFolder;
    protected $defaultFont = 5;
    

    /**
     * Avatar::__construct()
     * 
     * @return
     */
    public function __construct()
    {
        $this->setConfig();
        $this->shape                    = Arr::get($this->config, 'shape', 'circle');
        $this->chars                    = Arr::get($this->config, 'chars', 2);
        $this->availableBackgrounds     = Arr::get($this->config, 'backgrounds', [$this->background]);
        $this->availableForegrounds     = Arr::get($this->config, 'foregrounds', [$this->foreground]);
        $this->fonts                    = Arr::get($this->config, 'fonts', [1]);
        $this->fontSize                 = Arr::get($this->config, 'fontSize', 32);
        $this->width                    = Arr::get($this->config, 'width', 100);
        $this->height                   = Arr::get($this->config, 'height', 100);
        $this->ascii                    = Arr::get($this->config, 'ascii', false);
        $this->borderSize               = Arr::get($this->config, 'border.size');
        $this->borderColor              = Arr::get($this->config, 'border.color');
        $this->initialGenerator         = (new InitialGenerator())->setUppercase(Arr::get($this->config, 'uppercase'));
    }
    /**
     * Avatar::__toString()
     * 
     * @return
     */
    public function __toString()
    {
        return (string)$this->toBase64();
    }
    
    /**
     * Avatar::updateConfig()
     * 
     * @return void
     */
    public function updateConfig($key,$value){
        Arr::set($this->config, $key, $value);
        return $this;
    }
    
    /**
     * Avatar::create()
     * 
     * @param mixed $name
     * @return
     */
    public function create($name)
    {
        $this->name = $name;
        $this->initialGenerator->setName($name);
        $this->initialGenerator->setLength($this->chars);
        $this->initials = $this->initialGenerator->getInitial();
        $this->setForeground($this->getRandomForeground());
        $this->setBackground($this->getRandomBackground());
        return $this;
    }
    
    /**
     * Avatar::setConfig()
     * 
     * @param mixed $config
     * @return
     */
    public function setConfig($config=null){
        if(is_null($config))
            $config     = require dirname(__dir__ ) . DIRECTORY_SEPARATOR .'config' . DIRECTORY_SEPARATOR . 'config.php';
        $this->config   = new Repository($config);
        return $this;
    }
    
    /**
     * Avatar::setFontFolder()
     * 
     * @param mixed $folders
     * @return
     */
    public function setFontFolder($folders)
    {
        $this->fontFolder = $folders;
    }
    
    /**
     * Avatar::setFont()
     * 
     * @param mixed $font
     * @return
     */
    public function setFont($font)
    {
        if (is_file($font))
            $this->font = $font;
        return $this;
    }
    
    /**
     * Avatar::setLength()
     * 
     * @param mixed $length
     * @return
     */
    public function setLength($length)
    {
        $this->chars = (int)$length;
        return $this;
    }
    
    /**
     * Avatar::getImage()
     * 
     * @return
     */
    public function getImage()
    {
        $this->buildAvatar();
        return $this->image;
    }
    
    /**
     * Avatar::toBase64()
     * 
     * @return
     */
    public function toBase64()
    {
        $this->buildAvatar();
        return $this->image->encode('data-url');
    }
    
    /**
     * Avatar::save()
     * 
     * @param mixed $path
     * @param integer $quality
     * @return
     */
    public function save($path, $quality = 90)
    {
        $this->buildAvatar();
        return $this->image->save($path, $quality);
    }
    
    /**
     * Avatar::setBackground()
     * 
     * @param mixed $hex
     * @return
     */
    public function setBackground($hex)
    {
        $this->background = $hex;
        return $this;
    }
    
    /**
     * Avatar::setForeground()
     * 
     * @param mixed $hex
     * @return
     */
    public function setForeground($hex)
    {
        $this->foreground = $hex;
        return $this;
    }
    
    /**
     * Avatar::setDimension()
     * 
     * @param mixed $width
     * @param mixed $height
     * @return
     */
    public function setDimension($width, $height = null)
    {
        if (!$height)
            $height = $width;
        $this->width = $width;
        $this->height = $height;
        return $this;
    }
    
    /**
     * Avatar::setFontSize()
     * 
     * @param mixed $size
     * @return
     */
    public function setFontSize($size)
    {
        $this->fontSize = $size;
        return $this;
    }
    
    /**
     * Avatar::setBorder()
     * 
     * @param mixed $size
     * @param mixed $color
     * @return
     */
    public function setBorder($size, $color)
    {
        $this->borderSize = $size;
        $this->borderColor = $color;
        return $this;
    }
    
    /**
     * Avatar::setShape()
     * 
     * @param mixed $shape
     * @return
     */
    public function setShape($shape)
    {
        $this->shape = $shape;
        return $this;
    }
    
    /**
     * Avatar::getInitial()
     * 
     * @return
     */
    public function getInitial()
    {
        return $this->initials;
    }
    
    /**
     * Avatar::getRandomBackground()
     * 
     * @return
     */
    protected function getRandomBackground()
    {
        return $this->getRandomElement($this->availableBackgrounds, $this->background);
    }
    
    /**
     * Avatar::getRandomForeground()
     * 
     * @return
     */
    protected function getRandomForeground()
    {
        return $this->getRandomElement($this->availableForegrounds, $this->foreground);
    }
    
    /**
     * Avatar::setRandomFont()
     * 
     * @return
     */
    protected function setRandomFont()
    {
        $this->font = $this->defaultFont;
        $initials = $this->getInitial();
        if ($initials)
        {
            $number = ord($initials[0]);
            $font = $this->fonts[$number % count($this->fonts)];
            if (!is_array($this->fontFolder))
                throw new \Exception('Font folder not set');
            foreach ($this->fontFolder as $folder)
            {
                $fontFile = $folder . $font;
                if (is_file($fontFile))
                {
                    $this->font = $fontFile;
                    break;
                }
            }
        }
    }
    
    /**
     * Avatar::getBorderColor()
     * 
     * @return
     */
    protected function getBorderColor()
    {
        if ($this->borderColor == 'foreground')
            return $this->foreground;
        if ($this->borderColor == 'background')
            return $this->background;
        return $this->borderColor;
    }
    
    /**
     * Avatar::buildAvatar()
     * 
     * @return
     */
    protected function buildAvatar()
    {
        $x = $this->width / 2;
        $y = $this->height / 2;
        $manager = new ImageManager(array('driver' => Arr::get($this->config, 'driver')));
        $this->image = $manager->canvas($this->width, $this->height);
        $this->createShape();
        if($this->name){
            $this->chooseFont();
            $this->image->text($this->initials, $x, $y, function (AbstractFont $font)
            {
                $font->file($this->font); $font->size($this->fontSize); $font->color($this->foreground); $font->align('center'); $font->valign('middle'); }
            );
        }
    }
    
    /**
     * Avatar::createShape()
     * 
     * @return
     */
    protected function createShape()
    {
        $method = 'create' . ucfirst($this->shape) . 'Shape';
        if (method_exists($this, $method))
            return $this->$method();
        throw new \InvalidArgumentException("Shape [$this->shape] currently not supported.");
    }
    
    /**
     * Avatar::createCircleShape()
     * 
     * @return
     */
    protected function createCircleShape()
    {
        $circleDiameter = $this->width - $this->borderSize;
        $x = $this->width / 2;
        $y = $this->height / 2;
        if ($this->imgBackground)
            $this->image->fill($this->imgBackground);
        $this->image->circle($circleDiameter, $x, $y, function (AbstractShape $draw)
        {
            if (!$this->imgBackground){$draw->background($this->background); $draw->border($this->borderSize, $this->getBorderColor());}
        }
        );
    }
    
    /**
     * Avatar::createSquareShape()
     * 
     * @return
     */
    protected function createSquareShape()
    {
        $x = $y = $this->borderSize;
        $width = $this->width - ($this->borderSize * 2);
        $height = $this->height - ($this->borderSize * 2);
        if ($this->imgBackground)
            $this->image->fill($this->imgBackground);
        $this->image->rectangle($x, $y, $width, $height, function (AbstractShape $draw)
        {
            if (!$this->imgBackground){$draw->background($this->background); $draw->border($this->borderSize, $this->getBorderColor());}
        }
        );
    }
    
    /**
     * Avatar::getRandomElement()
     * 
     * @param mixed $array
     * @param mixed $default
     * @return
     */
    protected function getRandomElement($array, $default)
    {
        if (strlen($this->name) == 0)
            return $default;
        $number = ord($this->name[0]);
        $i = 1;
        $charLength = strlen($this->name);
        while ($i < $charLength)
        {
            $number += ord($this->name[$i]);
            $i++;
        }
        return $array[$number % count($array)];
    }
    
    /**
     * Avatar::chooseFont()
     * 
     * @return
     */
    protected function chooseFont()
    {
        if (!$this->font)
            $this->setRandomFont();
    }
}