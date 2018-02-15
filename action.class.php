<?php

/**
 * Handle yml files easily. 
 */
class PluginWfYml{
  public $file = null;
  public $yml = null;
  public $file_exists = false;
  public $root_path_to_key = null; // When using get/set this is included as key.
  
  /**
   * 
   * @param type $file
   * @param type $root_path_to_key
   */
  function __construct($file, $root_path_to_key = null) {
    $this->file = wfSettings::replaceTheme($file);
    $this->file = wfSettings::addRoot($this->file);
    
    if($root_path_to_key){
      $this->root_path_to_key = $root_path_to_key;
    }
    if(!file_exists($this->file)){
      $this->yml = array();  
    }else{
      $this->file_exists = true;
      $this->yml = wfFilesystem::loadYml($this->file);
    }
  }
  
  public function getFilename(){
    return $this->file;
  }
  
  /**
   * 
   * @param type $path_to_key
   * @return type
   */
  public function get($path_to_key = null){
    if($path_to_key){
      if(!$this->root_path_to_key){
        return wfArray::get($this->yml, $path_to_key);
      }else{
        return wfArray::get($this->yml, $this->root_path_to_key.'/'.$path_to_key);
      }
    }else{
      if(!$this->root_path_to_key){
        return $this->yml;
      }else{
        return wfArray::get($this->yml, $this->root_path_to_key);
      }
    }
  }
  /**
   * 
   * @param type $value
   * @param type $path_to_key
   */
  //public function set($value, $path_to_key = null){
  public function set($path_to_key, $value){
    if($path_to_key){
      if(!$this->root_path_to_key){
        $this->yml = wfArray::set($this->yml, $path_to_key, $value);
      }else{
        $this->yml = wfArray::set($this->yml, $this->root_path_to_key.'/'.$path_to_key, $value);
      }
    }else{
      if(!$this->root_path_to_key){
        $this->yml = $value;
      }else{
        $this->yml = wfArray::set($this->yml, $this->root_path_to_key, $value);          
      }
    }
  }
  
  
  /**
   * This function is for set element who has id attribute without knowing the full path.
   * @param type $id
   * @param type $key
   * @param type $value
   */
  public function setById($id, $key = null, $value){
    wfPlugin::includeonce('wf/arraysearch');
    $wf_arraysearch = new PluginWfArraysearch();
    $wf_arraysearch->data = array('key_name' => 'id', 'key_value' => $id, 'data' => $this->yml);
    $data = $wf_arraysearch->get();
    if(sizeof($data)>0){
      $path_to_key = $data[0];
      $path_to_key = substr($path_to_key, 1);
      $path_to_key = str_replace('/attribute/id', '', $path_to_key);
      if($key){
        $this->set($path_to_key.'/'.$key, $value);
      }else{
        $this->set($path_to_key, $value);
      }
    }else{
      echo 'Could not find element with id '.$id.'.<br>';
    }
  }
  /**
   * Get element by id. 
   * Hole element or specific value if key is set.
   * @param string $id
   * @param string $key
   */
  public function getById($id, $key = null){
    wfPlugin::includeonce('wf/arraysearch');
    wfPlugin::includeonce('wf/array');
    $wf_arraysearch = new PluginWfArraysearch();
    $wf_arraysearch->data = array('key_name' => 'id', 'key_value' => $id, 'data' => $this->yml);
    $data = $wf_arraysearch->get();
    if(sizeof($data)>0){
      $path_to_key = $data[0];
      $path_to_key = substr($path_to_key, 1);
      $path_to_key = str_replace('/attribute/id', '', $path_to_key);
      if($key){
        return new PluginWfArray($this->get($path_to_key.'/'.$key));
      }else{
        return new PluginWfArray($this->get($path_to_key));
      }
    }else{
      return null;
    }
  }
  public function setUnset($path_to_key){
    if(!$this->root_path_to_key){
      $this->yml = wfArray::setUnset($this->yml, $path_to_key);      
    }else{
      $this->yml = wfArray::setUnset($this->yml, $this->root_path_to_key.'/'.$path_to_key);      
    }
  }
  
  
  /**
   * Save yml.
   */
  public function save(){
    if(!file_exists($this->file)){
      file_put_contents($this->file , "{}");
    }
    wfSettings::setSettings($this->file, $this->yml);
  }
  
  public function sort($key, $desc = false){
    if($this->get()){
      $this->set(null, wfArray::sortMultiple($this->get(), $key, $desc));
    }
  }
  
  public function dump(){
    wfHelp::yml_dump($this->yml);
  }
  
  
}