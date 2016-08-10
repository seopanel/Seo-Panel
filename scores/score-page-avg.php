<?php

class ScorePageAverage extends Score{
    
    function __construct() {
        $this->tag = 'page_avg';
        $this->label = 'Average Page Score';
        $this->weight = 10;
        $this->type = 'website';
        $this->settings = array();
        $this->default_settings = array();
        $this->default_weight = 10;
        $this->error_pre = 109000;
        $this->load_settings();
        $this->load_weight();
    }
    
    function calc_score($info) {
        global $sp_db;
        $ret = array();
        if(isset($info['project_id'])){
            $sp_db->where('crawled',1);
            $sp_db->where('project_id',$info['project_id']);
            $avgscore = $sp_db->getValue('auditorreports','sum(score)/count(*)');
            if(empty($avgscore)){
                $ret['scoreInfo'] = 0; 
            }else{
                $ret['scoreInfo'] = $avgscore;
            }
        }
        return $ret;
    }
    
    function validate_settings($settings) {
        return parent::validate_settings($settings);
    }
    
}
$score_object = New ScorePageAverage();
register_score($score_object->get_tag(), $score_object);