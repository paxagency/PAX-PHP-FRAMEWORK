<?php
class controlExample {
    public function getString($get=[],$post=[]) {
        return "Class Called";
    }
    public function getNumber($get=[],$post=[]) {
        return 22;
    }
    public function getArray($get=[],$post=[]) {
        return ["This url is calling the class controlExample","And calling the method getArray"];
    }
    public function getAssoc($get=[],$post=[]) {
        return ['success'=>1];
    }
}
?>
