<style>
.aiv-card {
	background: #fff;
	border: 1px solid rgba(0,0,0,0.06);
	border-radius: 14px;
	padding: 26px 28px;
	margin-bottom: 24px;
	box-shadow: 0 2px 14px rgba(20,20,43,0.06);
}
.aiv-card-header {
	display: flex;
	align-items: center;
	gap: 14px;
	margin-bottom: 16px;
}
.aiv-card-icon {
	flex: 0 0 auto;
	width: 40px;
	height: 40px;
	border-radius: 11px;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 16px;
	box-shadow: 0 3px 8px rgba(102,126,234,0.35);
}
.aiv-card-title {
	font-size: 16px;
	font-weight: 700;
	color: #24243a;
	line-height: 1.3;
}
.aiv-card-subtitle {
	font-size: 13px;
	color: #8a8ea3;
	margin-top: 2px;
	font-weight: 400;
}
.aiv-card-body > p:last-child { margin-bottom: 0; }

/* "Discover More" feature callouts - was 5 separate, visually identical
   .aiv-note strips (thin left border, 13px text, bare icon) easy to skim
   right past. One card instead, each item with its own colored icon
   badge and a proper button CTA instead of an inline text link, bigger
   text - reads as a set of things to try, not background noise. */
.aiv-discover-item {
	display: flex;
	align-items: center;
	gap: 14px;
	padding: 14px 0;
	border-bottom: 1px solid #eef0f5;
}
.aiv-discover-item:last-child { border-bottom: none; padding-bottom: 0; }
.aiv-discover-item:first-child { padding-top: 0; }
.aiv-discover-icon {
	flex: 0 0 auto;
	width: 36px;
	height: 36px;
	border-radius: 10px;
	color: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 15px;
	box-shadow: 0 2px 6px rgba(0,0,0,0.12);
}
.aiv-discover-text {
	flex: 1;
	font-size: 14.5px;
	color: #3a3d4d;
	line-height: 1.4;
}
.aiv-discover-text small {
	display: block;
	font-size: 12px;
	color: #8a8ea3;
	font-weight: 400;
	margin-top: 1px;
}
.aiv-discover-cta {
	flex: 0 0 auto;
	display: inline-flex;
	align-items: center;
	gap: 6px;
	background: #f4f5fb;
	color: #4a4fd6;
	font-size: 13px;
	font-weight: 700;
	padding: 8px 14px;
	border-radius: 8px;
	text-decoration: none;
	white-space: nowrap;
	transition: background 0.15s ease;
}
.aiv-discover-cta:hover { background: #e7e9fa; color: #3a3ecc; text-decoration: none; }
@media (max-width: 600px) {
	.aiv-discover-item { flex-wrap: wrap; }
	.aiv-discover-cta { margin-left: 50px; }
}

.aiv-note {
	background: #f4f5fb;
	border: none;
	border-left: 3px solid #8890e0;
	border-radius: 8px;
	padding: 12px 16px;
	font-size: 13px;
	color: #565a72;
	margin: 14px 0;
	display: flex;
	align-items: flex-start;
	gap: 10px;
}
.aiv-note i { color: #7178d6; margin-top: 2px; }
.aiv-note.aiv-note-warn { border-left-color: #e0a23b; background: #fdf7ec; color: #7a5a13; }
.aiv-note.aiv-note-warn i { color: #cf8f1e; }
.aiv-note.aiv-note-danger { border-left-color: #e05353; background: #fdeeee; color: #7a1f1f; }
.aiv-note.aiv-note-danger i { color: #d64545; }

.aiv-code-box {
	background: #191a29;
	border-radius: 10px;
	padding: 16px 18px;
	position: relative;
	margin: 14px 0;
}
.aiv-code-box code, .aiv-code-box pre {
	color: #a9c7ff;
	font-size: 12.5px;
	font-family: ui-monospace, "SF Mono", Consolas, monospace;
	display: block;
	margin: 0;
	background: none;
	border: none;
	padding: 0;
	padding-right: 96px;
	overflow-x: auto;
	white-space: pre;
}
.aiv-code-box.aiv-code-multiline code,
.aiv-code-box.aiv-code-multiline pre {
	white-space: pre-wrap;
	word-break: break-word;
	padding-right: 0;
	max-height: 220px;
	overflow-y: auto;
}
.aiv-code-copy {
	position: absolute;
	top: 12px;
	right: 12px;
}

.aiv-btn {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	border: none;
	border-radius: 8px;
	padding: 9px 18px;
	font-size: 13px;
	font-weight: 600;
	cursor: pointer;
	text-decoration: none;
	transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.12s ease;
}
.aiv-btn:disabled, .aiv-btn.disabled { opacity: 0.5; cursor: not-allowed; }
.aiv-btn-primary {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: #fff;
	box-shadow: 0 3px 10px rgba(102,126,234,0.35);
}
.aiv-btn-primary:hover:not(:disabled):not(.disabled) { transform: translateY(-1px); box-shadow: 0 5px 14px rgba(102,126,234,0.45); color: #fff; }
.aiv-btn-outline {
	background: #fff;
	color: #565a72;
	border: 1px solid #dcdeea;
}
.aiv-btn-outline:hover { background: #f4f5fb; color: #33364a; }
.aiv-btn-danger {
	background: #e05353;
	color: #fff;
}
.aiv-btn-danger:hover { background: #cc4444; color: #fff; }

.aiv-status-line {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 13px;
	color: #8a8ea3;
	margin-top: 14px;
}
.aiv-status-line.is-ready { color: #1f9d55; }
.aiv-status-line .fa-spinner { color: #7178d6; }

.aiv-field-label {
	font-weight: 600;
	font-size: 13px;
	color: #33364a;
	margin-bottom: 6px;
	display: block;
}
.aiv-field { margin-bottom: 18px; }
.aiv-field:last-child { margin-bottom: 0; }
.aiv-field input[type="text"] {
	border: 1px solid #dcdeea;
	border-radius: 8px;
	padding: 9px 12px;
	font-size: 13px;
	width: 100%;
	max-width: 460px;
	transition: border-color 0.15s ease;
}
.aiv-field input[type="text"]:focus {
	border-color: #8890e0;
	outline: none;
	box-shadow: 0 0 0 3px rgba(102,126,234,0.12);
}
.aiv-field-hint { font-size: 12px; color: #9296a8; margin-top: 5px; }
.aiv-field-status { font-size: 12px; margin-top: 5px; display: flex; align-items: center; gap: 5px; }
.aiv-field-status.ok { color: #1f9d55; }
.aiv-field-status.warn { color: #b8790a; }
.aiv-field-status.err { color: #c0392b; }

.aiv-table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 14px 0; }
.aiv-table th {
	text-align: left;
	padding: 10px 14px;
	color: #8a8ea3;
	font-weight: 600;
	text-transform: uppercase;
	font-size: 11px;
	letter-spacing: 0.03em;
	border-bottom: 1px solid #eceef5;
	background: #fafafe;
}
.aiv-table td {
	padding: 12px 14px;
	border-bottom: 1px solid #f2f3f8;
	vertical-align: middle;
}
.aiv-table tr:last-child td { border-bottom: none; }
.aiv-table tr:hover td { background: #fafbff; }
.aiv-table td.aiv-num, .aiv-table th.aiv-num { text-align: right; }

/* status badge pills - replaces plain colored text (.text-success/
   .text-muted) in table cells with a proper pill, same visual language
   as the rest of the AI Visibility section's icon badges/CTAs. */
.aiv-pill {
	display: inline-block;
	padding: 3px 10px;
	border-radius: 20px;
	font-size: 11.5px;
	font-weight: 700;
}
.aiv-pill-success { background: #e6f7ee; color: #1f9d55; }
.aiv-pill-muted { background: #f1f2f6; color: #8a8ea3; }
.aiv-pill-warn { background: #fdf2da; color: #a9700f; margin-left: 4px; }

/* 3-way sub-nav shared by ai-perception's settings/check/tracking.ctp.php
   (Setup/Ask/Scheduled Tracking) - was plain Bootstrap btn-outline-
   primary/btn-primary, same icon-pill treatment as the Schema
   Generator's type selector for visual consistency across AI Visibility. */
.aiv-subnav-pill {
	display: inline-flex; align-items: center; gap: 7px;
	padding: 8px 16px; border-radius: 9px;
	font-size: 13.5px; font-weight: 600; text-decoration: none;
	border: 1px solid #d8dee8; background: #fff; color: #4a4e69;
	transition: all 0.15s ease;
}
.aiv-subnav-pill:hover { border-color: #4a8fc7; color: #4a8fc7; text-decoration: none; }
.aiv-subnav-pill.aiv-subnav-active {
	background: linear-gradient(135deg, #4a8fc7 0%, #6faee0 100%);
	border-color: #4a8fc7; color: #fff;
	box-shadow: 0 2px 6px rgba(74, 143, 199, 0.35); cursor: default;
}

/* pure-CSS toggle switch, replacing raw checkboxes */
.aiv-switch { position: relative; display: inline-block; width: 40px; height: 22px; flex: 0 0 auto; }
.aiv-switch input { opacity: 0; width: 0; height: 0; }
.aiv-switch-track {
	position: absolute; cursor: pointer; inset: 0;
	background: #d7d9e6; border-radius: 22px; transition: background 0.15s ease;
}
.aiv-switch-track::before {
	content: ""; position: absolute; height: 16px; width: 16px; left: 3px; top: 3px;
	background: #fff; border-radius: 50%; transition: transform 0.15s ease;
	box-shadow: 0 1px 3px rgba(0,0,0,0.25);
}
.aiv-switch input:checked + .aiv-switch-track { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.aiv-switch input:checked + .aiv-switch-track::before { transform: translateX(18px); }
.aiv-switch input:disabled + .aiv-switch-track { opacity: 0.45; cursor: not-allowed; }
.aiv-switch-row { display: flex; align-items: center; gap: 10px; }

.aiv-chip-group { display: flex; flex-wrap: wrap; gap: 8px 18px; margin: 14px 0; }
.aiv-chip { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #33364a; }
.aiv-chip input[type="checkbox"] { width: 15px; height: 15px; accent-color: #667eea; }

.aiv-divider { border: none; border-top: 1px solid #eceef5; margin: 22px 0; }

.aiv-badge-soft { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
.aiv-badge-soft.success { background: #e7f8ee; color: #1f9d55; }
.aiv-badge-soft.danger { background: #fdeeee; color: #c0392b; }
.aiv-badge-soft.neutral { background: #eceef5; color: #666a80; }

/* Setup vs Advanced tabs - client-side only (no navigation/reload), so
   the active tab is preserved in localStorage and restored by the inline
   script below, since every action on this page (toggling a rule, saving
   a form) reloads this whole view via AJAX and would otherwise always
   snap back to the first tab. */
.aiv-tabs {
	display: flex;
	gap: 6px;
	margin-bottom: 20px;
	border-bottom: 1px solid #eceef5;
}
.aiv-tab-btn {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	background: none;
	border: none;
	cursor: pointer;
	padding: 12px 6px;
	margin-bottom: -1px;
	font-size: 14px;
	font-weight: 600;
	color: #8a8ea3;
	border-bottom: 2px solid transparent;
	transition: color 0.15s ease, border-color 0.15s ease;
}
.aiv-tab-btn:hover { color: #565a72; }
.aiv-tab-btn.active { color: #5c4fd6; border-bottom-color: #764ba2; }
.aiv-tab-btn .aiv-tab-btn-badge {
	display: inline-flex;
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: #e05353;
}
.aiv-tab-panel[hidden] { display: none; }

/* Stat-tile grids (score/summary numbers across overview.ctp.php and
   aioverview.ctp.php) - was a hardcoded inline grid-template-columns:
   repeat(3 or 4, 1fr) with no mobile handling at all, squeezing 3-4
   tiles with 30px bold numbers into ~80-100px-wide columns on a phone.
   Collapses to 2 columns under 768px - the same 2-per-row treatment
   already used for the dashboard's own stat tiles elsewhere. */
.aiv-stat-grid { display: grid; gap: 20px; margin-bottom: 24px; }
.aiv-stat-grid-3 { grid-template-columns: repeat(3, 1fr); }
.aiv-stat-grid-4 { grid-template-columns: repeat(4, 1fr); }

@media screen and (max-width: 767px) {
	.aiv-card { padding: 18px 16px; }
	.aiv-stat-grid-3,
	.aiv-stat-grid-4 {
		grid-template-columns: repeat(2, 1fr);
		gap: 10px;
	}
}

/* Help-modal hero header - replaces the plain Bootstrap modal-header
   (bare title + bare "x") with a gradient banner and a circular icon
   badge, so the popup reads as a considered intro, not a bare dialog. */
.aiv-help-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; }
.aiv-help-modal-header {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	padding: 32px 28px 24px;
	text-align: center;
	position: relative;
}
.aiv-help-modal-header .close {
	position: absolute; top: 14px; right: 16px;
	color: #fff; opacity: 0.8; text-shadow: none; font-size: 24px; font-weight: 400;
}
.aiv-help-modal-header .close:hover { opacity: 1; }
.aiv-help-modal-icon {
	width: 56px; height: 56px; border-radius: 50%;
	background: rgba(255,255,255,0.18);
	display: flex; align-items: center; justify-content: center;
	margin: 0 auto 14px;
	font-size: 22px; color: #fff;
}
.aiv-help-modal-header .modal-title { font-size: 18px; font-weight: 700; color: #fff; margin: 0; }
.aiv-help-modal-body { padding: 26px 28px 8px; }
.aiv-help-modal-body p { font-size: 13.5px; color: #4a4e69; line-height: 1.6; }
.aiv-help-modal-body p:first-of-type { font-size: 14.5px; color: #2a2a3d; }
.aiv-help-modal-body .aiv-discover-item:last-of-type { border-bottom: none; }
.aiv-help-modal .modal-footer { border-top: none; padding: 4px 28px 26px; justify-content: center; }
.aiv-help-modal .modal-footer .btn {
	border-radius: 20px; padding: 8px 28px; font-weight: 600; font-size: 13px;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: #fff;
}
.aiv-help-modal .modal-footer .btn:hover { opacity: 0.92; color: #fff; }

/* Quick-info intro card - the always-visible "what is this tool" banner
   at the top of every AI tool page. Replaces the earlier pairing of a
   small "What does this do?" link up in the title row plus a thin,
   easy-to-miss .aiv-note strip below the sub-nav with one single,
   higher-visual-weight banner that IS the page's one obvious place for
   this, with its own "Learn more" CTA opening the same help modal. */
.aiv-intro-card {
	display: flex; align-items: center; gap: 16px;
	background: linear-gradient(135deg, rgba(102,126,234,0.08) 0%, rgba(118,75,162,0.08) 100%);
	border: 1px solid rgba(102,126,234,0.2);
	border-radius: 14px;
	padding: 16px 44px 16px 20px;
	margin-bottom: 20px;
	flex-wrap: wrap;
	position: relative;
}
.aiv-intro-close {
	position: absolute; top: 12px; right: 12px;
	display: flex; align-items: center; justify-content: center;
	width: 24px; height: 24px;
	background: rgba(255,255,255,0.75); border: 1px solid rgba(102,126,234,0.22);
	border-radius: 50%;
	color: #8a8ea3; font-size: 10.5px; line-height: 1; cursor: pointer;
	box-shadow: 0 1px 3px rgba(20,20,43,0.07);
	transition: all 0.15s ease;
}
.aiv-intro-close:hover {
	background: #5c4fd6; border-color: #5c4fd6; color: #fff;
	box-shadow: 0 3px 10px rgba(92,79,214,0.35);
	transform: scale(1.08);
}
.aiv-intro-close:active { transform: scale(0.94); }
.aiv-intro-icon {
	flex: 0 0 auto;
	width: 46px; height: 46px; border-radius: 13px;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: #fff; display: flex; align-items: center; justify-content: center;
	font-size: 19px;
	box-shadow: 0 4px 12px rgba(102,126,234,0.32);
}
.aiv-intro-text { flex: 1; min-width: 220px; }
.aiv-intro-title { font-size: 14.5px; font-weight: 700; color: #2a2a3d; margin-bottom: 3px; }
.aiv-intro-desc { font-size: 12.5px; color: #5a5e78; line-height: 1.5; }
.aiv-intro-cta {
	flex: 0 0 auto;
	display: inline-flex; align-items: center; gap: 6px;
	background: #fff; color: #5c4fd6;
	border: 1px solid rgba(102,126,234,0.35);
	padding: 8px 18px; border-radius: 20px;
	font-size: 12.5px; font-weight: 700; text-decoration: none;
	transition: all 0.15s ease; cursor: pointer;
}
.aiv-intro-cta:hover { background: #5c4fd6; color: #fff; border-color: #5c4fd6; text-decoration: none; }
@media (max-width: 600px) {
	.aiv-intro-card { padding: 14px 40px 14px 16px; }
	.aiv-intro-cta { width: 100%; justify-content: center; margin-top: 4px; }
}
</style>

<script>
// Dismiss for the "quick-info" intro card at the top of each AI tool
// page - cookie-based (not localStorage) per the project's own choice,
// so it naturally reappears once the cookie expires instead of staying
// hidden forever. Keyed by the page's help-modal id, so dismissing the
// (identical) card on any of the 3 AI Perception pages hides it on all
// 3, while every other AI tool page's card is dismissed independently.
function spDismissIntro(id, btn) {
	var card = btn.closest('.aiv-intro-card');
	if (card) card.style.display = 'none';
	createCookie('sp_intro_dismissed_' + id, '1', 14);
}
document.querySelectorAll('.aiv-intro-card[data-intro-id]').forEach(function(card) {
	if (readCookie('sp_intro_dismissed_' + card.getAttribute('data-intro-id')) === '1') {
		card.style.display = 'none';
	}
});
</script>
