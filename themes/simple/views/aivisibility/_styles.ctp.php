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
</style>
