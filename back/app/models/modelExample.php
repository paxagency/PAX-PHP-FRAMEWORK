<?php
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
        return ['success'=>1];
    }
    public function useInjection($get=[],$post=[]) {
        return $this->utilExample->roundNumber(1000.234233425);
    }
}
?>