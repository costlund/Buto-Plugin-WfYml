<?php
/**
 * Get yml as object with a save method.
 */
class PluginWfYml{
  public $file = null;
  public $yml = null;
  public $file_exists = false;
  public $root_path_to_key = null; // When using get/set this is included as key.
  function __construct($file, $root_path_to_key = null, $replace = array()) {
    /**
     * 
     */
    wfPlugin::includeonce('wf/array');
    wfPlugin::includeonce('wf/arraysearch');
    /**
     * 
     */
    $this->file = wfSettings::replaceTheme($file);
    $this->file = wfSettings::addRoot($this->file);
    if($root_path_to_key){
      $this->root_path_to_key = $root_path_to_key;
    }
    if(!file_exists($this->file)){
      $this->yml = array();  
    }else{
      $this->file_exists = true;
      $this->yml = wfFilesystem::loadYml($this->file, true, $replace);
      if(!is_array($this->yml)){
        /**
         * If yml file only contains "''" we has to set it to an empty array.
         */
        $this->yml = array();  
      }
    }
  }
  /**
   * Get filename.
   */
  public function getFilename(){
    return $this->file;
  }
  /**
   * Get.
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
   * getString
   */
  public function getString($path_to_key = null){
    $data = $this->get($path_to_key);
    $data = sfYaml::dump($data, 99);
    return $data;
  }
  /**
   * Set.
   */
  public function set($path_to_key, $value){
    if($path_to_key){
      if(!$this->root_path_to_key){
        if($path_to_key === true){
          $this->yml[] = $value;
        }else{
          $this->yml = wfArray::set($this->yml, $path_to_key, $value);
        }
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
  public function setById($id, $key = null, $value = null){
    wfPlugin::includeonce('wf/arraysearch');
    $wf_arraysearch = new PluginWfArraysearch();
    $wf_arraysearch->data = array('key_name' => 'id', 'key_value' => $id, 'data' => $this->yml);
    $data = $wf_arraysearch->get();
    if(sizeof($data)>0){
      $path_to_key = $data[0];
      $path_to_key = wfPhpfunc::substr($path_to_key, 1);
      $path_to_key = wfPhpfunc::str_replace('/attribute/id', '', $path_to_key);
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
   * Unset by id.
   */
  public function unsetById($id){
    wfPlugin::includeonce('wf/arraysearch');
    $wf_arraysearch = new PluginWfArraysearch();
    $wf_arraysearch->data = array('key_name' => 'id', 'key_value' => $id, 'data' => $this->yml);
    $data = $wf_arraysearch->get();
    if(sizeof($data)>0){
      $path_to_key = $data[0];
      $path_to_key = wfPhpfunc::substr($path_to_key, 1);
      $path_to_key = wfPhpfunc::str_replace('/attribute/id', '', $path_to_key);
      $this->setUnset($path_to_key);
    }else{
      echo 'Could not find element with id '.$id.'.<br>';
    }
  }
  /**
   * Get element by id. 
   * Hole element or specific value if key is set.
   * @param string $id
   * @param string $key
   * @return PluginWfArray
   */
  public function getById($id, $key = null){
    wfPlugin::includeonce('wf/arraysearch');
    wfPlugin::includeonce('wf/array');
    $wf_arraysearch = new PluginWfArraysearch();
    $wf_arraysearch->data = array('key_name' => 'id', 'key_value' => $id, 'data' => $this->yml);
    $data = $wf_arraysearch->get();
    if(sizeof($data)>0){
      $path_to_key = $data[0];
      $path_to_key = wfPhpfunc::substr($path_to_key, 1);
      $path_to_key = wfPhpfunc::str_replace('/attribute/id', '', $path_to_key);
      if($key){
        return new PluginWfArray($this->get($path_to_key.'/'.$key));
      }else{
        return new PluginWfArray($this->get($path_to_key));
      }
    }else{
      return null;
    }
  }
  /**
   * Unset.
   */
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
      wfFilesystem::createFile($this->file, "{}");
    }
    wfSettings::setSettings($this->file, $this->yml);
  }
  /**
   * Sort.
   */
  public function sort($key = null, $desc = false){
    if($key){
      if($this->get()){
        $this->set(null, wfArray::sortMultiple($this->get(), $key, $desc));
      }
    }else{
      $data = $this->yml;
      if(!$desc){
        ksort($data);
      }else{
        krsort($data);
      }
      $this->yml = $data;
    }
  }
  /**
   * Dump.
   */
  public function dump(){
    wfHelp::yml_dump($this->yml);
  }
  /**
   * Check if array is associative.
   * @param array $data
   * @return boolean
   */
  private function isArrayAssociative($data){
    foreach ($data as $key => $value) {
      if(!is_numeric($key)){
        return true;
      }
    }
    return false;
  }
  /**
   * Set values where tag match data key.
   * Example of usage is to replace innerHTML keys in an array where content is rs:name and data has a key with this id.
   * <p>Example of element where tags are id and city and these keys exist in data array:</p>
   * <pre>
   * type: span
   * attribute:
   * __data-id: 'rs:id'  
   * innerHTML: 'rs:city'
   * </pre>
   * @param array $data Array with matching keys.
   * @param string $tag Text before colon.
   * @param boolean $clear_nomatch If clear where key not exist in data.
   * @return null
   */
  public function setByTag($data, $tag = 'rs', $clear_nomatch = false){
    /**
     */
    if(is_null($data)){
      $data = array();
    }
    /**
     * Check if data is an array.
     */
    if(!is_array($data)){
      throw new Exception(__CLASS__.'::'.__FUNCTION__." says: Param data is not an array ($data)!");
    }
    /**
     * Check if array is associative.
     */
    if(sizeof($data) > 0 && !$this->isArrayAssociative($data)){
      throw new Exception("PluginWfYml says: Array in method setByTag is not associative."); 
    }
    /**
     * Include plugins.
     */
    wfPlugin::includeonce('wf/array');
    wfPlugin::includeonce('wf/arraysearch');
    /**
     * Set array as object.
     */
    if(!$this->root_path_to_key){
      $element = new PluginWfArray($this->yml);
    }else{
      $element = new PluginWfArray($this->yml);
      $element = new PluginWfArray($element->get($this->root_path_to_key));
    }
    /**
     */
    $data = new PluginWfArray($data);
    /**
     * Search keys.
     */
    $search = new PluginWfArraysearch(true);
    $search->data = array('key_name' => '', 'key_value' => '', 'data' => $element->get());
    $keys = $search->get();
    /**
     * Loop keys.
     */
    foreach ($keys as $key => $value) {
      $str = $element->get(wfPhpfunc::substr($value, 1));
      /**
       * If key match.
       */
      if(wfPhpfunc::substr($str, 0, wfPhpfunc::strlen($tag)+1) == $tag.':'){
        /**
         * 
         */
        $tag_key = wfPhpfunc::substr($str, wfPhpfunc::strlen($tag)+1);
        /**
         * If key exist in data.
         */
        if(wfArray::isKey($data->get(), $tag_key)){
          $this->set(wfPhpfunc::substr($value, 1), $data->get($tag_key));
        }elseif(array_key_exists($tag_key, $data->array)){
          $this->set(wfPhpfunc::substr($value, 1), $data->get($tag_key));
        }elseif($clear_nomatch){
          $this->set(wfPhpfunc::substr($value, 1), null);
        }
      }
    }
    return null;
  }
  /**
   * Clear tags.
   * @param Array $tags Or string
   */
  public function clearByTags($tags = 'rs'){
    if(!is_array($tags)){
      $this->setByTag(array(), $tags, true);
    }else{
      foreach ($tags as $key => $value) {
        $this->setByTag(array(), $value, true);
      }
    }
    return null;
  }
  /**
   * Merge data.
   * @param array $value
   */
  public function merge($value){
    $this->yml = array_merge($this->yml, $value);
    return null;
  }
}
