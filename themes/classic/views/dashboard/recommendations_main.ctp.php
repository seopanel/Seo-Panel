<?php if (!empty($noWebsites)) {
    include(SP_VIEWPATH.'/dashboard/no_websites.ctp.php');
} else { ?>
<form id="recommendations_dashboard_form" method="post">
<table class="search">
    <tr>
        <th><?php echo $spText['common']['Website'] ?? 'Website' ?>:</th>
        <td>
            <select name="website_id" id="website_id" class="custom-select"
                onchange="scriptDoLoadPost('<?php echo SP_WEBPATH?>/recommendations_dashboard.php', 'recommendations_dashboard_form', 'content')">
                <?php foreach ($websiteList as $ws) { ?>
                    <option value="<?php echo $ws['id'] ?>" <?php echo ($ws['id'] == $websiteId) ? 'selected' : '' ?>>
                        <?php echo htmlspecialchars($ws['name']) ?>
                    </option>
                <?php } ?>
            </select>
        </td>
        <td class="pl-3">
            <button type="button" class="btn btn-primary"
                onclick="scriptDoLoadPost('<?php echo SP_WEBPATH?>/recommendations_dashboard.php?sec=refresh', 'recommendations_dashboard_form', 'content')">
                <i class="fas fa-sync-alt"></i> Refresh Recommendations
            </button>
        </td>
        <?php if (!empty($refreshedAt)) { ?>
        <td class="pl-3 text-muted small align-middle">
            Last updated: <?php echo $refreshedAt ?>
        </td>
        <?php } ?>
    </tr>
</table>
</form>

<div class="mt-4">

<?php if (empty($recommendations)) { ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        No recommendations yet. Click <strong>Refresh Recommendations</strong> to analyse your SEO data.
    </div>
<?php } else {

    // Group by type for ordered display: errors → warnings → todos
    $grouped = array('error' => array(), 'warning' => array(), 'todo' => array());
    foreach ($recommendations as $rec) {
        $grouped[$rec['type']][] = $rec;
    }

    $typeMeta = array(
        'error'   => array('class' => 'danger',  'icon' => 'fa-times-circle',      'label' => 'Errors'),
        'warning' => array('class' => 'warning',  'icon' => 'fa-exclamation-triangle', 'label' => 'Warnings'),
        'todo'    => array('class' => 'info',     'icon' => 'fa-tasks',             'label' => 'To-Do'),
    );

    foreach ($typeMeta as $type => $meta) {
        if (empty($grouped[$type])) continue;
        $count = count($grouped[$type]);
        ?>
        <div class="card mb-4">
            <div class="card-header bg-<?php echo $meta['class'] ?> <?php echo ($meta['class'] === 'warning') ? 'text-dark' : 'text-white' ?>">
                <i class="fas <?php echo $meta['icon'] ?>"></i>
                <strong><?php echo $meta['label'] ?></strong>
                <span class="badge badge-light ml-2"><?php echo $count ?></span>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:40%">Recommendation</th>
                            <th>Details</th>
                            <?php if ($type === 'warning' && $grouped[$type][0]['category'] === 'webmaster_tools') { ?>
                            <th class="text-right" style="width:100px">Impressions<br><small class="text-muted font-weight-normal">30 days</small></th>
                            <th class="text-right" style="width:90px">Avg Position<br><small class="text-muted font-weight-normal">30 days</small></th>
                            <th class="text-right" style="width:70px">Clicks<br><small class="text-muted font-weight-normal">30 days</small></th>
                            <th class="text-right" style="width:60px">Avg CTR</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grouped[$type] as $rec) {
                            $meta_data = !empty($rec['meta']) ? json_decode($rec['meta'], true) : array();
                            ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($rec['title']) ?></strong></td>
                            <td class="text-muted small"><?php echo htmlspecialchars($rec['description']) ?></td>
                            <?php if ($type === 'warning' && $rec['category'] === 'webmaster_tools' && !empty($meta_data)) { ?>
                            <td class="text-right"><?php echo number_format($meta_data['impressions'] ?? 0) ?></td>
                            <td class="text-right"><?php echo $meta_data['average_position'] ?? '—' ?></td>
                            <td class="text-right"><?php echo number_format($meta_data['clicks'] ?? 0) ?></td>
                            <td class="text-right"><?php echo ($meta_data['ctr'] ?? 0) ?>%</td>
                            <?php } else { ?>
                            <td></td><td></td><td></td><td></td>
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
