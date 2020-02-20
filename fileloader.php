<?php

require_once(__DIR__ . '/strings.php');
/*
 *\
 */
class FileLoader{

    //
    private $resLink;
    private $loadPath;
    private $data;

    private $status = FileLoaderStrings::FAIL;
    private $lastError;//other

    private $timeAnswere = 300;//in seconds
    private $timeStart;

    public static function loadBaseFiles(){

        $links = FileLoaderStrings::LOAD_LINKS();

        foreach($links as $filename => $source){

            $i = new FileLoader($source, FileLoaderStrings::BASE_LOAD_PATH . '/' . $filename);
            $i->loadingData();
        }
    }

    public function __construct($resource, $save_to){
        
        $this->setResourceLink($resource);
        $this->setLoadPath($save_to);
        
        if(!$this->errorStatusIsActive())
            $this->status = FileLoaderStrings::INITIAL;
    }

    protected function setResourceLink($res_link){

        try{
            if($this->errorStatusIsActive()) return;

            if(!filter_var($res_link, FILTER_VALIDATE_URL))
                throw new Exception('Invalid url address');

            $this->resLink = $res_link;
        }
        catch(Exception $e){

            $this->lastError = $e;
        }
    }

    protected function setLoadPath($load_path){

        try{
            if($this->errorStatusIsActive()) return;
            
            if( !is_dir(pathinfo($load_path, PATHINFO_DIRNAME)) )
                throw new Exception('Invalid directory path address');

            $this->loadPath = $load_path;
        }
        catch(Exception $e){

            $this->lastError = $e;
        }
    }

    //other
    public function errorStatusIsActive(){

        if(!isset($this->lastError))
            return false;

        return true;
    }

    //other
    public function getLastErrorMessage(){

        if(!$this->errorStatusIsActive())
            return;  

        return $this->lastError->getMessage();
    }

    private function timeInterval(){

        if(!isset($this->timeStart)) return;

        return (new DateTime())->getTimestamp() - $this->timeStart->getTimestamp();
    }

    //
    public function loadingData(){

        try{
            if($this->errorStatusIsActive() || $this->status!==FileLoaderStrings::INITIAL) 
                return;
            
            $this->timeStart = new DateTime();

            //get data
            $this->status = $this->readData() ? FileLoaderStrings::READ : FileLoaderStrings::FAIL;

            //write data
            if($this->status === FileLoaderStrings::READ)
                $this->status = $this->writeData() ? FileLoaderStrings::SUCCESS : FileLoaderStrings::FAIL;
        }
        catch(Exception $e){

            $this->lastError = $e;
        }
    }

    protected function writeData(){

        try{

            if($this->errorStatusIsActive()) return;

            $resp;
            while($this->timeInterval() < $this->timeAnswere){

                $resp = file_put_contents($this->loadPath, $this->data);

                if((bool)$resp)
                    break;
            }

            return $resp;
        }
        catch(Exception $e){

            $this->lastError = $e;
        }
    }

    protected function readData(){

        try{

            if($this->errorStatusIsActive()) return;

            while($this->timeInterval() < $this->timeAnswere){
                
                $this->data = file_get_contents($this->resLink);
                if((bool)$this->data) break; 
            }

            return (bool)$this->data;
        }
        catch(Exception $e){

            $this->lastError = $e;
        }
    }
}

?>