<?php

class ScoreIndexedGoogle extends Score{
    
    function __construct() {
        $this->tag = 'google_indexed';
        $this->label = 'Google index';
        $this->weight = 10;
        $this->type = 'report';
        $this->settings = array();
        $this->default_settings = array();
        $this->default_weight = 10;
        $this->error_pre = 109000;
        $this->load_settings();
        $this->load_weight();
    }
    
    function calc_score($info) {
        $ret = array();
        if(isset($info[$this->tag])){
            $se = 'Google';
            $spTextSA = $this->getLanguageTexts('siteauditor', $_SESSION['lang_code']);
            if($info[$this->tag]) {
                $ret['scoreInfo'] = 1;                
            } else {
                $ret['scoreInfo'] = -1;
                $msg = $spTextSA["The page is not indexed in"]." ".$se;
                 $ret['commentInfo'] = formatErrorMsg($msg, 'error', '');
            }   
        }
        return $ret;
    }
    
    function validate_settings($settings) {
        return parent::validate_settings($settings);
    }
    
}
$score_object = New ScoreIndexedGoogle();
register_score($score_object->get_tag(), $score_object);