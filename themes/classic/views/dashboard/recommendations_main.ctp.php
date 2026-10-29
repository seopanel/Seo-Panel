<?php if (!empty($noWebsites)) {
    include(SP_VIEWPATH.'/dashboard/no_websites.ctp.php');
} else {

    $grouped = array('error' => array(), 'warning' => array(), 'todo' => array());
    foreach ($recommendations as $rec) {
        $grouped[$rec['type']][] = $rec;
    }
    $totalCount = count($recommendations);

    $typeMeta = array(
        'error'   => array('class' => 'error',   'icon' => 'fa-times-circle',         'label' => 'Errors'),
        'warning' => array('class' => 'warning', 'icon' => 'fa-exclamation-triangle', 'label' => 'Warnings'),
        'todo'    => array('class' => 'todo',    'icon' => 'fa-tasks',                'label' => 'To-Do'),
    );
    ?>
<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>

<style>
.rec-container { width: 100%; }
.rec-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 14px 24px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.rec-banner.rec-clear { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.rec-banner-icon { font-size: 28px; opacity: 0.9; }
.rec-banner-text h2 { margin: 0 0 2px 0; font-size: 17px; font-weight: 600; }
.rec-banner-text p { margin: 0; opacity: 0.92; font-size: 12px; }
.rec-controls {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-left: 4px solid #667eea;
    border-radius: 10px;
    padding: 16px 22px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    box-shadow: 0 2px 10px rgba(102, 126, 234, 0.08);
}
.rec-site-badge {
    width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 13px;
    box-shadow: 0 2px 6px rgba(102, 126, 234, 0.35);
}
.rec-controls label { font-weight: 600; color: #444; font-size: 13px; margin: 0; }
.rec-controls select.custom-select { min-width: 220px; }
.rec-btn {
    background: #667eea;
    color: white;
    border: none;
    padding: 9px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.rec-btn:hover { background: #5a6fd8; color: white; }
.rec-last-updated { color: #888; font-size: 12px; margin-left: auto; }
.rec-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.rec-card-header {
    padding: 16px 22px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 600;
}
.rec-card-header.error   { background: #fdeaea; color: #c0392b; }
.rec-card-header.warning { background: #fef6e6; color: #b8790a; }
.rec-card-header.todo    { background: #eaf2fd; color: #2f6fce; }
.rec-count-badge {
    background: rgba(255,255,255,0.7);
    border-radius: 12px;
    padding: 2px 11px;
    font-size: 12px;
}
.rec-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.rec-table th {
    text-align: left;
    padding: 10px 22px;
    color: #999;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    border-bottom: 1px solid #eee;
    background: #fafafa;
}
.rec-table th.rec-num { text-align: right; }
.rec-table td {
    padding: 14px 22px;
    border-bottom: 1px solid #f2f2f2;
    vertical-align: top;
}
.rec-table td.rec-num { text-align: right; white-space: nowrap; }
.rec-table tr:last-child td { border-bottom: none; }
.rec-table tr:hover td { background: #fafbff; }
.rec-title { font-weight: 600; color: #333; }
.rec-desc { color: #777; font-size: 12px; margin-top: 4px; line-height: 1.5; }
.rec-stats { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 6px; }
.rec-stat { font-size: 12px; color: #555; }
.rec-stat b { color: #333; }
.rec-empty {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    color: #888;
}
.rec-empty i { font-size: 28px; color: #667eea; display: block; margin-bottom: 10px; }
.rec-empty.rec-clear { border-color: #38ef7d; }
.rec-empty.rec-clear i { color: #11998e; }
.rec-empty.rec-error-banner { border-color: #e74c3c; color: #c0392b; }
.rec-empty.rec-error-banner i { color: #e74c3c; }
</style>

<div class="rec-container">

    <!-- same dual-context situation as the Website selector/score cards
         below - no Dashboard tab has its own page title (the tab bar
         itself provides that context), but this same view reached via
         Tools -> AI Visibility -> AI Insights had no heading at all,
         unlike every other standalone AI Visibility page. Visible by
         default (standalone context), hidden on the Dashboard tab. -->
    <div id="recSectionHeadWrap" class="aiv-page-head">
        <?php echo showSectionHead($spTextTools['AI Insights'] ?? 'AI Insights'); ?>
        <a href="javascript:void(0);" data-toggle="modal" data-target="#recHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> <?php echo $spTextAIV['What does this do?'] ?? 'What does this do?'?></a>
    </div>
    <script>
    if (document.getElementById('sp_shared_filter_bar')) {
        document.getElementById('recSectionHeadWrap').style.display = 'none';
    }
    </script>

    <!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
    <div class="modal fade" id="recHelpModal" tabindex="-1" role="dialog" aria-labelledby="recHelpModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="recHelpModalLabel"><i class="fas fa-lightbulb"></i> <?php echo $spTextAIV['About AI Insights'] ?? 'About AI Insights'?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body aiv-help-modal-body">
                    <p><?php echo $spTextAIV['A prioritized to-do list for improving this site\'s AI visibility - errors, warnings, and to-dos - built from data the other SEO Panel tools already collect (Site Auditor, AI Visibility, Schema Generator, and more).'] ?? 'A prioritized to-do list for improving this site\'s AI visibility - errors, warnings, and to-dos - built from data the other SEO Panel tools already collect (Site Auditor, AI Visibility, Schema Generator, and more).'?></p>
                    <p style="color:#8a8ea3;font-size:12.5px;margin-bottom:0;"><?php echo $spTextAIV['Refreshes automatically once a day, or on demand with the Refresh button. You can also generate an on-demand plain-English summary of the current findings.'] ?? 'Refreshes automatically once a day, or on demand with the Refresh button. You can also generate an on-demand plain-English summary of the current findings.'?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $spText['button']['Close'] ?? 'Close'?></button>
                </div>
            </div>
        </div>
    </div>

    <form id="recommendations_dashboard_form" method="post">
        <div class="rec-controls">
            <!-- Website selection lives in the shared filter bar above
                 #main_dashboard_nav (userhome.ctp.php) when this page is
                 reached as the Dashboard's AI Visibility tab - but this
                 same view is ALSO reached directly from Tools -> AI
                 Visibility -> AI Insights (leftmenu.ctp.php), a different
                 page shell with no shared bar at all, where this would be
                 the only way to switch websites. Visible by default, then
                 hidden by the inline script below only when the shared
                 bar's own container is actually present on the page
                 (confirmed to be the Dashboard-tab context), rather than
                 assuming one context unconditionally. -->
            <span id="recWebsiteSelectWrap">
            <label><?php echo $spText['common']['Website'] ?? 'Website' ?>:</label>
            <select name="website_id" id="website_id" class="custom-select" style="width:auto;max-width:280px;"
                onchange="scriptDoLoadPost('<?php echo SP_WEBPATH?>/recommendations_dashboard.php', 'recommendations_dashboard_form', 'content')">
                <?php foreach ($websiteList as $ws) { ?>
                    <option value="<?php echo $ws['id'] ?>" <?php echo ($ws['id'] == $websiteId) ? 'selected' : '' ?>>
                        <?php echo htmlspecialchars($ws['name']) ?>
                    </option>
                <?php } ?>
            </select>
            <!-- same reasoning as website_id above - the shared bar drives
                 its own reloads via GET, this just keeps the Refresh
                 button's own POST (serializes this whole form) carrying
                 whichever period that last reload actually used, instead
                 of silently dropping back to the default every refresh. -->
            <input type="hidden" name="period" value="<?php echo htmlspecialchars($period ?? 'month')?>">
            </span>
            <script>
            // hide the Website select only when the Dashboard's shared
            // filter bar is actually present (see the comment above) -
            // on the Tools -> AI Insights entry point it stays visible,
            // since nothing else on that page lets you switch websites.
            if (document.getElementById('sp_shared_filter_bar')) {
                document.getElementById('recWebsiteSelectWrap').style.display = 'none';
            }
            </script>

            <?php
            // this row otherwise has nothing saying which site/range the
            // score below is actually for - filled in with what's already
            // computed above (no new query), rather than leaving it blank.
            $recCurrentSite = null;
            foreach ($websiteList as $ws) { if ($ws['id'] == $websiteId) { $recCurrentSite = $ws; break; } }
            if (!empty($recCurrentSite)) { ?>
            <span style="display:flex; align-items:center; gap:12px;">
                <span class="rec-site-badge"><i class="fas fa-globe"></i></span>
                <span style="display:flex; flex-direction:column; line-height:1.3;">
                    <strong style="font-size:15px; color:#2d2d3a;"><?php echo htmlspecialchars($recCurrentSite['name']) ?></strong>
                    <span style="font-size:12px; color:#999;">
                        <?php if (!empty($recCurrentSite['url'])) { ?>
                            <?php echo htmlspecialchars($recCurrentSite['url']) ?> &middot;
                        <?php } ?>
                        last 30 days
                    </span>
                </span>
            </span>
            <?php } ?>

            <?php if (!empty($refreshedAt)) { ?>
            <span class="rec-last-updated" style="margin-left:auto;">Last updated: <?php echo htmlspecialchars($refreshedAt) ?></span>
            <?php } ?>

            <button type="button" class="rec-btn" <?php echo empty($refreshedAt) ? 'style="margin-left:auto;"' : ''?>
                onclick="this.disabled=true; scriptDoLoadPost('<?php echo SP_WEBPATH?>/recommendations_dashboard.php?sec=refresh', 'recommendations_dashboard_form', 'content')">
                <i class="fas fa-sync-alt"></i> Refresh AI Insights
            </button>
        </div>
    </form>

    <!-- The score card + AI Overview Tracking card duplicate what's
         already on the dedicated "AI Visibility Summary" page
         (aivisibility.php?sec=overview) - they belong on the Dashboard's
         "AI Visibility" tab (this same view, different name/context,
         where a score summary fits), not on the "AI Insights" page
         reached via the Tools menu, whose own name promises only the
         insights/recommendations list below. Hidden by the inline
         script after this block using the same #sp_shared_filter_bar
         presence check as the Website selector above - present on the
         Dashboard tab, absent on the standalone Tools-menu page. -->
    <div id="recScoreSummaryWrap">
    <?php if (!empty($aiVisibilitySummary) && $aiVisibilitySummary['overall'] !== null) {
        $avScore = intval($aiVisibilitySummary['overall']);
        $avColor1 = $avScore >= 67 ? '#1f9d55' : ($avScore >= 34 ? '#e0a23b' : '#e05353');
        $avColor2 = $avScore >= 67 ? '#34d399' : ($avScore >= 34 ? '#f5c161' : '#f0847f');
        $avGradient = "$avColor1 0%, $avColor2 100%";
        $avAccents = array(
            'AI Bot Crawling' => array('color' => '#0d9488', 'icon' => 'fa-robot'),
            'AI Overview Citation' => array('color' => '#764ba2', 'icon' => 'fa-layer-group'),
            'LLM Mention Rate' => array('color' => '#d6336c', 'icon' => 'fa-comment-dots'),
            'AI Referral Traffic' => array('color' => '#c9820f', 'icon' => 'fa-mouse-pointer'),
        );
        $avDefaultAccent = array('color' => '#4a4e69', 'icon' => 'fa-chart-bar');
    ?>
    <div class="rec-card" style="padding:22px 26px; border-left:4px solid transparent; border-image:linear-gradient(180deg, <?php echo $avGradient?>) 1;">
        <div style="display:flex;align-items:center;gap:28px;flex-wrap:wrap;">
            <div style="text-align:center;flex-shrink:0;">
                <div style="font-size:11px;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">AI Visibility Score</div>
                <div style="width:88px;height:88px;border-radius:50%;margin:0 auto;display:flex;align-items:center;justify-content:center;background:radial-gradient(circle, #fff 58%, transparent 60%), conic-gradient(<?php echo htmlspecialchars($avColor1)?> 0%, <?php echo htmlspecialchars($avColor2)?> <?php echo $avScore?>%, #eef0f5 <?php echo $avScore?>% 100%);box-shadow:0 2px 10px rgba(0,0,0,0.06);">
                    <span style="font-size:32px;font-weight:800;background:linear-gradient(135deg, <?php echo $avGradient?>);-webkit-background-clip:text;background-clip:text;color:transparent;line-height:1;"><?php echo $avScore?></span>
                </div>
            </div>
            <div style="align-self:stretch;width:1px;background:linear-gradient(180deg, transparent, #e5e7eb, transparent);flex-shrink:0;"></div>
            <div style="display:flex;gap:22px;flex-wrap:wrap;flex:1;">
                <?php foreach ($aiVisibilitySummary['components'] as $component) {
                    $accent = isset($avAccents[$component['label']]) ? $avAccents[$component['label']] : $avDefaultAccent;
                ?>
                <div style="min-width:130px;text-align:center;" title="<?php echo htmlspecialchars($component['detail'])?>">
                    <div style="width:26px;height:26px;border-radius:8px;margin:0 auto 5px;display:flex;align-items:center;justify-content:center;font-size:11px;color:#fff;background:<?php echo $component['measured'] ? htmlspecialchars($accent['color']) : '#c5c8d4'?>;">
                        <i class="fas <?php echo htmlspecialchars($accent['icon'])?>"></i>
                    </div>
                    <div style="font-size:18px;font-weight:700;color:<?php echo $component['measured'] ? htmlspecialchars($accent['color']) : '#c5c8d4'?>;"><?php echo $component['measured'] ? intval($component['score']) . '%' : '&mdash;'?></div>
                    <div style="font-size:11px;color:#999;font-weight:600;margin-top:2px;"><?php echo htmlspecialchars($component['label'])?></div>
                    <?php if (empty($component['periodAware'])) { ?>
                    <div style="font-size:9px;color:#b0b4c0;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;margin-top:1px;">current</div>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
            <a href="<?php echo SP_WEBPATH?>/seo-tools.php?menu_sec=ai-visibility" class="rec-btn" style="text-decoration:none;flex-shrink:0;">
                <i class="fas fa-eye"></i> View full AI Visibility report
            </a>
        </div>
    </div>
    <?php } ?>

    <?php if (!empty($aioSummary) && !empty($aioSummary['measured'])) {
        $aioRate = !empty($aioSummary['present']) ? (int) round((intval($aioSummary['cited']) / intval($aioSummary['present'])) * 100) : null;
        $aioMissed = intval($aioSummary['present']) - intval($aioSummary['cited']);
        $aioTiles = array(
            array('icon' => 'fa-key', 'color' => '#4a4e69', 'value' => intval($aioSummary['measured']), 'label' => 'Keyword-Engine Pairs Tracked'),
            array('icon' => 'fa-robot', 'color' => '#667eea', 'value' => intval($aioSummary['present']), 'label' => 'AI Overviews Seen'),
            array('icon' => 'fa-check-circle', 'color' => '#11998e', 'value' => intval($aioSummary['cited']), 'label' => 'Times You Were Cited', 'suffix' => $aioRate !== null ? " ($aioRate%)" : ''),
            array('icon' => 'fa-times-circle', 'color' => '#e05353', 'value' => max(0, $aioMissed), 'label' => 'AI Overviews You Missed'),
        );
    ?>
    <div class="rec-card" style="padding:22px 26px; margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:34px;height:34px;border-radius:8px;background:#764ba2;display:flex;align-items:center;justify-content:center;color:#fff;font-size:15px;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:700;color:#333;">AI Overview Tracking</div>
                    <div style="font-size:9px;color:#b0b4c0;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;" title="AI Overview presence is each keyword's latest measured state, not a historical count - doesn't change with the Period selector above">current snapshot</div>
                </div>
            </div>
            <a href="<?php echo SP_WEBPATH?>/aivisibility.php?sec=aioverview&website_id=<?php echo intval($websiteId)?>" class="rec-btn" style="text-decoration:none;">
                <i class="fas fa-search"></i> View full AI Overview report
            </a>
        </div>

        <div style="display:flex;gap:28px;flex-wrap:wrap;">
            <?php foreach ($aioTiles as $tile) { ?>
            <div style="min-width:140px;text-align:center;">
                <div style="width:30px;height:30px;border-radius:8px;margin:0 auto 6px;display:flex;align-items:center;justify-content:center;font-size:12px;color:#fff;background:<?php echo htmlspecialchars($tile['color'])?>;">
                    <i class="fas <?php echo htmlspecialchars($tile['icon'])?>"></i>
                </div>
                <div style="font-size:22px;font-weight:800;color:<?php echo htmlspecialchars($tile['color'])?>;"><?php echo $tile['value'] . ($tile['suffix'] ?? '')?></div>
                <div style="font-size:11px;color:#999;font-weight:600;margin-top:2px;"><?php echo htmlspecialchars($tile['label'])?></div>
            </div>
            <?php } ?>
        </div>

        <?php if (!empty($aioTopCompetitor) || !empty($aioSummary['unsupported'])) { ?>
        <div style="margin-top:18px;padding-top:16px;border-top:1px solid #eee;display:flex;gap:24px;flex-wrap:wrap;font-size:13px;color:#555;">
            <?php if (!empty($aioTopCompetitor)) { ?>
            <div>
                <i class="fas fa-crosshairs" style="color:#e0a23b;margin-right:6px;"></i>
                Most often cited instead of you: <strong><?php echo htmlspecialchars($aioTopCompetitor['domain'])?></strong>
                (<?php echo intval($aioTopCompetitor['keyword_count'])?> keyword<?php echo intval($aioTopCompetitor['keyword_count']) == 1 ? '' : 's'?>)
            </div>
            <?php } ?>
            <?php if (!empty($aioSummary['unsupported'])) { ?>
            <div style="color:#999;">
                <i class="fas fa-info-circle" style="margin-right:6px;"></i>
                <?php echo intval($aioSummary['unsupported'])?> keyword&middot;engine combo<?php echo intval($aioSummary['unsupported']) == 1 ? '' : 's'?> on a search engine AI Overview checking doesn't support yet
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
    </div>
    <script>
    // see the comment at the top of #recScoreSummaryWrap - hide the score/
    // tracking summary when this page is NOT the Dashboard's AI Visibility
    // tab (i.e. reached directly via Tools -> AI Visibility -> AI Insights),
    // so "AI Insights" shows only the insights list it's named for.
    if (!document.getElementById('sp_shared_filter_bar')) {
        document.getElementById('recScoreSummaryWrap').style.display = 'none';
    }
    </script>

    <?php if (!empty($refreshError)) { ?>
        <div class="rec-empty rec-error-banner">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($refreshError) ?>
        </div>
    <?php } ?>

    <?php if (empty($recommendations) && empty($refreshedAt)) { ?>
        <div class="rec-empty">
            <i class="fas fa-info-circle"></i>
            No AI insights yet. Click <strong>Refresh AI Insights</strong> to analyse your SEO data.
        </div>
    <?php } elseif (empty($recommendations)) { ?>
        <div class="rec-empty rec-clear">
            <i class="fas fa-check-circle"></i>
            All clear - no issues found as of <?php echo htmlspecialchars($refreshedAt) ?>.
        </div>
    <?php } else { ?>

        <div class="rec-banner <?php echo empty($grouped['error']) ? 'rec-clear' : '' ?>">
            <div class="rec-banner-icon"><i class="fas <?php echo empty($grouped['error']) ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i></div>
            <div class="rec-banner-text">
                <h2><?php echo $totalCount ?> AI insight<?php echo $totalCount == 1 ? '' : 's' ?> found</h2>
                <p>
                    <?php echo count($grouped['error']) ?> errors &middot;
                    <?php echo count($grouped['warning']) ?> warnings &middot;
                    <?php echo count($grouped['todo']) ?> to-dos
                </p>
            </div>
        </div>

        <?php if (!empty($localAiAvailable)) { ?>
        <div class="rec-card" style="padding:18px 22px;">
            <button type="button" class="rec-btn" id="aiSummaryBtn" onclick="aivGenerateAISummary(<?php echo intval($websiteId)?>)">
                <i class="fas fa-robot"></i> <?php echo $spTextRec['Generate AI summary'] ?? 'Generate AI summary'?>
            </button>
            <p id="aiSummaryText" style="display:none;margin-top:12px;color:#444;line-height:1.6;"></p>
        </div>
        <script>
        function aivGenerateAISummary(websiteId) {
            var btn = document.getElementById('aiSummaryBtn');
            var el = document.getElementById('aiSummaryText');
            btn.disabled = true;
            fetch('<?php echo SP_WEBPATH?>/recommendations_dashboard.php?sec=generate-ai-summary&website_id=' + websiteId, { credentials: 'same-origin' })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    btn.disabled = false;
                    el.textContent = data.ok ? data.summary : (data.error || '<?php echo addslashes($spTextRec['ai-summary-unavailable'] ?? 'Local AI summary is not available right now.')?>');
                    el.style.display = 'block';
                })
                .catch(function() {
                    btn.disabled = false;
                    el.textContent = '<?php echo addslashes($spTextRec['ai-summary-unavailable'] ?? 'Local AI summary is not available right now.')?>';
                    el.style.display = 'block';
                });
        }
        </script>
        <?php } ?>

        <?php foreach ($typeMeta as $type => $meta) {
            if (empty($grouped[$type])) continue;
            $count = count($grouped[$type]);
            ?>
        <div class="rec-card">
            <div class="rec-card-header <?php echo $meta['class'] ?>">
                <i class="fas <?php echo $meta['icon'] ?>"></i>
                <?php echo $meta['label'] ?>
                <span class="rec-count-badge"><?php echo $count ?></span>
            </div>
            <div class="table-responsive">
            <table class="rec-table">
                <thead>
                    <tr>
                        <th style="width:45%">Recommendation</th>
                        <th>Details</th>
                        <?php if (!empty($seoDiaryPluginId)) { ?>
                        <th style="width:1%; white-space:nowrap;"></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grouped[$type] as $rec) {
                        // Each row decides for itself whether its `meta` JSON matches a
                        // known shape worth rendering as stat chips - a per-row check,
                        // not a per-group one, since multiple categories can share a type.
                        $meta_data = !empty($rec['meta']) ? json_decode($rec['meta'], true) : array();
                        $isWmRow = ($rec['category'] === 'webmaster_tools' && !empty($meta_data));
                        ?>
                    <tr>
                        <td>
                            <div class="rec-title"><?php echo htmlspecialchars($rec['title']) ?></div>
                        </td>
                        <td class="rec-desc">
                            <?php if ($isWmRow) { ?>
                            <div class="rec-stats">
                                <span class="rec-stat">Impressions (30d): <b><?php echo number_format($meta_data['impressions'] ?? 0) ?></b></span>
                                <span class="rec-stat">Avg Position: <b><?php echo $meta_data['average_position'] ?? '—' ?></b></span>
                                <span class="rec-stat">Clicks (30d): <b><?php echo number_format($meta_data['clicks'] ?? 0) ?></b></span>
                                <span class="rec-stat">Avg CTR: <b><?php echo ($meta_data['ctr'] ?? 0) ?>%</b></span>
                            </div>
                            <?php } ?>
                            <?php echo htmlspecialchars($rec['description']) ?>
                        </td>
                        <?php if (!empty($seoDiaryPluginId)) {
                            // Opt-in only - never auto-created. newDiary() reads
                            // title/description straight off $_REQUEST into the New
                            // Diary form's prefilled fields - the user still picks a
                            // project/category/due date and confirms before anything
                            // is actually created.
                            //
                            // Opens via the shared Bootstrap #ajaxModalSP (js/popup.js)
                            // rather than navigating away or using the jQuery UI
                            // #dialogContent dialog (scriptDoLoadDialog()) - that one
                            // wraps every popup in its own generic "Seo Panel" title
                            // bar on top of the content's own heading, a redundant
                            // second header this content doesn't need (the New Diary
                            // form already has its own icon+title). #ajaxModalSP has
                            // no such wrapper chrome, matching the rest of this app's
                            // popups (AI Overview Cited Sources, Competitor Citations).
                            // This ALSO sidesteps the layout/jQuery bug a plain
                            // full-page link to seo-plugins.php used to hit (every
                            // plugin controller hardcodes $layout='ajax' - a bare
                            // fragment with no jQuery, fine when AJAX-loaded like this,
                            // fatal as a full top-level navigation) without depending
                            // on admin-panel.php's start_script mechanism at all - we
                            // never leave the current page.
                            $diaryUrl = SP_WEBPATH . "/seo-plugins.php?pid=" . intval($seoDiaryPluginId)
                                . "&action=newDiary"
                                . "&title=" . urlencode($rec['title'])
                                . "&description=" . urlencode($rec['description']);
                            $diaryOnclick = "openAjaxModalSP('" . addslashes($diaryUrl) . "', '<i class=&quot;fas fa-book&quot;></i> " . addslashes($spTextRec['Add to SEO Diary'] ?? 'Add to SEO Diary') . "')";
                            ?>
                        <td>
                            <a href="javascript:void(0);" onclick="<?php echo htmlspecialchars($diaryOnclick, ENT_QUOTES) ?>" class="rec-btn" style="text-decoration:none; display:inline-block;" title="Add to SEO Diary">
                                <i class="fas fa-book"></i>
                            </a>
                        </td>
                        <?php } ?>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        </div>
        <?php } ?>

    <?php } ?>

</div>
<?php } ?>
