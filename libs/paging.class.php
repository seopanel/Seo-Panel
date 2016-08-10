<?php
/*******************************************************************************************
 Credits: dezignwork.com
 Desc:    This class will will create a pagination div with all the links, page numbers/link
 and highlighted current page. All the pagination content will be placed in a div.
 The style of the div can be changed.
 ********************************************************************************************/
class Paging {

	var $result;
	var $link_class;
	var $div_class;
	var $current_page;
	var $PAGE='Page';

	function loadPaging($result, $per_page=15){
		if(is_numeric($result)) {
		} else {
			if(!$result or mysql_num_rows($result)<1) { return 0; }
		}
		$this->result = $result;
		$this->per_page = $per_page;
	}

	function printPaging($scriptArgs='') {		
		
		$tmp = '';
		if(empty($this->current_page)) {
			$this->setCurrentPage(1);
		}
		if(!is_numeric($this->result)) {
			$num_rows = mysql_num_rows($this->result);
		} else {
			$num_rows = $this->result;
		}

		if($this->per_page==0) {
			return 0;
		}
		$pages_required = ceil($num_rows/$this->per_page);
		if(!empty($this->div_class)) { $div_class=' class="'.$this->div_class.'"'; }
		$tmp .= "<div$div_class>"; ": "; ": ";
		$page_line="";
		$start_page=$this->current_page-4;
		if($start_page<1) { $start_page=1; }
		$end_page=$this->current_page+4;
		if($end_page>$pages_required) { $end_page=$pages_required; }
		if(($end_page-$start_page)<8) {
			$diff=abs($end_page-9);
			for($i=0; $i<$diff; $i++) {
				if($end_page<$pages_required) {
					$end_page++;
				}
			}
		}

		# arrow left
		if($start_page>1) {
			if(!empty($this->action_path)) {
				$this->scriptArgs = $scriptArgs . "&pageno=1";
				$page_line .= $this->createPagingLink('&laquo;', '', '&nbsp;&nbsp;');				
			}
		}

		# pages
		for($i=$start_page; $i<=$end_page; $i++) {
			if($i>$end_page) { break; }
			if($this->current_page!=$i) {
				if(!empty($this->action_path)) {					
					$this->scriptArgs = $scriptArgs . "&pageno=$i";
					$page_line .= $this->createPagingLink($i, '', '&nbsp; | &nbsp;');
				}
			} else {
				$page_line.='<b>'.$i.'</b>&nbsp; | &nbsp;';
			}

		}
		$page_line=substr($page_line,0,strlen($page_line)-15);

		# arrow right
		if($end_page<$pages_required) {
			if(!empty($this->action_path)) {
				$this->scriptArgs = $scriptArgs . "&pageno=$pages_required";
				$page_line .= $this->createPagingLink('&raquo;', '&nbsp;&nbsp;', '');
			}
		}
		$tmp .= $page_line;
		$tmp .= '</div><div style="clear:both;"></div>';
		return $tmp;
	}

	# func to create paging link
	function createPagingLink($linkText='', $linkBefore='', $linkAfter=''){
				
		if(!empty($this->link_class)) { $linkClass=' class="'.$this->link_class.'"'; }
		
		if ($this->scriptFunction == 'link') {
			$link = "$linkBefore <a href='$this->scriptPath$this->scriptArgs' $linkClass>$linkText</a>$linkAfter";
		} elseif($this->scriptFunction == 'scriptDoLoadPost') {
			$link = "$linkBefore<a href='javascript:void(0);' $linkClass onclick=\"scriptDoLoadPost('$this->scriptPath', '$this->scriptForm', '$this->showArea', '$this->scriptArgs')\">$linkText</a>$linkAfter";	
		}else{			
			$link = "$linkBefore<a href='javascript:void(0);' $linkClass onclick=\"$this->scriptFunction('$this->scriptPath', '$this->showArea', '$this->scriptArgs')\">$linkText</a>$linkAfter";
		}
		return $link;		
	}
	

	# mutators
	function setActionPath($path) {
		$this->action_path = $path;
	}

	function setLinkClass($class) {
		$this->link_class = $class;
	}
	function setDivClass($class) {
		$this->div_class = $class;
	}

	function setPageLimit($limit) {
		$this->per_page = $limit;
	}
	function setCurrentPage($page) {
		$this->current_page = $page;
	}

	# func to print pages
	function printPages($scriptPath, $scriptForm='', $scriptfunction='scriptDoLoad', $showArea='content', $scriptArgs=''){
		
		$this->scriptPath = $scriptPath;
		$this->scriptFunction = $scriptfunction;
		$this->showArea = $showArea;
		$this->scriptForm = $scriptForm;
		$this->scriptArgs = $scriptArgs;
		
		$this->setActionPath(1);

		# determine current page number
		$pageNo = 0;
		if(isset($_POST["pageno"])){
			$pageNo = $_POST["pageno"];
		}elseif(isset($_GET["pageno"])){
			$pageNo = $_GET["pageno"];			
		}
		if(!empty($pageNo)) {
			$this->start = ($pageNo - 1) * $this->per_page;
			$this->setCurrentPage($pageNo);
		} else {
			$this->setCurrentPage(1);
			$this->start = 0;
		}
		return $this->printPaging($scriptArgs);
		
	}

}
?>