<?php

class ScorePageRank extends Score{
    
    function __construct() {
        $this->tag = 'pagerank';
        $this->label = 'Moz Rank';
        $this->weight = 1;
        $this->type = 'report';
        $this->settings = array();
        $this->default_settings = array(
            'SA_PR_CHECK_LEVEL_SECOND' => array('label' => 'MozRank check level second','type' => 'small','val' => 6),
            'SA_PR_CHECK_LEVEL_FIRST' => array('label' => 'MozRank check level first','type' => 'small','val' => 3)
            );
        $this->default_weight = 10;
        $this->error_pre = 106000;
        $this->load_settings();
        $this->load_weight();
        add_action('save_'.$this->tag.'_settings', array($this,'update_constants'));
    }
    
    function calc_score($info) {
        $ret = array();
        if(isset($info[$this->tag])){
            // check google pagerank
            $spTextSA = $this->getLanguageTexts('siteauditor', $_SESSION['lang_code']);
            $ret['scoreInfo'] = $info['pagerank'] / 10;
            if ($info['pagerank'] >= $this->settings['SA_PR_CHECK_LEVEL_SECOND']['val']) {
                $msg = $spTextSA["The page is having exellent pagerank"];
                $ret['commentInfo'] = formatSuccessMsg($msg);
            } else if ($info['pagerank'] >= $this->settings['SA_PR_CHECK_LEVEL_FIRST']['val']) {
                $msg = $spTextSA["The page is having very good pagerank"];
                $ret['commentInfo'] = formatSuccessMsg($msg);
            } else if ($info['pagerank']) {
                $msg = $spTextSA["The page is having good pagerank"];
                $ret['commentInfo'] = formatSuccessMsg($msg);
            } else {
                $msg = $spTextSA["The page is having poor pagerank"];
                $ret['commentInfo'] = formatErrorMsg($msg, 'error', '');
            }
        }else{
            $ret['scoreInfo'] = 0;
            $msg = $spTextSA["The page is having poor pagerank"];
            $ret['commentInfo'] = formatErrorMsg($msg, 'error', '');
        }
        return $ret;
    }
    
