<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	   *
 *   sendtogeo@gmail.com   												   *
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

# class defines all blog controller functions
class BlogController extends Controller{

	# func to show all blogs
	function listBlogs($info=[]){
		$whereCond = "status=1 and link_page=''";
		$whereCond .= !empty($info['tag']) ? " and tags like '%".addslashes(trim(urldecode($info['tag'])))."%'" : "";
		$whereCond .= !empty($info['search']) ? " and blog_content like '%".addslashes($info['search'])."%'" : "";
		$countInfo = $this->dbHelper->getRow("cust_blogs", $whereCond, "count(*) count");
		$totalPageCount = ceil(($countInfo['count'] / SP_PAGINGNO));
		$currPage = intval($info['page']);
		$currPage = $currPage ? $currPage : 1;
		
		$whereCond .= " order by created_time desc";
		$start = ($currPage - 1) * SP_PAGINGNO;
		$whereCond .= " limit $start," . SP_PAGINGNO;
		$blogList = $this->dbHelper->getAllRows("cust_blogs", $whereCond);
		$blogBaseLink = SP_WEBPATH . "/blog.php?search=".$info['search']."&tag=".$info['tag'];
		$this->set("blogBaseLink", $blogBaseLink);
		$this->set("post", $info);
		
		$olderPage = ($currPage < $totalPageCount) ? $currPage + 1 : 0;
		$newerPage = ($currPage > 1) ? $currPage - 1 : 0;
		$this->set('olderPage', $olderPage);
		$this->set('newerPage', $newerPage);		
		$this->set('blogList', $blogList);
		$this->render('blog/blog_list');
	}

	# func to show a blog
	function showBlog($blogId){
		$whereCond = "status=1 and id=" . intval($blogId);
		$blogInfo = $this->dbHelper->getRow("cust_blogs", $whereCond);
		$this->set('spTitle', $blogInfo['meta_title']);
		$this->set('spDescription', $blogInfo['meta_description']);
		$this->set('spKeywords', $blogInfo['meta_keywords']);
		$this->set('blogInfo', $blogInfo);
		$this->render('blog/blog_show');
	}	
		
}
?>