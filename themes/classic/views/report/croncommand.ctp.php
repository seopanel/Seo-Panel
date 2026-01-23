<?php echo showSectionHead($spTextPanel['Cron Command']); ?>

<style>
.cron-container {
    max-width: 900px;
    margin: 0 auto;
}
.cron-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px 30px;
    border-radius: 10px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
}
.cron-header-icon {
    font-size: 48px;
    opacity: 0.9;
}
.cron-header-text h2 {
    margin: 0 0 8px 0;
    font-size: 22px;
    font-weight: 600;
}
.cron-header-text p {
    margin: 0;
    opacity: 0.9;
    font-size: 14px;
}
.cron-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.cron-card-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.cron-card-title i {
    color: #667eea;
    font-size: 18px;
}
.cron-command-box {
    background: #1e1e1e;
    border-radius: 8px;
    padding: 20px;
    position: relative;
    margin-bottom: 15px;
}
.cron-command {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 14px;
    color: #4ec9b0;
    word-break: break-all;
    margin: 0;
    line-height: 1.6;
}
.cron-copy-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #667eea;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s ease;
}
.cron-copy-btn:hover {
    background: #5a6fd6;
    transform: translateY(-1px);
}
.cron-copy-btn.copied {
    background: #28a745;
}
.cron-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}
.cron-info-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px 20px;
    border-left: 4px solid #667eea;
}
.cron-info-item.warning {
    border-left-color: #ffc107;
    background: #fffbeb;
}
.cron-info-item.success {
    border-left-color: #28a745;
    background: #f0fff4;
}
.cron-info-item.info {
    border-left-color: #17a2b8;
    background: #e7f6f8;
}
.cron-info-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}
.cron-info-value {
    font-size: 14px;
    color: #333;
    font-weight: 500;
}
.cron-schedule-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
.cron-schedule-table th {
    background: #f8f9fa;
    padding: 10px 15px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    border-bottom: 2px solid #dee2e6;
}
.cron-schedule-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #e9ecef;
    font-size: 14px;
}
.cron-schedule-table code {
    background: #e9ecef;
    padding: 3px 8px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 13px;
}
.cron-note {
    background: #e7f3ff;
    border-left: 4px solid #007bff;
    padding: 15px 20px;
    border-radius: 0 8px 8px 0;
    margin-top: 20px;
    font-size: 14px;
    color: #004085;
}
.cron-note i {
    margin-right: 8px;
}
.cron-link-box {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    margin-top: 15px;
}
.cron-link-box a {
    color: #667eea;
    font-weight: 600;
    text-decoration: none;
    font-size: 15px;
}
.cron-link-box a:hover {
    text-decoration: underline;
}
.cron-link-box i {
    margin-right: 8px;
}
</style>

