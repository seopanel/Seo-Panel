<?php $publishedTime = strtotime($blogInfo['updated_time'])?>
<div class="col" id="home_screen">
	<!-- Search Section -->
	<div class="blog_search" style="margin-bottom: 30px;">
		<form action="<?php echo SP_WEBPATH . "/blog.php"?>" method="post">
			<div class="input-group" style="max-width: 500px;">
				<input type="text" name="search" class="form-control" value="<?php echo $post['search']?>" placeholder="Search blogs...">
				<div class="input-group-append">
					<button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Search</button>
				</div>
			</div>
		</form>
	</div>

	<!-- Back Button -->
	<div style="margin-bottom: 20px;">
		<a href="<?php echo SP_WEBPATH . "/blog.php"?>" class="btn btn-sm btn-outline-secondary">
			<i class="fa fa-arrow-left"></i> Back to Blog List
		</a>
	</div>

	<!-- Blog Article -->
	<article class="blog_article" style="background: white; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 40px;">
		<!-- Feature Image -->
		<?php
		$featureImage = !empty($blogInfo['feature_image']) ? SP_WEBPATH . '/' . $blogInfo['feature_image'] : '';
		$hasImage = !empty($featureImage);
		?>

		<?php if ($hasImage) { ?>
			<div class="blog_feature_image" style="width: 100%; height: 450px; overflow: hidden; position: relative;">
				<img src="<?php echo $featureImage?>" alt="<?php echo htmlspecialchars($blogInfo['blog_title'])?>" style="width: 100%; height: 100%; object-fit: cover;">
				<div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); padding: 40px 40px 30px;">
					<h1 style="color: white; font-size: 2.5rem; font-weight: 700; margin: 0; text-shadow: 0 2px 10px rgba(0,0,0,0.3);">
						<?php echo $blogInfo['blog_title']?>
					</h1>
				</div>
			</div>
		<?php } ?>

		<div style="padding: 40px;">
			<!-- Title (if no image) -->
			<?php if (!$hasImage) { ?>
				<h1 style="color: #2c3e50; font-size: 2.5rem; font-weight: 700; margin-bottom: 20px; line-height: 1.3;">
					<?php echo $blogInfo['blog_title']?>
				</h1>
			<?php } ?>

			<!-- Meta Info -->
			<div style="display: flex; align-items: center; padding-bottom: 25px; margin-bottom: 30px; border-bottom: 2px solid #f0f0f0;">
				<div style="display: flex; align-items: center; color: #666; font-size: 14px;">
					<div style="margin-right: 25px;">
						<i class="fa fa-calendar" style="color: #007bff; margin-right: 8px;"></i>
						<strong>Published:</strong> <?php echo date('F d, Y', $publishedTime);?>
					</div>
					<div>
						<i class="fa fa-user" style="color: #007bff; margin-right: 8px;"></i>
						<strong>Author:</strong> Admin
					</div>
				</div>
			</div>

			<!-- Blog Content -->
			<div class="blog_content" style="font-size: 16px; line-height: 1.8; color: #333; margin-bottom: 30px;">
				<?php echo convertMarkdownToHtml($blogInfo['blog_content'])?>
			</div>

			<!-- Tags Section -->
			<?php
			$tagList = explode(",", $blogInfo['tags']);
			if (!empty($blogInfo['tags'])) {
				?>
				<div style="padding-top: 30px; border-top: 2px solid #f0f0f0;">
					<div style="display: flex; align-items: center; flex-wrap: wrap; gap: 10px;">
						<strong style="color: #666; margin-right: 10px;">
							<i class="fa fa-tags"></i> Tags:
						</strong>
						<?php
						foreach ($tagList as $tag) {
							$tag = trim($tag);
							if (!empty($tag)) {
								?>
								<a href="<?php echo SP_WEBPATH . "/blog.php?tag=". urlencode($tag);?>"
								   style="display: inline-block; padding: 8px 18px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 25px; font-size: 13px; font-weight: 500; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);">
									<i class="fa fa-tag"></i> <?php echo $tag;?>
								</a>
								<?php
							}
						}
						?>
					</div>
				</div>
			<?php } ?>
		</div>
	</article>

	<!-- Back to Blog List Button -->
	<div class="text-center" style="margin: 40px 0;">
		<a href="<?php echo SP_WEBPATH . "/blog.php"?>" class="btn btn-primary btn-lg" style="border-radius: 30px; padding: 12px 40px; font-weight: 500;">
			<i class="fa fa-list"></i> View All Blog Posts
		</a>
	</div>
</div>

<style>
/* Blog Content Styling */
.blog_content p {
	margin-bottom: 20px;
}

.blog_content h1, .blog_content h2, .blog_content h3, .blog_content h4 {
	color: #2c3e50;
	margin-top: 30px;
	margin-bottom: 15px;
	font-weight: 600;
}

.blog_content h1 {
	font-size: 2rem;
}

.blog_content h2 {
	font-size: 1.75rem;
}

.blog_content h3 {
	font-size: 1.5rem;
}

.blog_content ul, .blog_content ol {
	margin-left: 20px;
	margin-bottom: 20px;
}

.blog_content li {
	margin-bottom: 8px;
}

.blog_content a {
	color: #007bff;
	text-decoration: underline;
}

.blog_content a:hover {
	color: #0056b3;
}

.blog_content img {
	max-width: 100%;
	height: auto;
	border-radius: 8px;
	margin: 20px 0;
	box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.blog_content blockquote {
	border-left: 4px solid #007bff;
	padding-left: 20px;
	margin: 20px 0;
	color: #555;
	font-style: italic;
	background: #f8f9fa;
	padding: 15px 20px;
	border-radius: 4px;
}

.blog_content code {
	background: #f4f4f4;
	padding: 2px 6px;
	border-radius: 3px;
	font-family: 'Courier New', monospace;
	font-size: 14px;
	color: #e83e8c;
}

.blog_content pre {
	background: #282c34;
	color: #abb2bf;
	padding: 20px;
	border-radius: 8px;
	overflow-x: auto;
	margin: 20px 0;
}

.blog_content pre code {
	background: none;
	padding: 0;
	color: inherit;
}

/* Tag hover effect */
.blog_content a[href*="tag"]:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(102, 126, 234, 0.5) !important;
}

/* Responsive */
@media (max-width: 768px) {
	.blog_feature_image {
		height: 300px !important;
	}

	.blog_feature_image h1 {
		font-size: 1.75rem !important;
	}

	.blog_article > div {
		padding: 25px !important;
	}

	.blog_content {
		font-size: 15px !important;
	}
}
</style>
