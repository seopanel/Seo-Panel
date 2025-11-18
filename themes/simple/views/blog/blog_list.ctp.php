<div class="col-12" id="home_screen">
            <!-- Search Section at Top -->
            <div class="blog_search" style="float: none !important; margin-bottom: 30px; margin-top: 20px; text-align: center !important; width: 100% !important;">
            	<form action="<?php echo SP_WEBPATH . "/blog.php"?>" method="post">
            		<div style="max-width: 600px; margin: 0 auto;">
            			<input type="text" name="search" value="<?php echo $post['search']?>" placeholder="Search blogs by keyword, title, or content..." style="width: 100% !important; padding: 12px 20px; border: 1px solid #ddd; border-radius: 25px; font-size: 15px;">
            		</div>
            	</form>
            </div>
            <?php
            if (!empty($blogList)) {
	            foreach ($blogList as $blogInfo) {
	            	$publishedTime = strtotime($blogInfo['updated_time'])
	            	?>
	            	<div class="blog_section">
	            		<div class="blog_List_head">
	            			<a href="<?php echo SP_WEBPATH . "/blog.php?id=" . $blogInfo['id']?>">
	            				<?php echo $blogInfo['blog_title']?>
	            			</a>
	            			<p>Posted on <?php echo date('F d, Y', $publishedTime);?> by Admin</p>
	            		</div>
	            		<div class="blog_body">
	            			<p><?php echo convertMarkdownToHtml($blogInfo['blog_content'])?></p>
	            		</div>
		            	<div class="blog_tags">
		            		Tags: 
		            		<?php
		            		$tagList = explode(",", $blogInfo['tags']);
		            		foreach ($tagList as $tag) {
		            			$tag = trim($tag);
		            			if (!empty($tag)) {
		            				?>
		            				<a href="<?php echo SP_WEBPATH . "/blog.php?tag=". urlencode($tag);?>"><?php echo $tag;?></a>&nbsp;
		            				<?php
		            			}
		            		}
		            		?>
		            	</div>
	            	</div>
	            	<?php
	            }
	            ?>
	            
	            <?php if (!empty($olderPage)) { ?>
		            <div style="float: left;width: 40%;" class="blog_paginate_div">
		            	<a href="<?php echo $blogBaseLink . "&page=$olderPage";?>">&lt;&lt; <?php echo $spBlogText['Older Posts']?></a>
		            </div>
	            <?php }?>
	            
	            <?php if (!empty($newerPage)) { ?>
		            <div style="float: left;width: 40%;" class="blog_paginate_div">
		            	<a href="<?php echo $blogBaseLink . "&page=$newerPage";?>"><?php echo $spBlogText['Newer Posts']?> &gt;&gt;</a>
		            </div>
	            <?php }?>
			<?php } else {?>
				<div class="blog_section">
	            	<div class="blog_List_head">
	            		<a><?php echo $spBlogText['Nothing Found']?></a>
	            		<br><br>
						<span><?php echo $spBlogText['NothingFound_text2']?></span>
	            	</div>
	            </div>
			<?php }?>
</div>