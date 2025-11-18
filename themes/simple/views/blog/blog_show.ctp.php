<?php $publishedTime = strtotime($blogInfo['updated_time'])?>
<div class="col-12" id="home_screen">
            <!-- Search Section at Top -->
            <div class="blog_search" style="float: none !important; margin-bottom: 30px; margin-top: 20px; text-align: center !important; width: 100% !important;">
            	<form action="<?php echo SP_WEBPATH . "/blog.php"?>" method="post">
            		<div style="max-width: 600px; margin: 0 auto;">
            			<input type="text" name="search" value="" placeholder="Search blogs by keyword, title, or content..." style="width: 100% !important; padding: 12px 20px; border: 1px solid #ddd; border-radius: 25px; font-size: 15px;">
            		</div>
            	</form>
            </div>
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
            		<?php
            		$tagList = explode(",", $blogInfo['tags']);
            		foreach ($tagList as $tag) {
	            		$tag = trim($tag);
            			if (!empty($tag)) {
            				?>
            				<a href="<?php echo SP_WEBPATH . "/blog.php?tag=". urlencode($tag);?>"><?php echo $tag;?></a>
            				<?php
            			}
            		}
            		?>
            	</div>
            </div>
</div>
