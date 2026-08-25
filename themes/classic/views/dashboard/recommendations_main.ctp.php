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

<style>
.rec-container { width: 100%; }
.rec-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px 30px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
}
.rec-banner.rec-clear { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.rec-banner-icon { font-size: 42px; opacity: 0.9; }
.rec-banner-text h2 { margin: 0 0 6px 0; font-size: 21px; font-weight: 600; }
.rec-banner-text p { margin: 0; opacity: 0.92; font-size: 13px; }
.rec-controls {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 18px 22px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
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
.rec-empty {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    color: #888;
}
.rec-empty i { font-size: 28px; color: #667eea; display: block; margin-bottom: 10px; }
</style>

<div class="rec-container">

    <form id="recommendations_dashboard_form" method="post">
        <div class="rec-controls">
            <label><?php echo $spText['common']['Website'] ?? 'Website' ?>:</label>
            <select name="website_id" id="website_id" class="custom-select"
                onchange="scriptDoLoadPost('<?php echo SP_WEBPATH?>/recommendations_dashboard.php', 'recommendations_dashboard_form', 'content')">
                <?php foreach ($websiteList as $ws) { ?>
                    <option value="<?php echo $ws['id'] ?>" <?php echo ($ws['id'] == $websiteId) ? 'selected' : '' ?>>
                        <?php echo htmlspecialchars($ws['name']) ?>
                    </option>
                <?php } ?>
            </select>

            <button type="button" class="rec-btn"
                onclick="scriptDoLoadPost('<?php echo SP_WEBPATH?>/recommendations_dashboard.php?sec=refresh', 'recommendations_dashboard_form', 'content')">
                <i class="fas fa-sync-alt"></i> Refresh AI Insights
            </button>

            <?php if (!empty($refreshedAt)) { ?>
            <span class="rec-last-updated">Last updated: <?php echo htmlspecialchars($refreshedAt) ?></span>
            <?php } ?>
        </div>
    </form>

    <?php if (empty($recommendations)) { ?>
        <div class="rec-empty">
            <i class="fas fa-info-circle"></i>
            No AI insights yet. Click <strong>Refresh AI Insights</strong> to analyse your SEO data.
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

        <?php foreach ($typeMeta as $type => $meta) {
            if (empty($grouped[$type])) continue;
            $count = count($grouped[$type]);
            $showWmCols = ($type === 'warning' && $grouped[$type][0]['category'] === 'webmaster_tools');
            ?>
        <div class="rec-card">
            <div class="rec-card-header <?php echo $meta['class'] ?>">
                <i class="fas <?php echo $meta['icon'] ?>"></i>
                <?php echo $meta['label'] ?>
                <span class="rec-count-badge"><?php echo $count ?></span>
            </div>
            <table class="rec-table">
                <thead>
                    <tr>
                        <th style="width:45%">Recommendation</th>
                        <?php if ($showWmCols) { ?>
                        <th class="rec-num">Impressions<br><span style="font-weight:400;text-transform:none;">30 days</span></th>
                        <th class="rec-num">Avg Position<br><span style="font-weight:400;text-transform:none;">30 days</span></th>
                        <th class="rec-num">Clicks<br><span style="font-weight:400;text-transform:none;">30 days</span></th>
                        <th class="rec-num">Avg CTR</th>
                        <?php } else { ?>
                        <th>Details</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grouped[$type] as $rec) {
                        $meta_data = !empty($rec['meta']) ? json_decode($rec['meta'], true) : array();
                        ?>
                    <tr>
                        <td>
                            <div class="rec-title"><?php echo htmlspecialchars($rec['title']) ?></div>
                            <?php if ($showWmCols) { ?><div class="rec-desc"><?php echo htmlspecialchars($rec['description']) ?></div><?php } ?>
                        </td>
                        <?php if ($showWmCols && !empty($meta_data)) { ?>
                        <td class="rec-num"><?php echo number_format($meta_data['impressions'] ?? 0) ?></td>
                        <td class="rec-num"><?php echo $meta_data['average_position'] ?? '—' ?></td>
                        <td class="rec-num"><?php echo number_format($meta_data['clicks'] ?? 0) ?></td>
                        <td class="rec-num"><?php echo ($meta_data['ctr'] ?? 0) ?>%</td>
                        <?php } else { ?>
                        <td class="rec-desc"><?php echo htmlspecialchars($rec['description']) ?></td>
                        <?php } ?>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>

    <?php } ?>

</div>
<?php } ?>
