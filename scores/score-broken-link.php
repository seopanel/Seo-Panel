<?php

class ScoreBrokenLink extends Score{
    
    function __construct() {
        $this->tag = 'brocken';
        $this->label = 'broken link';
        $this->weight = 1;
        $this->type = 'report';
        $this->settings = array();
        $this->default_settings = array();
        $this->default_weight = 1;
        $this->error_pre = 104000;
        $this->load_settings();
        $this->load_weight();
    }
    
    function calc_score($info) {
        $ret = array();
        // if link brocken
        $spTextSA = $this->getLanguageTexts('siteauditor', $_SESSION['lang_code']);
        if ($info['brocken']) {
            $ret['scoreInfo'] = -1;
            $msg = $spTextSA["The page is brocken"];
            $ret['commentInfo'] = formatErrorMsg($msg, 'error', '');
        }else{
            $ret['scoreInfo'] = 0;
        }
        return $ret;
    }
    
    function validate_settings($settings) {
        return parent::validate_settings($settings);
    }
    
}
$score_object = New ScoreBrokenLink();
register_score($score_object->get_tag(), $score_object);