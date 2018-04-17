<?php
class TesttttBehavior extends Behavior {
    /**
     * 钩子对外统一接口
     */
    public function run(&$params){
        echo 123;exit;
    }

}