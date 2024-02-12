<?php
#[AllowDynamicProperties]
class modelExample {
	public $inject = ["utilExample"];
    public function __construct() {}
    public function getString($get=[],$post=[]) {
        return "Class Called";
    }
    public function getNumber($get=[],$post=[]) {
        return 22;
    }
    public function getArray($get=[],$post=[]) {
        return [1,2,3,4];
    }
    public function getAssoc($get=[],$post=[]) {
        return ["url"=>"This URL is calling the modelExample class, getAssoc method","format"=>"By default an object outputs JSON","pretty"=>"Adding the ?pretty get parameter the JSON is pretty print","success"=>true];
    }
    public function useInjection($get=[],$post=[]) {
        return "This url is calling modeExample class, useInjection method. <br>And is using the utilExample class injected to get: ".$this->utilExample->roundNumber(1000.234233425);
    }
}
?>