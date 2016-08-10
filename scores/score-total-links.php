<?php

class ScoreTotalLinks extends Score{
    
    function __construct() {
        $this->tag = 'total_links';
        $this->label = 'total links';
        $this->weight = 1;
        $this->type = 'report';
        $this->settings = array();
        $this->default_settings = array(
            'SA_TOTAL_LINKS_MAX' => array('label' => 'Maximum links in a page','type' => 'small','val' => 50)
            );
        $this->default_weight = 1;
        $this->error_pre = 105000;
        $this->load_settings();
        $this->load_weight();
        add_action('save_'.$this->tag.'_settings', array($this,'update_constants'));
    }
    
    function calc_score($info) {
        $ret = array();
        if(isset($info[$this->tag])){
            // check meta description length
            $spTextSA = $this->getLanguageTexts('siteauditor', $_SESSION['lang_code']);
            if ( $info[$this->tag] >= $this->settings['SA_TOTAL_LINKS_MAX']['val'] ) {
                $ret['scoreInfo'] = -1;
                $msg = $spTextSA["The total number of links in page is greater than"]." ".$this->settings['SA_TOTAL_LINKS_MAX']['val'];
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

        if(isset($settings['SA_TOTAL_LINKS_MAX'])){
            if(is_numeric($settings['SA_TOTAL_LINKS_MAX'])){
                $clean_settings['SA_TOTAL_LINKS_MAX']['val'] = absint($settings['SA_TOTAL_LINKS_MAX']);
            }else if(is_array($settings['SA_TOTAL_LINKS_MAX']) && isset ($settings['SA_TOTAL_LINKS_MAX']['val']) &&
                   is_numeric($settings['SA_TOTAL_LINKS_MAX']['val'])){
                $clean_settings['SA_TOTAL_LINKS_MAX']['val'] = absint($settings['SA_TOTAL_LINKS_MAX']['val']);
            }else{
                if(isset($this->settings['SA_TOTAL_LINKS_MAX']['val'])){
                    $clean_settings['SA_TOTAL_LINKS_MAX']['val'] = $this->settings['SA_TOTAL_LINKS_MAX']['val'];
                }
                $ret['error'] .= 'Invalid '.$this->label.' on page|';
                if($ret['error_code'] > 0){
                    $ret['error_code'] = $this->error_pre + 000;
                }else{
                    $ret['error_code'] = $this->error_pre + 002;
                }
            }
        }else if(isset($this->settings['SA_TOTAL_LINKS_MAX']['val'])){
            $clean_settings['SA_TOTAL_LINKS_MAX']['val'] = $this->settings['SA_TOTAL_LINKS_MAX']['val'];
        }
        $ret['clean_settings'] = $clean_settings;
        return parent::validate_settings($ret);
    }
    
    function update_constants($settings){
        $data = array();
        $data["set_val"] = $settings['SA_TOTAL_LINKS_MAX']['val'];
        if(update_setting('SA_TOTAL_LINKS_MAX', $data) && function_exists('runkit_constant_redefine')){
            runkit_constant_redefine ('SA_TOTAL_LINKS_MAX',$settings['SA_TOTAL_LINKS_MAX']['val']);
        }
    }
}
$score_object = New ScoreTotalLinks();
register_score($score_object->get_tag(), $score_object);