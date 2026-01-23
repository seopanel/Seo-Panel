<?php if(empty($_COOKIE['hidenews']) && !SP_HOSTED_VERSION && empty($custSiteInfo['disable_news'])){ ?>
	<style>
		.news-banner {
			background: linear-gradient(135deg, #ffecb3 0%, #ffe082 100%);
			border-radius: 6px;
			padding: 8px 16px;
			margin: 8px 15px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
			display: flex;
			align-items: center;
			justify-content: space-between;
			position: relative;
			overflow: hidden;
			border: 1px solid #ffd54f;
		}
		.news-banner::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			bottom: 0;
			width: 4px;
			background: linear-gradient(180deg, #ff9800, #f57c00);
			border-radius: 8px 0 0 8px;
		}
		.news-banner-content {
			display: flex;
			align-items: center;
			flex: 1;
		}
		.news-banner-icon {
			background: linear-gradient(135deg, #ff9800, #f57c00);
			border-radius: 50%;
			width: 32px;
			height: 32px;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-right: 12px;
			flex-shrink: 0;
			box-shadow: 0 2px 6px rgba(255, 152, 0, 0.3);
		}
		.news-banner-icon svg {
			width: 16px;
			height: 16px;
			fill: #ffffff;
		}
		.news-banner #newsalert {
			color: #5d4037;
			font-size: 15px;
			line-height: 1.4;
		}
		.news-banner #newsalert a {
			color: #5d4037;
			text-decoration: none;
			transition: all 0.3s ease;
		}
		.news-banner #newsalert a:hover {
			color: #e65100;
		}
		.news-banner #newsalert b {
			font-weight: 600;
			color: #3e2723;
		}
		.news-banner #newsalert b[style*="color:red"],
		.news-banner #newsalert b[style*="color: red"] {
			color: #ffffff !important;
			background: linear-gradient(135deg, #e53935, #c62828);
			padding: 5px 8px;
			border-radius: 3px;
			font-size: 15px;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			box-shadow: 0 2px 4px rgba(229, 57, 53, 0.3);
		}
		.news-banner #newsalert b[style*="color:green"],
		.news-banner #newsalert b[style*="color: green"] {
			color: #2e7d32 !important;
			font-size: 16px;
			font-weight: 700;
		}
		.news-banner-close {
			background: rgba(0, 0, 0, 0.2);
			border: none;
			color: #5d4037;
			width: 24px;
			height: 24px;
			border-radius: 50%;
			cursor: pointer;
			display: flex;
			align-items: center;
			justify-content: center;
			transition: all 0.3s ease;
			margin-left: 12px;
			flex-shrink: 0;
		}
		.news-banner-close:hover {
			background: rgba(0, 0, 0, 0.35);
			color: #3e2723;
			transform: rotate(90deg);
		}
		.news-banner-close svg {
			width: 12px;
			height: 12px;
			fill: currentColor;
		}
	</style>
	<div class="row-fluid" style="width: 100%;">
		<div class="news-banner alert alert-dismissible fade show" role="alert" id="myAlert">
			<div class="news-banner-content">
				<div class="news-banner-icon">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12zM11 5h2v6h-2zm0 8h2v2h-2z"/>
					</svg>
				</div>
				<span id="newsalert"></span>
			</div>
			<button type="button" class="news-banner-close" data-dismiss="alert" aria-label="Close">
				<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
				</svg>
			</button>
		</div>
	</div>
	<script>
	    scriptDoLoad('<?php echo SP_WEBPATH?>/index.php?sec=news', 'newsalert');
	    $('#myAlert').on('closed.bs.alert', function () {
	    	hideNewsBox('newsalert', 'hidenews', '1')
	    });
	</script>
<?php }?>