<?php

require_once 'PHPUnit/Autoload.php';
require_once 'PHP/CodeCoverage.php';

class LimeCodeCoverage extends PHP_CodeCoverage
{
  //protected $blackList =  '/(vendor\/|Plugin\/test\/|\/map\/|\/base\/|\/om\/|\.yml|\.xml)/';
  protected $blackList =  '/(vendor\/|Plugin\/test\/|\/map\/|\/base\/|\/om\/|\.yml|\.xml\/|\/tmp)/';
  protected $folder;

    //++AZ++public function append(array $data, $id = NULL, array $filterGroups = array('DEFAULT'))
    public function append(array $data, $id = NULL, $append = TRUE)
    {
        $this->applyIgnoreFilter($data);

        parent::append($data,$id,$append);
    }

    protected function applyIgnoreFilter(&$data)
    {
      foreach($data as $file => $value)
      {
        if(($this->folder && !strpos($file, $this->folder)) || preg_match($this->blackList,$file) !== 0)
        {
          unset($data[$file]);
        }
      }
    }

    public function setFolder($folder)
    {
      $this->folder = $folder;
    }

}