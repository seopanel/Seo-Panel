<head>
	<meta content="text/html; charset=UTF-8" http-equiv="content-type" />
    <style>
    	.search {margin-top: 5px;}
        .search A {text-decoration: none;background-color: white;}
        .search a:focus {outline: none;}
        .search th {text-align: right; padding-right: 5px; padding-bottom: 5px; font-size: 11px;}
        .search td {text-align: left; padding-left: 5px; padding-bottom: 5px;}
        .list {	margin-top: 10px;}        
        .listHead {background-color: #DAE5EE;border-bottom: 1px solid #B0C2CC;}        
        .listHead td {text-align: center;  height: 25px; font-weight: bold; color: #343333; font-family: Tahoma, verdana, arial, sans-serif;
    	font-size: 11px; margin: 0; padding: 0 3px 0 4px; vertical-align: middle;border-right: 1px solid #B0C2CC;border-bottom: 1px solid #B0C2CC;border-top: 1px solid #B0C2CC;}        
        .listHead td.left {border-left: 1px solid #B0C2CC;}		
		#sortLink {color: black; text-decoration: none;}
		#sortLink:HOVER { text-decoration: underline;background-color: #DBE6EE;}
		.sort_asc{background:transparent url(<?php echo SP_IMGPATH?>/sort_asc.gif) no-repeat scroll right center;padding-right: 18px;}
		.sort_desc{background:transparent url(<?php echo SP_IMGPATH?>/sort_desc.gif) no-repeat scroll right center;padding-right: 18px;}
		.blue_row {background-color: #F4F7FA;}
		.white_row {background-color: #FFFFFF;}
        .blue_row td,.white_row td {text-align: center; color: #343333; font-family: Tahoma, verdana, arial, sans-serif; font-size: 11px;
    	margin: 0; padding: 5px 3px 5px 4px; height: 25px;}
        .blue_row td.left,.white_row td.left{text-align: left; padding-left: 10px;}
        .td_border {border-left: 1px #B0C2CC solid; border-bottom: 1px #B0C2CC solid;}
        .td_br_right {border-right: 1px #B0C2CC solid; border-bottom: 1px #B0C2CC solid;}
        .td_left_border {border-left: 1px #B0C2CC solid;}
        .td_bottom_border {border-bottom: 1px #B0C2CC solid;}
        .blue_row .td_left_col,.white_row .td_left_col {border-left: 1px #B0C2CC solid; border-right: 1px #B0C2CC solid; border-bottom: 1px #B0C2CC solid;
    	text-align: right; padding-right: 6px;}
        .blue_row .td_right_col,.white_row .td_right_col {border-right: 1px #B0C2CC solid; border-bottom: 1px #B0C2CC solid; text-align: left; padding-left: 6px;}
        .tab_left_bot {border-right: 1px #B0C2CC solid;border-left: 1px #B0C2CC solid;border-bottom: 1px #B0C2CC solid;}
        .tab_right_bot {border-right: 1px #B0C2CC solid;border-bottom: 1px #B0C2CC solid;}
        .summary{border: 1px solid #B0C2CC;}
		.summary td{height: 30px;}
		.summary .topheader {background-color: #dae5ee; color: #000000; font-size: 13px; margin: 0; padding: 1px 1px 1px 1px; font-weight: bold;
		border: none; text-align: center;}
        .summary .subheader {background-color: #ECF3FA; color: #000000; font-size: 11px; margin: 0; padding: 1px 1px 1px 5px; font-weight: bold;
    	border-left: 1px solid #B0C2CC; text-align: center;}
        .summary .subheaderdark {background-color: #BFD5E7; color: #000000; font-size: 11px; margin: 0; padding: 1px 1px 1px 5px; font-weight: bold;
    	border-left: 1px solid #B0C2CC; text-align: center;}
        .summary .content {color: #000000; font-size: 11px; margin: 0; padding: 1px 1px 1px 5px; text-align: left; border-left: 1px solid #B0C2CC; border-top: 1px solid #B0C2CC;}
        .summary .contentmid {color: #000000; font-size: 11px; margin: 0; padding: 1px 1px 1px 5px; text-align: center; border-left: 1px solid #B0C2CC; border-top: 1px solid #B0C2CC;}
        .summary .norecord{ color: #000000; font-size: 11px; margin: 0; padding: 1px 1px 1px 1px; text-align: center; border-top: 1px solid #B0C2CC;}
		.summary A { text-decoration: none;}
      	td {font-family: "Lucida Grande",verdana,arial,helvetica,sans-serif;}
        .green {color: #00903B;}
        .red {color: #990000;}
        a:visited {color: #0033CC;}

        /* bug fix: the periodic report email's Keyword Position/Website
           Analytics/Review Reports sections (report/archive.ctp.php,
           analytics/analytics_summary.ctp.php, review/
           review_report_summary.ctp.php) share markup with the
           interactive admin page and PDF export, all using id="cust_tab"/
           id="head" - styled in the main app via screen.css, which an
           email never loads (only this file's own <style> block, or
           inline attributes, ever apply in an email client). Those
           tables rendered as bare, unstyled HTML in every report email
           until now. nth-child striping is skipped by clients that don't
           support it (older Outlook desktop) - it just falls back to
           plain white rows there, same as before this fix, rather than
           breaking anything. */
        #cust_tab {border-collapse: collapse; width: 100%; margin-top: 8px; font-family: Tahoma, verdana, arial, sans-serif;}
        #cust_tab th, #cust_tab td {border: 1px solid #B0C2CC; padding: 6px 8px; font-size: 11px;}
        #cust_tab th, th#head {background-color: #DAE5EE; color: #343333; font-weight: bold; text-align: center;}
        #cust_tab td {color: #343333; text-align: center;}
        #cust_tab tr:nth-child(even) td {background-color: #F4F7FA;}
        .SectionHeader {margin: 14px 0 4px;}
        .SectionHeader h3 {font-size: 14px; font-weight: bold; color: #2d3748; font-family: Tahoma, verdana, arial, sans-serif; margin: 0;}

        /* bug fix: report/archive.ctp.php and analytics_summary.ctp.php
           wrap their (often wide, multi-search-engine) #cust_tab tables
           in <div class="table-responsive"> expecting Bootstrap's own
           "overflow-x:auto" rule for that class - Bootstrap's CSS is
           never loaded in an email, so it silently did nothing and a
           report with 2+ search engines/metrics just overflowed past the
           email's width with no way to see the cut-off columns. Same
           rule Bootstrap itself uses. */
        .table-responsive {overflow-x: auto; width: 100%;}
    </style>
</head>