<?php
class controlExample {
    public function __construct() {}
    public function getString($get=[],$post=[]) {
        return "Class Called";
    }
    public function getNumber($get=[],$post=[]) {
        return 22;
    }
    public function getArray($get=[],$post=[]) {
        return [2,3,4];
    }
    public function getAssoc($get=[],$post=[]) {
        return ['success'=>1];
    }
}
?>
