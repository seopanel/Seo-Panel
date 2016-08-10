<?php

abstract class Score{

    protected $tag;
    protected $label;
    protected $weight = 1;
    protected $type = 'report';
    protected $settings = array();
    protected $default_settings = array();
    protected $default_weight = 1;
    protected $error_pre = 100000;
    private $active = TRUE;

    abstract public function calc_score($info);

    public function validate_settings($settings){
        if(isset($settings['clean_settings'])){
            $ret = $settings;
            if(!is_array($settings['clean_settings'])){
                $ret['clean_settings'] = array();
            }
        }else{
            if(is_array($settings)){
                $ret['clean_settings'] = $settings;
            }else{
                $ret['clean_settings'] = array();
            }
        }
        $ret['clean_settings'] = array_merge($this->default_settings, $ret['clean_settings']);
        $ret['submitted_settings'] = $settings;
        if(!isset($ret['error']) || empty($ret['error'])){
            $ret['error'] = '';
        }
        if(!isset($ret['error_code']) || empty($ret['error_code'])){
            $ret['error_code'] = 0;
        }
        return $ret;
    }

    final public function show_settings(){
        $ret_settings = array();
        if(is_array($this->settings)){
            $setting_fields = array('label','type','val');
            foreach($this->settings as $k => $v){
                $ins_array = array();
                if(!is_array($v)){
                    continue;
                }
                foreach($setting_fields as $field){
                    if(!isset($v[$field])){
                        continue 2;
                    }else{
                        $ins_array[$field] = $v[$field];
                    }
                }
                if(is_numeric($k)){
                    $tag = $this->tag."_".absint($k);
                }else{
                    $tag = $k;
                }
                $ret_settings[$tag] = $ins_array;
            }
        }
        return $ret_settings;
    }
    
    final public function save_setting($settings = NULL){
        $data = Array ("set_val" => '',
                   "set_label" => $this->label,
                   "set_category" => 'ign_score_setting',
                   "set_type" => 'text',
                   "display" => 0
        );
        if(is_array($settings)){
            $ret = $this->validate_settings($settings);
        }else if(is_array($this->settings)){
            $ret = $this->validate_settings($this->settings);
        }else{
            $this->settings = array();
            $ret = array();
            $ret['clean_settings'] = FALSE;
        }
            
        if(isset($ret['clean_settings']) && is_array($ret['clean_settings'])){
            $data['set_val'] = $ret['clean_settings'];
            $updated = update_setting($this->tag, $data);
            if(!$updated){
                $ret['error'] = 'Failed to update settings';
                $ret['error_code'] = $this->error_pre + 101;
            }else{
                $this->settings = $ret['clean_settings'];
                do_action('save_'.$this->tag.'_settings',$ret['clean_settings']);
            }
        }else{
            if(!isset($ret['error']) || empty($ret['error'])){
                $ret['error'] = 'Settings not valid';
            }
            if(!isset($ret['error_code']) || empty($ret['error_code'])){
                $ret['error_code'] = $this->error_pre + 100;
            }
        }
        if(!isset($ret['submitted_settings']) || !is_array($ret['submitted_settings'])){
            $ret['submitted_settings'] = $settings;
        }
        return $ret;
    }
    
     final public function load_settings(){
        $active = get_setting_value($this->tag."_ACTIVE");
        if($active != '2'){
            $this->active = TRUE;
        }else{
            $this->active = FALSE;
        }
        $settings = get_setting_value($this->tag);
        $ret = $this->validate_settings($settings);
        if(isset($ret['error_code']) && $ret['error_code'] > 0){
            $this->settings = $this->default_settings;
        } else if( isset($ret['clean_settings']) && is_array($ret['clean_settings'])){
            $this->settings = array_merge($this->default_settings,$ret['clean_settings']);
            return;
        }else{
            $this->settings = $this->default_settings;
        }
        $this->save_setting($this->default_settings);
    }

   final public function save_weight($weight = FALSE){
        $data = Array ("set_val" => '',
                   "set_label" => $this->label.' weight',
                   "set_category" => 'ign_score_weight',
                   "set_type" => 'small',
                   "display" => 0
        );
        $this->weight = $this->clean_weight($weight);
        $data['set_val'] = $this->weight;
        update_setting($this->tag.'_WEIGHT', $data);
        return $this->weight;
    }
    
    final public function clean_weight($weight = FALSE){
        if($weight && is_numeric($weight)){
           return absint($weight);
        }else if(is_numeric($this->weight)){
            return absint($this->weight);
        }else{
            return $this->default_weight;
        }
    }
    
    final public function load_weight(){
        $weight = get_setting_value($this->tag.'_WEIGHT');
        $this->weight = $this->clean_weight($weight);
        if($this->weight != $weight){
            $this->save_weight(); 
        }
        return $this->weight;
    }
    
    final public function get_settings(){
        if(is_array($this->settings)){
            return $this->settings;
        }else{
            $this->settings = array();
            return array();
        }
    }

    final public function is_type($type){
        return $this->type ==  $type;
    }

    final public function get_tag(){
        return $this->tag;
    }

    final public function get_label(){
        return $this->label;
    }

    final public function get_weight(){
        $this->weight = $this->clean_weight();
        return $this->weight;
    }

    final public function get_type(){
        return $this->type;
    }
    
    final public function activate(){
        if(!$this->active){
            $data = Array ("set_val" => 1,
                       "set_label" => $this->label.' active',
                       "set_category" => 'ign_score_active',
                       "set_type" => 'small',
                       "display" => 0
            );
            if(update_setting($this->tag.'_ACTIVE', $data)){
                $this->active = TRUE;
            }
        }
    }
    
    final public function deactivate(){
        if($this->active){
            $data = Array ("set_val" => 2,
                       "set_label" => $this->label.' active',
                       "set_category" => 'ign_score_active',
                       "set_type" => 'small',
                       "display" => 0
            );
            if(update_setting($this->tag.'_ACTIVE', $data)){
                $this->active = FALSE;
            }
        }
    }
    
    final public function is_active(){
        return $this->active;
    }
    
}

global $scores;
$default_types = array('report','website');
$add_types = apply_filters('score_types', array());
$score_types = array_merge($default_types,$add_types);
if(!is_array($scores)){
    $scores = array();
}
foreach($score_types as $k){
    if(!isset($scores[$k]) || !is_array($scores[$k])){
        $scores[$k] = array();
    }
}
