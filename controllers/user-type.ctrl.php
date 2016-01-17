<?php
/**************************************************************************
*   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	          *
*   sendtogeo@gmail.com   											      *
*                                                                         *
*   This program is free software; you can redistribute it and/or modify  *
*   it under the terms of the GNU General Public License as published by  *
*   the Free Software Foundation; either version 2 of the License, or     *
*   (at your option) any later version.                                   *
*                                                                         *
*   This program is distributed in the hope that it will be useful,       *
*   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
*   GNU General Public License for more details.                          *
*                                                                         *
*   You should have received a copy of the GNU General Public License     *
*   along with this program; if not, write to the                         *
*   Free Software Foundation, Inc.,                                       *
*   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
***************************************************************************/

/**
 * Class defines all user type controller functions
 */
class UserTypeController extends Controller
{
    
    public $userSpecFields = array('keywordcount','websitecount','price');
    
    /**
     * Function to list all the available user types
     * @params : Array of values to be passed
     * @return : Display the list
     */
    public function listUserTypes($info = '')
    {
        $info['pageno'] = intval($info['pageno']);
        $sql = "SELECT * FROM usertypes where id!=1";
        
        # pagination setup		
        $this->db->query($sql, true);
        $this->paging->setDivClass('pagingdiv');
        $this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
        $pagingDiv = $this->paging->printPages('user-types-manager.php?userid='.$info['userid']);
        $this->set('pagingDiv', $pagingDiv);
        $sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
        $userTypeList = $this->db->select($sql);
        
        // Set the spec details for user type
        foreach ($userTypeList as $key => $userType) {
            $sql = "select * from user_specs where user_type_id=" . $userType['id'];
            $userTypeSpecList = $this->db->select($sql);
            
            foreach ($userTypeSpecList as $userTypeSpec) {
                $userType[$userTypeSpec['spec_column']] = $userTypeSpec['spec_value'];
            }
            $userTypeList[$key] = $userType;
        }
        
        // if subscription plugin active
        if ($this->isPluginSubsActive) {
            $currencyCtrler = new CurrencyController();
            $this->set('currencyList', $currencyCtrler->getCurrencyCodeMapList());
        }
        
        $this->set('pageNo', $info['pageno']);
        $this->set('list', $userTypeList);
        $this->render('usertypes/list');
    }
    
    /**
     * Function to edit the user type selected
     * @params : $userTypeId - user type id, $listInfo - data array
     * @return : Display the edit form
     */
    public function editUserType($userTypeId, $listInfo='')
    {
        $userTypeId = intval($userTypeId);
        if (!empty($userTypeId)) {
            if (empty($listInfo)) {
                $listInfo = $this->__getUserTypeInfo($userTypeId);
                $listInfo['old_user_type'] = $listInfo['user_type'];
            }
            $listInfo['websitecount'] = stripslashes($listInfo['websitecount']);
            $listInfo['description'] = stripslashes($listInfo['description']);
            $listInfo['keywordcount'] = stripslashes($listInfo['keywordcount']);
            $listInfo['price'] = stripslashes($listInfo['price']);
            $listInfo['status'] = stripslashes($listInfo['status']);
            $this->set('post', $listInfo);
        
            // if subscription plugin active
            if ($this->isPluginSubsActive) {
                $currencyCtrler = new CurrencyController();
                $this->set('currencyList', $currencyCtrler->getCurrencyCodeMapList());
            }
            
            $this->render('usertypes/edit');
            exit;
        }
        $this->listUserTypes();
    }

    /**
     * Function to edit the user type selected
     * @params : $userTypeId - user type id
     * @return : returns the data
     */
    public function __getUserTypeInfo($userTypeId)
    {
        $userTypeId = intval($userTypeId);
        $sql = "select * from usertypes where id=$userTypeId";
        $listInfo = $this->db->select($sql, true);

        // Set the spec details for user type
        $sql = "select * from user_specs where user_type_id=" . $listInfo['id'];
        $userTypeSpecList = $this->db->select($sql);
            
        foreach ($userTypeSpecList as $userTypeSpec) {
            $listInfo[$userTypeSpec['spec_column']] = $userTypeSpec['spec_value'];
        }
        
        return empty($listInfo['id']) ? false :  $listInfo;
    }