    function validate_settings($settings) {
        $ret['submitted_settings'] = $settings;
        $clean_settings = $this->default_settings;
        $ret['error'] = '';
        $ret['error_code'] = 0;
        if(isset($settings['SA_PR_CHECK_LEVEL_FIRST'])){
            if(is_numeric($settings['SA_PR_CHECK_LEVEL_FIRST'])){
                $clean_settings['SA_PR_CHECK_LEVEL_FIRST']['val'] = absint($settings['SA_PR_CHECK_LEVEL_FIRST']);
            }else if(is_array($settings['SA_PR_CHECK_LEVEL_FIRST']) && isset ($settings['SA_PR_CHECK_LEVEL_FIRST']['val']) &&
                is_numeric($settings['SA_PR_CHECK_LEVEL_FIRST']['val'])){
                $clean_settings['SA_PR_CHECK_LEVEL_FIRST']['val'] = absint($settings['SA_PR_CHECK_LEVEL_FIRST']['val']);
            }else{
                if(isset($this->settings['SA_PR_CHECK_LEVEL_FIRST']['val'])){
                    $clean_settings['SA_PR_CHECK_LEVEL_FIRST']['val'] = $this->settings['SA_PR_CHECK_LEVEL_FIRST']['val'];
                }
                $ret['error'] .= 'Invalid '.$this->label.' First Level|';
                $ret['error_code'] = $this->error_pre + 001;
            }
        }else if(isset($this->settings['SA_PR_CHECK_LEVEL_FIRST']['val'])){
            $clean_settings['SA_PR_CHECK_LEVEL_FIRST']['val'] = $this->settings['SA_PR_CHECK_LEVEL_FIRST']['val'];
        }

        if(isset($settings['SA_PR_CHECK_LEVEL_SECOND'])){
            if(is_numeric($settings['SA_PR_CHECK_LEVEL_SECOND'])){
                $clean_settings['SA_PR_CHECK_LEVEL_SECOND']['val'] = absint($settings['SA_PR_CHECK_LEVEL_SECOND']);
            }else if(is_array($settings['SA_PR_CHECK_LEVEL_SECOND']) && isset ($settings['SA_PR_CHECK_LEVEL_SECOND']['val']) &&
                   is_numeric($settings['SA_PR_CHECK_LEVEL_SECOND']['val'])){
                $clean_settings['SA_PR_CHECK_LEVEL_SECOND']['val'] = absint($settings['SA_PR_CHECK_LEVEL_SECOND']['val']);
            }else{
                if(isset($this->settings['SA_PR_CHECK_LEVEL_SECOND']['val'])){
                    $clean_settings['SA_PR_CHECK_LEVEL_SECOND']['val'] = $this->settings['SA_PR_CHECK_LEVEL_SECOND']['val'];
                }
                $ret['error'] .= 'Invalid '.$this->label.' Second Level|';
                if($ret['error_code'] > 0){
                    $ret['error_code'] = $this->error_pre + 000;
                }else{
                    $ret['error_code'] = $this->error_pre + 002;
                }
            }
        }else if(isset($this->settings['SA_PR_CHECK_LEVEL_SECOND']['val'])){
            $clean_settings['SA_PR_CHECK_LEVEL_SECOND']['val'] = $this->settings['SA_PR_CHECK_LEVEL_SECOND']['val'];
        }
        if($clean_settings['SA_PR_CHECK_LEVEL_SECOND']['val'] > 10){
            $clean_settings['SA_PR_CHECK_LEVEL_SECOND']['val'] = 10;
        }
        if($clean_settings['SA_PR_CHECK_LEVEL_FIRST']['val'] > 10){
            $clean_settings['SA_PR_CHECK_LEVEL_FIRST']['val'] = 10;
        }
        if($clean_settings['SA_PR_CHECK_LEVEL_SECOND']['val'] < $clean_settings['SA_PR_CHECK_LEVEL_FIRST']['val']){
            if(isset($this->settings['SA_PR_CHECK_LEVEL_FIRST']['val']) && 
                    $this->settings['SA_PR_CHECK_LEVEL_SECOND']['val'] >= $clean_settings['SA_PR_CHECK_LEVEL_FIRST']['val']){
                $clean_settings['SA_PR_CHECK_LEVEL_SECOND']['val'] = $this->settings['SA_PR_CHECK_LEVEL_SECOND']['val'];
            }else if($this->default_settings['SA_PR_CHECK_LEVEL_SECOND']['val'] >= $clean_settings['SA_PR_CHECK_LEVEL_FIRST']['val']){
                $clean_settings['SA_PR_CHECK_LEVEL_SECOND']['val'] = $this->default_settings['SA_PR_CHECK_LEVEL_SECOND']['val'];
            }else{
                $clean_settings['SA_PR_CHECK_LEVEL_SECOND']['val'] = $clean_settings['SA_PR_CHECK_LEVEL_FIRST']['val'];
            }
            $ret['error'] .= $this->label.' Second Level must equal or exceed '.$this->label.' First Level';
            if($ret['error_code'] > 0){
                $ret['error_code'] = $this->error_pre + 000;
            }else{
                $ret['error_code'] = $this->error_pre + 003;
            }
        }
        $ret['clean_settings'] = $clean_settings;
        return parent::validate_settings($ret);
    }
    
    function update_constants($settings){
        $data = array();
        $data["set_val"] = $settings['SA_PR_CHECK_LEVEL_SECOND']['val'];
        if(update_setting('SA_PR_CHECK_LEVEL_SECOND', $data) && function_exists('runkit_constant_redefine')){
            runkit_constant_redefine ('SA_PR_CHECK_LEVEL_SECOND',$settings['SA_PR_CHECK_LEVEL_SECOND']['val']);
        }
        $data["set_val"] = $settings['SA_PR_CHECK_LEVEL_FIRST']['val'];
        if(update_setting('SA_PR_CHECK_LEVEL_FIRST', $data) && function_exists('runkit_constant_redefine')){
            runkit_constant_redefine ('SA_PR_CHECK_LEVEL_FIRST',$settings['SA_TITLE_MIN_LENGTH']['val']);
        }
    }
}
$score_object = New ScorePageRank();
register_score($score_object->get_tag(), $score_object);