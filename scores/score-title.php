<?php

class ScoreTitle extends Score{
    
    function __construct() {
        $this->tag = 'page_title';
        $this->label = 'page title';
        $this->weight = 1;
        $this->type = 'report';
        $this->settings = array();
        $this->default_settings = array(
            'SA_TITLE_MAX_LENGTH' => array('label' => 'Maximum length of page title','type' => 'small','val' => 80),
            'SA_TITLE_MIN_LENGTH' => array('label' => 'Minimum length of page title','type' => 'small','val' => 50)
            );
        $this->default_weight = 1;
        $this->error_pre = 101000;
        $this->load_settings();
        $this->load_weight();
        add_action('save_'.$this->tag.'_settings', array($this,'update_constants'));
    }
    
    function calc_score($info) {
        $ret = array();
        // check page title length
        if(isset($info[$this->tag])){
            $spTextSA = $this->getLanguageTexts('siteauditor', $_SESSION['lang_code']);
            $lengTitle = strlen($info[$this->tag]);
            if ( ($lengTitle <= $this->settings['SA_TITLE_MAX_LENGTH']['val']) && ($lengTitle >= $this->settings['SA_TITLE_MIN_LENGTH']['val']) ) {
                $ret['scoreInfo'] = 1;        
            } else {
                $ret['scoreInfo'] = -1;
                $msg = $spTextSA["The '.$this->label.' length is not between"]." ".$this->settings['SA_TITLE_MIN_LENGTH']['val']." & ".$this->settings['SA_TITLE_MAX_LENGTH']['val'];
                $ret['commentInfo'] = formatErrorMsg($msg, 'error', '');
            }
        }else{
            $ret['scoreInfo'] = -1;
            $msg = $spTextSA["The '.$this->label.' length is not between"]." ".$this->settings['SA_TITLE_MIN_LENGTH']['val']." & ".$this->settings['SA_TITLE_MAX_LENGTH']['val'];
            $ret['commentInfo'] = formatErrorMsg($msg, 'error', '');
        }
        return $ret;
    }
    
    function validate_settings($settings) {
        $ret['submitted_settings'] = $settings;
        $clean_settings = $this->default_settings;
        $ret['error'] = '';
        $ret['error_code'] = 0;
        if(isset($settings['SA_TITLE_MIN_LENGTH'])){
            if(is_numeric($settings['SA_TITLE_MIN_LENGTH'])){
                $clean_settings['SA_TITLE_MIN_LENGTH']['val'] = absint($settings['SA_TITLE_MIN_LENGTH']);
            }else if(is_array($settings['SA_TITLE_MIN_LENGTH']) && isset ($settings['SA_TITLE_MIN_LENGTH']['val']) &&
                is_numeric($settings['SA_TITLE_MIN_LENGTH']['val'])){
                $clean_settings['SA_TITLE_MIN_LENGTH']['val'] = absint($settings['SA_TITLE_MIN_LENGTH']['val']);
            }else{
                if(isset($this->settings['SA_TITLE_MIN_LENGTH']['val'])){
                    $clean_settings['SA_TITLE_MIN_LENGTH']['val'] = $this->settings['SA_TITLE_MIN_LENGTH']['val'];
                }
                $ret['error'] .= 'Invalid '.$this->label.' Min Length|';
                $ret['error_code'] = $this->error_pre + 001;
            }
        }else if(isset($this->settings['SA_TITLE_MIN_LENGTH']['val'])){
            $clean_settings['SA_TITLE_MIN_LENGTH']['val'] = $this->settings['SA_TITLE_MIN_LENGTH']['val'];
        }

        if(isset($settings['SA_TITLE_MAX_LENGTH'])){
            if(is_numeric($settings['SA_TITLE_MAX_LENGTH'])){
                $clean_settings['SA_TITLE_MAX_LENGTH']['val'] = absint($settings['SA_TITLE_MAX_LENGTH']);
            }else if(is_array($settings['SA_TITLE_MAX_LENGTH']) && isset ($settings['SA_TITLE_MAX_LENGTH']['val']) &&
                   is_numeric($settings['SA_TITLE_MAX_LENGTH']['val'])){
                $clean_settings['SA_TITLE_MAX_LENGTH']['val'] = absint($settings['SA_TITLE_MAX_LENGTH']['val']);
            }else{
                if(isset($this->settings['SA_TITLE_MAX_LENGTH']['val'])){
                    $clean_settings['SA_TITLE_MAX_LENGTH']['val'] = $this->settings['SA_TITLE_MAX_LENGTH']['val'];
                }
                $ret['error'] .= 'Invalid '.$this->label.' Max Length|';
                if($ret['error_code'] > 0){
                    $ret['error_code'] = $this->error_pre + 000;
                }else{
                    $ret['error_code'] = $this->error_pre + 002;
                }
            }
        }else if(isset($this->settings['SA_TITLE_MAX_LENGTH']['val'])){
            $clean_settings['SA_TITLE_MAX_LENGTH']['val'] = $this->settings['SA_TITLE_MAX_LENGTH']['val'];
        }
        if($clean_settings['SA_TITLE_MAX_LENGTH']['val'] < $clean_settings['SA_TITLE_MIN_LENGTH']['val']){
            if(isset($this->settings['SA_TITLE_MIN_LENGTH']['val']) && 
                    $this->settings['SA_TITLE_MAX_LENGTH']['val'] >= $clean_settings['SA_TITLE_MIN_LENGTH']['val']){
                $clean_settings['SA_TITLE_MAX_LENGTH']['val'] = $this->settings['SA_TITLE_MAX_LENGTH']['val'];
            }else if($this->default_settings['SA_TITLE_MAX_LENGTH']['val'] >= $clean_settings['SA_TITLE_MIN_LENGTH']['val']){
                $clean_settings['SA_TITLE_MAX_LENGTH']['val'] = $this->default_settings['SA_TITLE_MAX_LENGTH']['val'];
            }else{
                $clean_settings['SA_TITLE_MAX_LENGTH']['val'] = $clean_settings['SA_TITLE_MIN_LENGTH']['val'];
            }
            $ret['error'] .= $this->label.' Max Length must equal or exceed '.$this->label.' Min Length';
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
        $data["set_val"] = $settings['SA_TITLE_MAX_LENGTH']['val'];
        if(update_setting('SA_TITLE_MAX_LENGTH', $data) && function_exists('runkit_constant_redefine')){
            runkit_constant_redefine ('SA_TITLE_MAX_LENGTH',$settings['SA_TITLE_MAX_LENGTH']['val']);
        }
       $data["set_val"] = $settings['SA_TITLE_MIN_LENGTH']['val'];
        if(update_setting('SA_TITLE_MIN_LENGTH', $data) && function_exists('runkit_constant_redefine')){
            runkit_constant_redefine ('SA_TITLE_MIN_LENGTH',$settings['SA_TITLE_MIN_LENGTH']['val']);
        }
    }
}
$score_object = New ScoreTitle();
register_score($score_object->get_tag(), $score_object);