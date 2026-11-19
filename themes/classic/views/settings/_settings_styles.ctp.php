<style>
/* Settings pages redesign - replaces the old plain <table class="list">
   key/value rows (System/API/MOZ/Google/DataForSEO/Local AI/Mail/Seo
   Panel API/Site Auditor/Proxy/Report settings) with the same card +
   bordered-row language already established for AI Visibility
   (.aiv-card, see aivisibility/_styles.ctp.php, included alongside this
   file on every page that uses these classes). Scoped to its own
   sp-settings-* classes rather than touching .list/.td_left_col/
   .td_right_col, which are shared by 30+ unrelated pages across the app. */
.sp-settings-row {
	display: flex;
	align-items: flex-start;
	gap: 24px;
	padding: 18px 0;
	border-bottom: 1px solid #eef0f5;
}
.sp-settings-row:first-child { padding-top: 4px; }
.sp-settings-row:last-child { border-bottom: none; padding-bottom: 4px; }
.sp-settings-row-label {
	flex: 0 0 260px;
	font-size: 13.5px;
	font-weight: 600;
	color: #33364a;
	padding-top: 8px;
}
.sp-settings-row-label small {
	display: block;
	font-size: 12px;
	font-weight: 400;
	color: #9296a8;
	margin-top: 3px;
	line-height: 1.4;
}
.sp-settings-row-control { flex: 1; min-width: 0; }
.sp-settings-row-control .form-control,
.sp-settings-row-control select.custom-select,
.sp-settings-row-control textarea {
	border: 1px solid #dcdeea !important;
	border-radius: 8px !important;
	transition: border-color .15s ease, box-shadow .15s ease;
}
.sp-settings-row-control .form-control:focus,
.sp-settings-row-control select.custom-select:focus,
.sp-settings-row-control textarea:focus {
	border-color: #8890e0 !important;
	outline: none;
	box-shadow: 0 0 0 3px rgba(102,126,234,0.12);
}
.sp-settings-row-control #show_conn_res { margin-top: 10px; }

@media screen and (max-width: 700px) {
	.sp-settings-row { flex-direction: column; gap: 6px; padding: 14px 0; }
	.sp-settings-row-label { flex: none; padding-top: 0; }
}
</style>