<div class="cron-container">
    <div class="cron-header">
        <div class="cron-header-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="cron-header-text">
            <h2><?php echo $spTextPanel['Reports Manager Cron Setup'] ?? 'Reports Manager Cron Setup'?></h2>
            <p><?php echo $spTextPanel['Automate your SEO reports generation with scheduled cron jobs'] ?? 'Automate your SEO reports generation with scheduled cron jobs'?></p>
        </div>
    </div>

    <div class="cron-card">
        <div class="cron-card-title">
            <i class="fas fa-terminal"></i>
            <?php echo $spTextPanel['Add following command to your cron tab']?>
        </div>

        <?php $command = "*/15 * * * * php ".SP_ABSPATH."/cron.php"; ?>

        <div class="cron-command-box">
            <button class="cron-copy-btn" id="copyBtn">
                <i class="fas fa-copy"></i> <?php echo $spText['button']['Copy'] ?? 'Copy'?>
            </button>
            <pre class="cron-command" id="cronCommand"><?php echo $command; ?></pre>
        </div>

        <div class="cron-info-grid">
            <div class="cron-info-item">
                <div class="cron-info-label"><?php echo $spText['label']['Schedule'] ?? 'Schedule'?></div>
                <div class="cron-info-value"><?php echo $spTextPanel['Every 15 minutes'] ?? 'Every 15 minutes'?></div>
            </div>
            <div class="cron-info-item success">
                <div class="cron-info-label"><?php echo $spText['label']['Script Path'] ?? 'Script Path'?></div>
                <div class="cron-info-value"><?php echo SP_ABSPATH; ?>/cron.php</div>
            </div>
        </div>
    </div>

    <div class="cron-card">
        <div class="cron-card-title">
            <i class="fas fa-tasks"></i>
            <?php echo $spTextPanel['What This Cron Does'] ?? 'What This Cron Does'?>
        </div>

        <div class="cron-info-grid">
            <div class="cron-info-item info">
                <div class="cron-info-label"><i class="fas fa-search"></i> <?php echo $spText['label']['Feature'] ?? 'Feature'?></div>
                <div class="cron-info-value"><?php echo $spTextPanel['Keyword Position Checking'] ?? 'Keyword Position Checking'?></div>
            </div>
            <div class="cron-info-item info">
                <div class="cron-info-label"><i class="fas fa-link"></i> <?php echo $spText['label']['Feature'] ?? 'Feature'?></div>
                <div class="cron-info-value"><?php echo $spTextPanel['Backlinks Reports'] ?? 'Backlinks Reports'?></div>
            </div>
            <div class="cron-info-item info">
                <div class="cron-info-label"><i class="fas fa-chart-bar"></i> <?php echo $spText['label']['Feature'] ?? 'Feature'?></div>
                <div class="cron-info-value"><?php echo $spTextPanel['Rank Reports'] ?? 'Rank Reports'?></div>
            </div>
            <div class="cron-info-item info">
                <div class="cron-info-label"><i class="fas fa-globe"></i> <?php echo $spText['label']['Feature'] ?? 'Feature'?></div>
                <div class="cron-info-value"><?php echo $spTextPanel['Saturation Reports'] ?? 'Saturation Reports'?></div>
            </div>
        </div>
    </div>

    <div class="cron-card">
        <div class="cron-card-title">
            <i class="fas fa-calendar-alt"></i>
            <?php echo $spTextPanel['Common Cron Schedules'] ?? 'Common Cron Schedules'?>
        </div>

        <table class="cron-schedule-table">
            <thead>
                <tr>
                    <th><?php echo $spText['label']['Schedule'] ?? 'Schedule'?></th>
                    <th><?php echo $spText['label']['Expression'] ?? 'Expression'?></th>
                    <th><?php echo $spText['label']['Description'] ?? 'Description'?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong><?php echo $spTextPanel['Every 15 minutes'] ?? 'Every 15 minutes'?></strong></td>
                    <td><code>*/15 * * * *</code></td>
                    <td><?php echo $spTextPanel['Recommended for active sites'] ?? 'Recommended for active tracking'?></td>
                </tr>
                <tr>
                    <td><strong><?php echo $spTextPanel['Every 30 minutes'] ?? 'Every 30 minutes'?></strong></td>
                    <td><code>*/30 * * * *</code></td>
                    <td><?php echo $spTextPanel['Good balance of frequency'] ?? 'Good balance of frequency'?></td>
                </tr>
                <tr>
                    <td><strong><?php echo $spTextPanel['Every hour'] ?? 'Every hour'?></strong></td>
                    <td><code>0 * * * *</code></td>
                    <td><?php echo $spTextPanel['For moderate tracking needs'] ?? 'For moderate tracking needs'?></td>
                </tr>
                <tr>
                    <td><strong><?php echo $spTextPanel['Daily at midnight'] ?? 'Daily at midnight'?></strong></td>
                    <td><code>0 0 * * *</code></td>
                    <td><?php echo $spTextPanel['Once per day'] ?? 'Once per day'?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="cron-card">
        <div class="cron-card-title">
            <i class="fas fa-question-circle"></i>
            <?php echo $spTextPanel['How to Setup Cron'] ?? 'How to Setup Cron'?>
        </div>

        <div class="cron-info-item" style="margin-bottom: 15px;">
            <div class="cron-info-label"><?php echo $spText['label']['Step'] ?? 'Step'?> 1</div>
            <div class="cron-info-value"><?php echo $spTextPanel['Open your terminal or SSH into your server'] ?? 'Open your terminal or SSH into your server'?></div>
        </div>

        <div class="cron-info-item" style="margin-bottom: 15px;">
            <div class="cron-info-label"><?php echo $spText['label']['Step'] ?? 'Step'?> 2</div>
            <div class="cron-info-value"><?php echo $spTextPanel['Type'] ?? 'Type'?> <code>crontab -e</code> <?php echo $spTextPanel['to edit your crontab'] ?? 'to edit your crontab'?></div>
        </div>

        <div class="cron-info-item" style="margin-bottom: 15px;">
            <div class="cron-info-label"><?php echo $spText['label']['Step'] ?? 'Step'?> 3</div>
            <div class="cron-info-value"><?php echo $spTextPanel['Paste the command above and save'] ?? 'Paste the command above and save the file'?></div>
        </div>

        <div class="cron-note">
            <i class="fas fa-info-circle"></i>
            <strong><?php echo $spText['common']['Note'] ?? 'Note'?>:</strong>
            <?php echo $spTextPanel['Make sure PHP is in your system PATH'] ?? 'Make sure PHP is in your system PATH. If not, use the full path to PHP (e.g., /usr/bin/php)'?>
        </div>

        <div class="cron-link-box">
            <p style="margin: 0 0 10px 0; color: #666;"><?php echo $spTextPanel['alsocheckfollowlink'] ?? 'For detailed setup instructions, visit:'?></p>
            <a href="<?php echo SP_MAIN_SITE?>/install/setup-cron/" target="_blank">
                <i class="fas fa-external-link-alt"></i> <?php echo SP_MAIN_SITE?>/install/setup-cron/
            </a>
        </div>
    </div>
</div>

<script>
document.getElementById('copyBtn').addEventListener('click', function() {
    var btn = this;
    var command = document.getElementById('cronCommand').textContent;
    var textarea = document.createElement('textarea');
    textarea.value = command;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    btn.innerHTML = '<i class="fas fa-check"></i> <?php echo $spText['common']['Copied'] ?? 'Copied'?>!';
    btn.classList.add('copied');
    setTimeout(function() {
        btn.innerHTML = '<i class="fas fa-copy"></i> <?php echo $spText['button']['Copy'] ?? 'Copy'?>';
        btn.classList.remove('copied');
    }, 2000);
});
</script>
