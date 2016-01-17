<?php
class TestPlugin extends SeoPluginsController
{
    
    public function index()
    {
        $this->set('sectionHead', TEST_HEAD);
        $userId = isLoggedIn();
        
        $this->pluginRender('index');
    }
    
    public function settings($info)
    {
        $this->set('sectionHead', TEST_SET_HEAD);
        
        $showGraph = empty($info['graph']) ? 0 : 1;
        $this->set('showGraph', $showGraph);
        $this->pluginRender('settings');
    }
    
    public function show($info)
    {
        $img = empty($info['tp_type']) ? "inrecords.jpg" : "records.jpg";
        
        # example for select values from a database table
        /*$sql = "select * from testplugin";
        $list = $this->db->select($sql);*/
        
        # example to insert data to database tabel
        /*$sql = "insert into testplugin(title,description) values('test title','test description')";
        $list = $this->db->query($sql);*/
        
        $this->set('img', $img);
        $this->pluginRender('show');
    }
}
