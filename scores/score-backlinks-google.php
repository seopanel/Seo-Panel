<?php

class ScoreBackLinksGoogle extends Score{
    
    function __construct() {
        $this->tag = 'google_backlinks';
        $this->label = 'Google backlinks';
        $this->weight = 10;
        $this->type = 'report';
        $this->settings = array();
        $this->default_settings = array(
            'SA_BL_CHECK_LEVEL' => array('label' => 'Backlinks check level','type' => 'small','val' => 25)
            );
        $this->default_weight = 10;
        $this->error_pre = 107000;
        $this->load_settings();
        $this->load_weight();
        add_action('save_'.$this->tag.'_settings', array($this,'update_constants'));
    }
    
    function calc_score($info) {
        $ret = array();
        if(isset($info[$this->tag])){
            // check meta description length
            $spTextSA = $this->getLanguageTexts('siteauditor', $_SESSION['lang_code']);
            $se =  'Google';
            if ($info[$this->tag] >= $this->settings['SA_BL_CHECK_LEVEL']['val']) {                
                $ret['scoreInfo'] = 1;
                $msg = $spTextSA["The page is having exellent number of backlinks in"]." ".$se;
                $ret['commentInfo'] = formatSuccessMsg($msg);
            } elseif($info[$this->tag]) {
                $ret['scoreInfo'] = .2;
                $msg = $spTextSA["The page is having good number of backlinks in"]." ".$se;
                $ret['commentInfo'] = formatSuccessMsg($msg);                
            } else {
                $ret['scoreInfo'] = 0;
                $msg = $spTextSA["The page is not having backlinks in"]." ".$se;
                $ret['commentInfo'] = formatErrorMsg($msg, 'error', '');
            }     
        }
        return $ret;
    }
    
    function validate_settings($settings) {
        $ret['submitted_settings'] = $settings;
        $clean_settings = $this->default_settings;
        $ret['error'] = '';
        $ret['error_code'] = 0;

        if(isset($settings['SA_BL_CHECK_LEVEL'])){
            if(is_numeric($settings['SA_BL_CHECK_LEVEL'])){
                $clean_settings['SA_BL_CHECK_LEVEL']['val'] = absint($settings['SA_BL_CHECK_LEVEL']);
            }else if(is_array($settings['SA_BL_CHECK_LEVEL']) && isset ($settings['SA_BL_CHECK_LEVEL']['val']) &&
                   is_numeric($settings['SA_BL_CHECK_LEVEL']['val'])){
                $clean_settings['SA_BL_CHECK_LEVEL']['val'] = absint($settings['SA_BL_CHECK_LEVEL']['val']);
            }else{
                if(isset($this->settings['SA_BL_CHECK_LEVEL']['val'])){
                    $clean_settings['SA_BL_CHECK_LEVEL']['val'] = $this->settings['SA_BL_CHECK_LEVEL']['val'];
                }
                $ret['error'] .= 'Invalid backlinks check level|';
                if($ret['error_code'] > 0){
                    $ret['error_code'] = $this->error_pre + 000;
                }else{
                    $ret['error_code'] = $this->error_pre + 002;
                }
            }
        }else if(isset($this->settings['SA_BL_CHECK_LEVEL']['val'])){
            $clean_settings['SA_BL_CHECK_LEVEL']['val'] = $this->settings['SA_BL_CHECK_LEVEL']['val'];
        }
        return parent::validate_settings($ret);
    }
    
    function update_constants($settings){
        $data = array();
        $data["set_val"] = $settings['SA_BL_CHECK_LEVEL']['val'];
        if(update_setting('SA_BL_CHECK_LEVEL', $data) && function_exists('runkit_constant_redefine')){
            runkit_constant_redefine ('SA_BL_CHECK_LEVEL',$settings['SA_BL_CHECK_LEVEL']['val']);
        }
    }
}
$score_object = New ScoreBackLinksGoogle();
register_score($score_object->get_tag(), $score_object);