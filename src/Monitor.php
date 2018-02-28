<?php

namespace SapiStudio\FileSystem;

use Symfony\Component\Finder\Finder;

class Monitor
{
    private $monitorLocation    = null;
    private $command            = null;
    protected $StorageLocation  = 'mScan.dat';
    protected $HashFileLimit    = 2048000;
    protected $reportStatus     = [];
    protected $IsExecDisabled;
    protected $existingFiles;
    protected $knownFiles;


    /**
     * Monitor::__construct()
     * 
     * @return
     */
    public function __construct(array $location)
    {
        $this->command = $command;
        $this->monitorLocation = $location;
        $this->timestamp = time();
        $this->IsExecDisabled = in_array('exec', explode(',', ini_get('disable_functions')));
    }

    /**
     * Monitor::watch()
     * 
     * @return
     */
    public function watch()
    {
        $this->getFiles();
        $this->GetKnownFiles();
        $this->Compare();
        $this->StoreKnownFiles();
    }
    
    /**
     * Monitor::getFiles()
     * 
     * @return
     */
    private function getFiles()
    {
        $loadLocationFiles = (new Finder)->ignoreUnreadableDirs()->files()->in($this->
            monitorLocation);
        if (!$loadLocationFiles)
            return false;
        foreach ($loadLocationFiles as $file)
            $this->existingFiles[$file->getPathname()] = [$this->GetHash($file->getPathname()), filemtime($file->getPathname())];
    }

    /**
     * Monitor::StoreKnownFiles()
     * 
     * @return
     */
    protected function StoreKnownFiles()
    {
        $Pack = [];
        if (!$this->existingFiles)
            return false;
        foreach ($this->existingFiles as $fileName => $fileDetails)
            $Pack[] = $fileDetails[0] . ',' . $fileDetails[1] . ',' . $fileName;
        file_put_contents($this->StorageLocation, gzdeflate(implode("\n", $Pack)));
    }

    /**
     * Monitor::GetKnownFiles()
     * 
     * @return
     */
    protected function GetKnownFiles()
    {
        $currentFiles = (is_file($this->StorageLocation)) ? explode("\n", gzinflate(@file_get_contents($this->StorageLocation))) : [];
        if (empty($currentFiles))
            $this->knownFiles = [];
        else
        {
            foreach ($currentFiles as $Line)
            {
                $Parts = explode(',', trim($Line), 3);
                if (count($Parts) == 3)
                    $this->knownFiles[$Parts[2]] = [$Parts[0], intval($Parts[1])];
            }
        }
    }

    /**
     * Monitor::GetHash()
     * 
     * @return
     */
    protected function GetHash($Path = null)
    {
        if (!is_file($Path))
            return false;
        if (filesize($Path) < $this->HashFileLimit)
            return hash_file('md5', $Path);
        if (!$this->IsExecDisabled)
        {
            exec("md5sum $Path", $Results);
            $Hash = explode(' ', $Results[0]);
            return $Hash[0];
        }
        $f = fopen($Path, 'rb');
        $md5 = '';
        while (!feof($f))
        {
            $Chunk = fread($f, 1 * 1024 * 1024);
            $md5 .= md5($Chunk, true);
        }
        fclose($f);
        return md5($md5);
    }

    /**
     * Monitor::Compare()
     * 
     * @return
     */
    protected function Compare()
    {
        $knownFilesKeys = array_keys($this->knownFiles);
        $existingFilesKeys = array_keys($this->existingFiles);
        foreach (array_diff($knownFilesKeys, $existingFilesKeys) as $File)
        {
            $this->reportStatus['deleted'][] = $File;
        }
        foreach (array_diff($existingFilesKeys, $knownFilesKeys) as $File)
        {
            $this->reportStatus['added'][] = ['name' => $File, 'time' => date('Y-m-d H:i:s',$this->existingFiles[$File][1])];
        }
        foreach (array_intersect($existingFilesKeys, $knownFilesKeys) as $File)
        {
            if ($this->existingFiles[$File] === $this->knownFiles[$File])
                continue;
            $this->reportStatus['modified'] = ['name' => $File, 'time' => date('Y-m-d H:i:s',$this->existingFiles[$File][1])];
        }
    }
}
