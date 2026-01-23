<div class="col-12" id="home_screen">
	<!-- Search Section at Top -->
	<div class="blog_search" style="float: none !important; text-align: center !important; margin-bottom: 40px; margin-top: 20px; width: 100% !important;">
		<div class="row justify-content-center">
			<div class="col-md-8 col-lg-6">
				<form action="<?php echo SP_WEBPATH . "/blog.php"?>" method="post">
					<div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden;">
						<input type="text" name="search" class="form-control" value="<?php echo $post['search']?>" placeholder="Search blogs by keyword, title, or content..." style="border: none; padding: 15px 20px; font-size: 16px; width: auto !important;">
						<div class="input-group-append">
							<button class="btn btn-primary" type="submit" style="padding: 0 30px; border: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"><i class="fa fa-search"></i> Search</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<?php
	if (!empty($blogList)) {
		?>
		<!-- Blog Grid - Full Width -->
		<div class="row">
			<?php
			foreach ($blogList as $blogInfo) {
				$publishedTime = strtotime($blogInfo['updated_time']);
				$excerpt = strip_tags(convertMarkdownToHtml($blogInfo['blog_content']));
				$excerpt = strlen($excerpt) > 200 ? substr($excerpt, 0, 200) . '...' : $excerpt;

				// Feature image or placeholder
				$featureImage = !empty($blogInfo['feature_image']) ? SP_WEBPATH . '/' . $blogInfo['feature_image'] : '';
				$hasImage = !empty($featureImage);
				?>
				<div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
					<div class="card h-100 shadow-sm" style="border: none; border-radius: 8px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;">
						<!-- Feature Image or Placeholder -->
						<a href="<?php echo SP_WEBPATH . "/blog.php?id=" . $blogInfo['id']?>" style="text-decoration: none;">
							<?php if ($hasImage) { ?>
								<img src="<?php echo $featureImage?>" class="card-img-top" alt="<?php echo htmlspecialchars($blogInfo['blog_title'])?>" style="height: 250px; object-fit: cover;">
							<?php } else { ?>
								<div style="height: 250px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
									<i class="fa fa-image" style="font-size: 80px; color: rgba(255,255,255,0.3);"></i>
								</div>
							<?php } ?>
						</a>

						<div class="card-body" style="padding: 20px;">
							<!-- Title -->
							<h5 class="card-title" style="margin-bottom: 10px;">
								<a href="<?php echo SP_WEBPATH . "/blog.php?id=" . $blogInfo['id']?>" style="color: #2c3e50; text-decoration: none; font-weight: 600; line-height: 1.4;">
									<?php echo $blogInfo['blog_title']?>
								</a>
							</h5>

							<!-- Meta Info -->
							<p class="text-muted" style="font-size: 13px; margin-bottom: 15px;">
								<i class="fa fa-calendar"></i> <?php echo date('F d, Y', $publishedTime);?>
								<span style="margin: 0 5px;">•</span>
								<i class="fa fa-user"></i> Admin
							</p>

							<!-- Excerpt -->
							<p class="card-text" style="color: #555; line-height: 1.6; margin-bottom: 15px;">
								<?php echo $excerpt?>
							</p>

							<!-- Tags -->
							<?php
							$tagList = explode(",", $blogInfo['tags']);
							if (!empty($blogInfo['tags'])) {
								?>
								<div style="margin-bottom: 15px;">
									<?php
									foreach ($tagList as $tag) {
										$tag = trim($tag);
										if (!empty($tag)) {
											?>
											<a href="<?php echo SP_WEBPATH . "/blog.php?tag=". urlencode($tag);?>"
											   style="display: inline-block; padding: 4px 12px; background: #f0f0f0; border-radius: 20px; font-size: 12px; color: #666; text-decoration: none; margin-right: 5px; margin-bottom: 5px; transition: background 0.2s;">
												<i class="fa fa-tag"></i> <?php echo $tag;?>
											</a>
											<?php
										}
									}
									?>
								</div>
							<?php } ?>

							<!-- Read More Button -->
							<a href="<?php echo SP_WEBPATH . "/blog.php?id=" . $blogInfo['id']?>" class="btn btn-primary btn-sm" style="border-radius: 20px; padding: 8px 20px;">
								Read More <i class="fa fa-arrow-right"></i>
							</a>
						</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>

		<!-- Pagination -->
		<div class="row mt-4">
			<div class="col-12">
				<nav aria-label="Blog pagination">
					<ul class="pagination justify-content-center">
						<?php if (!empty($newerPage)) { ?>
							<li class="page-item">
								<a class="page-link" href="<?php echo $blogBaseLink . "&page=$newerPage";?>">
									<i class="fa fa-chevron-left"></i> <?php echo $spBlogText['Newer Posts']?>
								</a>
							</li>
						<?php } ?>

						<?php if (!empty($olderPage)) { ?>
							<li class="page-item">
								<a class="page-link" href="<?php echo $blogBaseLink . "&page=$olderPage";?>">
									<?php echo $spBlogText['Older Posts']?> <i class="fa fa-chevron-right"></i>
								</a>
							</li>
						<?php } ?>
					</ul>
				</nav>
			</div>
		</div>
		<?php
	} else {
		?>
		<!-- No Results -->
		<div class="text-center" style="padding: 60px 20px;">
			<i class="fa fa-search" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
			<h4 style="color: #555; margin-bottom: 10px;"><?php echo $spBlogText['Nothing Found']?></h4>
			<p style="color: #999;"><?php echo $spBlogText['NothingFound_text2']?></p>
		</div>
	<?php } ?>
</div>

<style>
/* Hover effects */
.card:hover {
	transform: translateY(-5px);
	box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.card-title a:hover {
	color: #007bff !important;
}

.card-body a[href*="tag"]:hover {
	background: #007bff !important;
	color: white !important;
}

/* Responsive */
@media (max-width: 768px) {
	.col-sm-6, .col-md-4, .col-lg-3 {
		margin-bottom: 20px;
	}

	.blog_search .col-md-8 {
		padding: 0 15px;
	}
}

@media (min-width: 1200px) {
	/* 4 columns on large screens */
	.col-lg-3 {
		flex: 0 0 25%;
		max-width: 25%;
	}
}

@media (min-width: 992px) and (max-width: 1199px) {
	/* 3 columns on medium screens */
	.col-md-4 {
		flex: 0 0 33.333333%;
		max-width: 33.333333%;
	}
}

@media (min-width: 576px) and (max-width: 991px) {
	/* 2 columns on tablets */
	.col-sm-6 {
		flex: 0 0 50%;
		max-width: 50%;
	}
}
</style>
