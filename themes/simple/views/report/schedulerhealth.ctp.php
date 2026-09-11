<?php echo showSectionHead($spTextPanel['Scheduler Health'] ?? 'Scheduler Health'); ?>

<style>
.sh-container {
    max-width: 1000px;
    margin: 0 auto;
}
.sh-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 30px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
}
.sh-banner.locked {
    background: linear-gradient(135deg, #f0932b 0%, #eb4d4b 100%);
}
.sh-banner-status { font-size: 18px; font-weight: 600; }
.sh-banner-meta { font-size: 13px; opacity: 0.9; margin-top: 4px; }
.sh-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.sh-card-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.sh-card-title i { color: #667eea; font-size: 18px; }
.sh-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sh-table th, .sh-table td {
    text-align: left;
    padding: 8px 10px;
    border-bottom: 1px solid #eee;
}
.sh-table th { color: #888; font-weight: 600; text-transform: uppercase; font-size: 11px; }
.sh-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}
.sh-badge.completed, .sh-badge.success { background: #e6f7ee; color: #1a9d5c; }
.sh-badge.failed { background: #fdeaea; color: #d64545; }
.sh-badge.running { background: #eaf2fd; color: #3679d6; }
.sh-badge.incomplete, .sh-badge.pending { background: #fef6e6; color: #c98a13; }
.sh-empty { color: #999; font-style: italic; padding: 10px 0; }
.sh-command-box {
    background: #1e1e1e;
    border-radius: 8px;
    padding: 16px 20px;
    position: relative;
    margin: 15px 0;
}
.sh-command {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 13px;
    color: #4ec9b0;
    word-break: break-all;
    margin: 0;
}
.sh-copy-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #667eea;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
}
.sh-form-row { display: flex; align-items: center; gap: 12px; margin-bottom: 15px; flex-wrap: wrap; }
.sh-form-row label { font-weight: 600; color: #444; font-size: 13px; }
.sh-btn {
    background: #667eea;
    color: white;
    border: none;
    padding: 8px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
}
.sh-btn.secondary { background: #eee; color: #444; }
.sh-note { font-size: 12px; color: #888; margin-top: 10px; }
</style>

<div class="sh-container">

    <?php if (!empty($lockedBy)): ?>
    <div class="sh-banner locked">
        <div>
            <div class="sh-banner-status"><i class="fas fa-sync fa-spin"></i> <?php echo $spTextPanel['A cron run is currently in progress'] ?? 'A cron run is currently in progress'; ?></div>
        </div>
    </div>
    <?php elseif (!empty($lastRun)): ?>
    <div class="sh-banner">
        <div>
            <div class="sh-banner-status">
                <?php echo $spTextPanel['Last run'] ?? 'Last run'; ?>:
                <span class="sh-badge <?php echo htmlspecialchars($lastRun['status']); ?>"><?php echo htmlspecialchars($lastRun['status']); ?></span>
            </div>
            <div class="sh-banner-meta">
                <?php echo htmlspecialchars($lastRun['trigger_source']); ?> &middot;
                <?php echo htmlspecialchars($lastRun['started_at']); ?> &middot;
                <?php echo !empty($lastRun['duration_ms']) ? number_format($lastRun['duration_ms']) . ' ms' : '-'; ?> &middot;
                <?php echo intval($lastRun['websites_processed']); ?> <?php echo $spTextPanel['websites processed'] ?? 'websites processed'; ?>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="sh-banner">
        <div class="sh-banner-status"><?php echo $spTextPanel['No cron runs recorded yet'] ?? 'No cron runs recorded yet'; ?></div>
    </div>
    <?php endif; ?>

    <div class="sh-card">
        <div class="sh-card-title"><i class="fas fa-history"></i> <?php echo $spTextPanel['Recent runs'] ?? 'Recent runs'; ?></div>
        <?php if (!empty($recentRuns)): ?>
        <table class="sh-table">
            <thead><tr>
                <th><?php echo $spText['label']['Started'] ?? 'Started'; ?></th>
                <th><?php echo $spText['label']['Trigger'] ?? 'Trigger'; ?></th>
                <th><?php echo $spText['label']['Status'] ?? 'Status'; ?></th>
                <th><?php echo $spText['label']['Duration'] ?? 'Duration'; ?></th>
                <th><?php echo $spTextPanel['Websites'] ?? 'Websites'; ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($recentRuns as $run): ?>
                <tr>
                    <td><?php echo htmlspecialchars($run['started_at']); ?></td>
                    <td><?php echo htmlspecialchars($run['trigger_source']); ?></td>
                    <td><span class="sh-badge <?php echo htmlspecialchars($run['status']); ?>"><?php echo htmlspecialchars($run['status']); ?></span></td>
                    <td><?php echo !empty($run['duration_ms']) ? number_format($run['duration_ms']) . ' ms' : '-'; ?></td>
                    <td><?php echo intval($run['websites_processed']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="sh-empty"><?php echo $spTextPanel['No cron runs recorded yet'] ?? 'No cron runs recorded yet'; ?></div>
        <?php endif; ?>
    </div>

    <div class="sh-card">
        <div class="sh-card-title"><i class="fas fa-tasks"></i> <?php echo $spTextPanel['Per-tool activity (last 7 days)'] ?? 'Per-tool activity (last 7 days)'; ?></div>
        <?php if (!empty($toolStats)): ?>
        <table class="sh-table">
            <thead><tr>
                <th><?php echo $spTextPanel['Tool'] ?? 'Tool'; ?></th>
                <th><?php echo $spTextPanel['Success'] ?? 'Success'; ?></th>
                <th><?php echo $spTextPanel['Failed'] ?? 'Failed'; ?></th>
                <th><?php echo $spTextPanel['Avg duration'] ?? 'Avg duration'; ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($toolStats as $stat): ?>
                <tr>
                    <td><?php echo htmlspecialchars($stat['url_section']); ?></td>
                    <td><?php echo intval($stat['success_count']); ?></td>
                    <td><?php echo intval($stat['failed_count']) > 0 ? '<span class="sh-badge failed">' . intval($stat['failed_count']) . '</span>' : '0'; ?></td>
                    <td><?php echo !empty($stat['avg_duration_ms']) ? number_format($stat['avg_duration_ms']) . ' ms' : '-'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="sh-empty"><?php echo $spTextPanel['No activity recorded in the last 7 days'] ?? 'No activity recorded in the last 7 days'; ?></div>
        <?php endif; ?>
    </div>

    <?php if (!empty($jobQueueEnabled)): ?>
    <div class="sh-card">
        <div class="sh-card-title"><i class="fas fa-layer-group"></i> <?php echo $spTextPanel['Job queue backlog'] ?? 'Job queue backlog'; ?></div>
        <?php if (!empty($queueBacklog)): ?>
        <table class="sh-table">
            <thead><tr>
                <th><?php echo $spTextPanel['Tool'] ?? 'Tool'; ?></th>
                <th><?php echo $spText['label']['Status'] ?? 'Status'; ?></th>
                <th><?php echo $spTextPanel['Count'] ?? 'Count'; ?></th>
                <th><?php echo $spTextPanel['Oldest pending since'] ?? 'Oldest pending since'; ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($queueBacklog as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['url_section']); ?></td>
                    <td><span class="sh-badge <?php echo htmlspecialchars($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td><?php echo intval($row['cnt']); ?></td>
                    <td><?php echo htmlspecialchars($row['oldest_available_at']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="sh-empty"><?php echo $spTextPanel['Queue is empty'] ?? 'Queue is empty'; ?></div>
        <?php endif; ?>

        <?php if (!empty($failedSamples)): ?>
        <div class="sh-card-title" style="margin-top:20px;"><i class="fas fa-exclamation-triangle"></i> <?php echo $spTextPanel['Recently failed chunks'] ?? 'Recently failed chunks'; ?></div>
        <table class="sh-table">
            <thead><tr>
                <th><?php echo $spTextPanel['Tool'] ?? 'Tool'; ?></th>
                <th><?php echo $spTextPanel['Chunk'] ?? 'Chunk'; ?></th>
                <th><?php echo $spTextPanel['Error'] ?? 'Error'; ?></th>
                <th><?php echo $spTextPanel['When'] ?? 'When'; ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($failedSamples as $fail): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fail['url_section']); ?></td>
                    <td><?php echo htmlspecialchars($fail['chunk_key']); ?></td>
                    <td><?php echo htmlspecialchars($fail['last_error']); ?></td>
                    <td><?php echo htmlspecialchars($fail['updated_at']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="sh-card">
        <div class="sh-card-title"><i class="fas fa-satellite-dish"></i> <?php echo $spTextPanel['External ping trigger'] ?? 'External ping trigger'; ?></div>
        <p style="color:#666; font-size:13px; margin-top:0;">
            <?php echo $spTextPanel['pingtriggerdesc'] ?? "Point an external cron/uptime service (or your own crontab) at this URL to trigger short, budget-limited cron runs - useful on hosts where you can't set up a real system cron job."; ?>
        </p>

        <form method="post" action="cron.php">
            <input type="hidden" name="sec" value="save_ping_settings">
            <div class="sh-form-row">
                <label><input type="checkbox" name="ping_enabled" value="1" <?php echo !empty($pingEnabled) ? 'checked' : ''; ?>> <?php echo $spTextPanel['Enable ping trigger'] ?? 'Enable ping trigger'; ?></label>
                <label><?php echo $spTextPanel['Budget (seconds)'] ?? 'Budget (seconds)'; ?>
                    <input type="number" name="ping_budget" value="<?php echo intval($pingBudget); ?>" min="5" max="60" style="width:60px;">
                </label>
                <button type="submit" class="sh-btn"><?php echo $spText['button']['Save'] ?? 'Save'; ?></button>
            </div>
        </form>

        <?php if (!empty($pingSecret)): ?>
        <div class="sh-command-box">
            <button class="sh-copy-btn" id="shCopyBtn"><i class="fas fa-copy"></i> <?php echo $spText['button']['Copy'] ?? 'Copy'; ?></button>
            <pre class="sh-command" id="shPingUrl"><?php echo htmlspecialchars($pingUrl . '?key=' . $pingSecret); ?></pre>
        </div>
        <?php else: ?>
        <div class="sh-empty"><?php echo $spTextPanel['No secret generated yet - generate one below before enabling the ping trigger.'] ?? 'No secret generated yet - generate one below before enabling the ping trigger.'; ?></div>
        <?php endif; ?>

        <form method="post" action="cron.php" onsubmit="return confirm('<?php echo $spTextPanel['Regenerating the secret will invalidate the current ping URL. Continue?'] ?? 'Regenerating the secret will invalidate the current ping URL. Continue?'; ?>');">
            <input type="hidden" name="sec" value="regenerate_ping_secret">
            <button type="submit" class="sh-btn secondary"><i class="fas fa-key"></i> <?php echo $spTextPanel['Generate new secret'] ?? 'Generate new secret'; ?></button>
        </form>

        <div class="sh-note"><?php echo $spTextPanel['pingsecretnote'] ?? 'The secret identifies and authorizes the caller - anyone with this URL can trigger a cron run, so treat it like a password. The endpoint always responds with no output.'; ?></div>
    </div>

</div>

<script>
var shCopyBtn = document.getElementById('shCopyBtn');
if (shCopyBtn) {
    shCopyBtn.addEventListener('click', function() {
        var btn = this;
        var url = document.getElementById('shPingUrl').textContent;
        var textarea = document.createElement('textarea');
        textarea.value = url;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        btn.innerHTML = '<i class="fas fa-check"></i> <?php echo $spText['common']['Copied'] ?? 'Copied'; ?>!';
        setTimeout(function() {
            btn.innerHTML = '<i class="fas fa-copy"></i> <?php echo $spText['button']['Copy'] ?? 'Copy'; ?>';
        }, 2000);
    });
}
</script>