    /**
     * Function to update the user type selected
     * @params : $listInfo - post values
     * @return : returns the data
     */
    public function updateUserType($listInfo)
    {
        $listInfo['id'] = intval($listInfo['id']);
        $this->set('post', $listInfo);
        $errMsg['user_type'] = formatErrorMsg($this->validate->checkBlank(trim($listInfo['user_type'])));
        $errMsg['websitecount'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['websitecount'])));
        $errMsg['keywordcount'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['keywordcount'])));
        
        // if subscription plugin active
        if ($this->isPluginSubsActive) {
            $errMsg['price'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['price'])));
        }
        
        // if no errors occured
        if (!$this->validate->flagErr) {
            if ($listInfo['user_type'] != $listInfo['old_user_type']) {
                if ($this->__checkUserType($listInfo['user_type'], $listInfo['id'])) {
                    $errMsg['user_type'] = formatErrorMsg($this->spTextWeb['User Type already exist']);
                    $this->validate->flagErr = true;
                }
            }
            
            if (!$this->validate->flagErr) {
                $sql = "update usertypes set
						user_type = '".addslashes($listInfo['user_type'])."',
						description = '".addslashes($listInfo['description'])."',
						status = '".intval($listInfo['user_type_status'])."'
						where id={$listInfo['id']}";
                
                if ($this->db->query($sql)) {
                    
                    // Delete the exisiting spec values
                    $sql = "delete from user_specs where user_type_id={$listInfo['id']}";
                    $this->db->query($sql);
                
                    // Get user type id of the current request
                    $sql = "insert into user_specs(user_type_id,spec_column,spec_value) values ";
                
                    // Loop through the data passed and create the value set
                    foreach ($this->userSpecFields as $field) {
                        $valueSet .=  "(" . $listInfo['id'] . ",'" . $field . "','" . addslashes($listInfo[$field]) . "'),";
                    }
                
                    $valueSet = trim($valueSet, ",");
                    $sql .= $valueSet;
                
                    $this->db->query($sql);
                    $this->listUserTypes();
                    exit;
                }
            }
        }
        $this->set('errMsg', $errMsg);
        $this->editUserType($listInfo['id'], $listInfo);
    }

    /**
     * Function to check the user type selected
     * @params : $userType
     * @return : boolean
     */
    public function __checkUserType($userType, $userTypeId = false)
    {
        $sql = "select id from usertypes where user_type='" . addslashes($userType) . "'";
        $sql .= empty($userTypeId) ? "" : " and id!=" . intval($userTypeId);
        $listInfo = $this->db->select($sql, true);
        return empty($listInfo['id']) ? false :  $listInfo['id'];
    }

    /**
     * Function to create a new user type
     * @params : $listInfo - post values
     * @return : returns the data
     */
    public function createUserType($listInfo)
    {
        $valueSet = "";
        $this->set('post', $listInfo);
        $errMsg['user_type'] = formatErrorMsg($this->validate->checkBlank(trim($listInfo['user_type'])));
        $errMsg['websitecount'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['websitecount'])));
        $errMsg['keywordcount'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['keywordcount'])));
        
        // if subscription plugin active
        if ($this->isPluginSubsActive) {
            $errMsg['price'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['price'])));
        }
                
        if (!$this->validate->flagErr) {
            if (!$this->__checkUserType($listInfo['user_type'])) {
                
                    // Set status value and sql
                    $status = $listInfo['user_type_status'] == "" ? 1 : intval($listInfo['user_type_status']);
                $sql = "insert into usertypes(user_type,description,status)
    				values('".addslashes($listInfo['user_type'])."','".addslashes($listInfo['description'])."',"
                             . $status . ")";
                    
                if ($this->db->query($sql)) {

                        // Get user type id of the current request
                        $userTypeId = $this->db->getMaxId('usertypes');
                    $sql = "insert into user_specs(user_type_id,spec_column,spec_value) values ";
                        
                        // Loop through the data passed and create the value set
                        foreach ($this->userSpecFields as $field) {
                            $valueSet .=  "(" . $userTypeId . ",'" . $field . "','" . addslashes($listInfo[$field]) . "'),";
                        }
                        
                    $valueSet = trim($valueSet, ",");
                    $sql .= $valueSet;
                        
                    $this->db->query($sql);
                    $this->listUserTypes();
                    exit;
                }
            } else {
                $errMsg['user_type'] = formatErrorMsg($this->spTextWeb['User Type already exist']);
            }
        }
        $this->set('errMsg', $errMsg);
        $this->newUserType($listInfo);
    }
    
    /**
     * Function to display add user type  form
     * @params : $info - data values array
     * @return : returns the data
     */
    public function newUserType($info='')
    {
        
        // if subscription plugin active
        if ($this->isPluginSubsActive) {
            $currencyCtrler = new CurrencyController();
            $this->set('currencyList', $currencyCtrler->getCurrencyCodeMapList());
        }
            
        $this->render('usertypes/new');
    }

    /**
     * Function to change the user type status
     * @params :  - $userTypeId - user type id, $status - user type id to be updated
     * @return : None
     */
    public function __changeStatus($userTypeId, $status)
    {
        $userTypeId = intval($userTypeId);
        $sql = "update usertypes set status=$status where id=$userTypeId";
        $this->db->query($sql);
    }

    /**
     * Function to delete the selected user type
     * @params : $userTypeId - user type id to be deleted
     * @return : None
     */
    public function __deleteUserType($userTypeId)
    {
        $userTypeId = intval($userTypeId);
        $sql = "delete from usertypes where id=$userTypeId";
        $this->db->query($sql);
    }
    
    /**
     * function to get all user types
     */
    public function getAllUserTypes($includeAdmin = false)
    {
        $sql = "select * from usertypes where status=1";
        $sql .= empty($includeAdmin) ? " and id!=1" : "";
        $sql .= " order by id";
        $uTypeList = $this->db->select($sql);
        $userTypeList = array();
        $priceList = array();
        
        // Set the spec details for user type
        foreach ($uTypeList as $userType) {
            $sql = "select * from user_specs where user_type_id=" . $userType['id'];
            $userTypeSpecList = $this->db->select($sql);
                
            foreach ($userTypeSpecList as $userTypeSpec) {
                $userType[$userTypeSpec['spec_column']] = $userTypeSpec['spec_value'];
                
                // if price column
                if ($userTypeSpec['spec_column'] == 'price') {
                    $priceList[$userType['id']] = $userTypeSpec['spec_value'];
                }
            }
            
            $userTypeList[$userType['id']] = $userType;
        }
        
        // sort and create new array
        asort($priceList);
        $sortUserTypeList = array();
        
        foreach ($priceList as $usertTypeId => $price) {
            $sortUserTypeList[] = $userTypeList[$usertTypeId];
        }
        
        return $sortUserTypeList;
    }
    
    /**
     * Function to get the user type spec details
     */
    public function getUserTypeSpec($userTypeId)
    {
        $sql = "select * from user_specs where user_type_id=" . $userTypeId;
        $userTypeSpecList = $this->db->select($sql);
            
        foreach ($userTypeSpecList as $userTypeSpec) {
            $userTypeSpecData[$userTypeSpec['spec_column']] = $userTypeSpec['spec_value'];
        }
        
        return $userTypeSpecData;
    }
    
    /**
     * Function to get the user type spec details by user
     */
    public function getUserTypeSpecByUser($userId)
    {
        $sql = "select * from users where id=" . $userId;
        $userDetails = $this->db->select($sql);
        
        $sql = "select * from user_specs where user_type_id=" . $userDetails[0]['utype_id'];
        $userTypeSpecList = $this->db->select($sql);
            
        foreach ($userTypeSpecList as $userTypeSpec) {
            $userTypeSpecData[$userTypeSpec['spec_column']] = $userTypeSpec['spec_value'];
        }
        
        return $userTypeSpecData;
    }
    
    # function to get default user type
    public function getDefaultUserTypeId()
    {
        $userTypeList = $this->getAllUserTypes();
        $defaultUserTypeId = !empty($userTypeList[0]['id']) ? $userTypeList[0]['id'] : false;
        return  $defaultUserTypeId;
    }
    
    # function to get admin user ype id
    public function getAdminUserTypeId()
    {
        $sql = "select id from usertypes where user_type='admin'";
        $userTypeInfo = $this->db->select($sql, true);
        return $userTypeInfo['id'];
    }
}
