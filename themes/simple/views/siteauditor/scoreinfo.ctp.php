<style>
.score-info-container {
    max-width: 1200px;
    margin: 0 auto;
}
.score-info-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.score-info-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 25px;
    border-radius: 8px;
    margin-bottom: 25px;
}
.score-info-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
}
.score-info-header p {
    margin: 10px 0 0 0;
    opacity: 0.9;
    font-size: 14px;
}
.score-section {
    margin-bottom: 30px;
}
.score-section-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #667eea;
    display: flex;
    align-items: center;
    gap: 10px;
}
.score-section-title i {
    color: #667eea;
}
.score-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
.score-table thead tr {
    background: #f8f9fa;
}
.score-table th {
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    font-size: 13px;
    text-transform: uppercase;
}
.score-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #e9ecef;
    font-size: 14px;
    color: #212529;
}
.score-table tbody tr:hover {
    background-color: #f8f9fa;
}
.score-positive {
    color: #28a745;
    font-weight: 600;
}
.score-negative {
    color: #dc3545;
    font-weight: 600;
}
.score-neutral {
    color: #6c757d;
    font-weight: 600;
}
.max-score-badge {
    display: inline-block;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 16px;
    font-weight: 600;
    margin-top: 15px;
}
.info-note {
    background: #e7f3ff;
    border-left: 4px solid #007bff;
    padding: 15px 20px;
    margin: 20px 0;
    border-radius: 0 8px 8px 0;
    font-size: 14px;
    color: #004085;
}
.info-note i {
    margin-right: 8px;
}
.factor-icon {
    width: 30px;
    text-align: center;
    color: #667eea;
}
</style>

<?php echo showSectionHead($spTextSA['Score Information'] ?? 'Score Information'); ?>

<div class="score-info-container">
    <div class="score-info-header">
        <h2><i class="fas fa-chart-line"></i> <?php echo $spTextSA['Site Auditor Score Calculation'] ?? 'Site Auditor Score Calculation'?></h2>
        <p><?php echo $spTextSA['Understanding how your page SEO score is calculated'] ?? 'Understanding how your page SEO score is calculated'?></p>
    </div>

    <div class="score-info-card">
        <div class="info-note">
            <i class="fas fa-info-circle"></i>
            <strong><?php echo $spText['common']['Note'] ?? 'Note'?>:</strong>
            <?php echo $spTextSA['The SEO score is calculated based on multiple factors'] ?? 'The SEO score is calculated based on multiple factors. Each factor contributes positively or negatively to the overall score. The maximum possible score is 38 points, which is converted to a percentage for display.'?>
        </div>

        <div class="score-section">
            <div class="score-section-title">
                <i class="fas fa-file-alt"></i> <?php echo $spTextSA['Content & Meta Tags'] ?? 'Content & Meta Tags'?>
            </div>
            <table class="score-table">
                <thead>
                    <tr>
                        <th style="width: 5%;"></th>
                        <th style="width: 25%;"><?php echo $spText['label']['Factor'] ?? 'Factor'?></th>
                        <th style="width: 40%;"><?php echo $spText['label']['Condition'] ?? 'Condition'?></th>
                        <th style="width: 15%;"><?php echo $spText['label']['Score'] ?? 'Score'?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-heading"></i></td>
                        <td><strong><?php echo $spText['label']['Title'] ?? 'Page Title'?></strong></td>
                        <td><?php echo $spTextSA['Optimal length (50-60 characters)'] ?? 'Optimal length (50-60 characters)'?></td>
                        <td class="score-positive">+2</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-heading"></i></td>
                        <td><strong><?php echo $spText['label']['Title'] ?? 'Page Title'?></strong></td>
                        <td><?php echo $spTextSA['Acceptable length (30-70 characters)'] ?? 'Acceptable length (30-70 characters)'?></td>
                        <td class="score-positive">+1</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-heading"></i></td>
                        <td><strong><?php echo $spText['label']['Title'] ?? 'Page Title'?></strong></td>
                        <td><?php echo $spTextSA['Poor length (too short or too long)'] ?? 'Poor length (too short or too long)'?></td>
                        <td class="score-negative">-2</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-align-left"></i></td>
                        <td><strong><?php echo $spText['label']['Description'] ?? 'Meta Description'?></strong></td>
                        <td><?php echo $spTextSA['Optimal length (150-160 characters)'] ?? 'Optimal length (150-160 characters)'?></td>
                        <td class="score-positive">+2</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-align-left"></i></td>
                        <td><strong><?php echo $spText['label']['Description'] ?? 'Meta Description'?></strong></td>
                        <td><?php echo $spTextSA['Acceptable length (120-200 characters)'] ?? 'Acceptable length (120-200 characters)'?></td>
                        <td class="score-positive">+1</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-align-left"></i></td>
                        <td><strong><?php echo $spText['label']['Description'] ?? 'Meta Description'?></strong></td>
                        <td><?php echo $spTextSA['Poor length'] ?? 'Poor length'?></td>
                        <td class="score-negative">-1</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="score-section">
            <div class="score-section-title">
                <i class="fas fa-chart-bar"></i> <?php echo $spTextSA['Authority & Backlinks'] ?? 'Authority & Backlinks'?>
            </div>
            <table class="score-table">
                <thead>
                    <tr>
                        <th style="width: 5%;"></th>
                        <th style="width: 25%;"><?php echo $spText['label']['Factor'] ?? 'Factor'?></th>
                        <th style="width: 40%;"><?php echo $spText['label']['Condition'] ?? 'Condition'?></th>
                        <th style="width: 15%;"><?php echo $spText['label']['Score'] ?? 'Score'?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-award"></i></td>
                        <td><strong><?php echo $spText['common']['Page Authority'] ?? 'Page Authority'?></strong></td>
                        <td><?php echo $spTextSA['Excellent (PA >= 50)'] ?? 'Excellent (PA >= 50)'?></td>
                        <td class="score-positive">+10</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-award"></i></td>
                        <td><strong><?php echo $spText['common']['Page Authority'] ?? 'Page Authority'?></strong></td>
                        <td><?php echo $spTextSA['Very Good (PA >= 30)'] ?? 'Very Good (PA >= 30)'?></td>
                        <td class="score-positive">+5</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-award"></i></td>
                        <td><strong><?php echo $spText['common']['Page Authority'] ?? 'Page Authority'?></strong></td>
                        <td><?php echo $spTextSA['Moderate (PA >= 20)'] ?? 'Moderate (PA >= 20)'?></td>
                        <td class="score-positive">+2</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-award"></i></td>
                        <td><strong><?php echo $spText['common']['Page Authority'] ?? 'Page Authority'?></strong></td>
                        <td><?php echo $spTextSA['Low (PA > 0)'] ?? 'Low (PA > 0)'?></td>
                        <td class="score-positive">+1</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-award"></i></td>
                        <td><strong><?php echo $spText['common']['Page Authority'] ?? 'Page Authority'?></strong></td>
                        <td><?php echo $spTextSA['No authority'] ?? 'No authority'?></td>
                        <td class="score-negative">-1</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-link"></i></td>
                        <td><strong><?php echo $spTextSA['Backlinks'] ?? 'Backlinks'?></strong></td>
                        <td><?php echo $spTextSA['Excellent (>= 100 backlinks)'] ?? 'Excellent (>= 100 backlinks)'?></td>
                        <td class="score-positive">+3</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-link"></i></td>
                        <td><strong><?php echo $spTextSA['Backlinks'] ?? 'Backlinks'?></strong></td>
                        <td><?php echo $spTextSA['Good (>= 10 backlinks)'] ?? 'Good (>= 10 backlinks)'?></td>
                        <td class="score-positive">+2</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-link"></i></td>
                        <td><strong><?php echo $spTextSA['Backlinks'] ?? 'Backlinks'?></strong></td>
                        <td><?php echo $spTextSA['Some backlinks'] ?? 'Some backlinks'?></td>
                        <td class="score-positive">+1</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-link"></i></td>
                        <td><strong><?php echo $spTextSA['Backlinks'] ?? 'Backlinks'?></strong></td>
                        <td><?php echo $spTextSA['No backlinks'] ?? 'No backlinks'?></td>
                        <td class="score-neutral">0</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="score-section">
            <div class="score-section-title">
                <i class="fas fa-search"></i> <?php echo $spTextSA['Search Engine Visibility'] ?? 'Search Engine Visibility'?>
            </div>
            <table class="score-table">
                <thead>
                    <tr>
                        <th style="width: 5%;"></th>
                        <th style="width: 25%;"><?php echo $spText['label']['Factor'] ?? 'Factor'?></th>
                        <th style="width: 40%;"><?php echo $spText['label']['Condition'] ?? 'Condition'?></th>
                        <th style="width: 15%;"><?php echo $spText['label']['Score'] ?? 'Score'?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="factor-icon"><i class="fab fa-google"></i></td>
                        <td><strong><?php echo $spTextSA['Indexed'] ?? 'Indexed'?></strong></td>
                        <td><?php echo $spTextSA['Page is indexed in Google'] ?? 'Page is indexed in Google'?></td>
                        <td class="score-positive">+4</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fab fa-google"></i></td>
                        <td><strong><?php echo $spTextSA['Indexed'] ?? 'Indexed'?></strong></td>
                        <td><?php echo $spTextSA['Page is not indexed'] ?? 'Page is not indexed'?></td>
                        <td class="score-negative">-2</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-robot"></i></td>
                        <td><strong><?php echo $spTextSA['Robots.txt'] ?? 'Robots.txt'?></strong></td>
                        <td><?php echo $spTextSA['Page is allowed by robots.txt'] ?? 'Page is allowed by robots.txt'?></td>
                        <td class="score-positive">+3</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-robot"></i></td>
                        <td><strong><?php echo $spTextSA['Robots.txt'] ?? 'Robots.txt'?></strong></td>
                        <td><?php echo $spTextSA['Page is blocked by robots.txt'] ?? 'Page is blocked by robots.txt'?></td>
                        <td class="score-negative">-5</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-brain"></i></td>
                        <td><strong><?php echo $spTextSA['AI Bot Access'] ?? 'AI Bot Access'?></strong></td>
                        <td><?php echo $spTextSA['AI robots allowed'] ?? 'AI robots allowed'?></td>
                        <td class="score-positive">+3</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-brain"></i></td>
                        <td><strong><?php echo $spTextSA['AI Bot Access'] ?? 'AI Bot Access'?></strong></td>
                        <td><?php echo $spTextSA['AI robots blocked'] ?? 'AI robots blocked'?></td>
                        <td class="score-negative">-2</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="score-section">
            <div class="score-section-title">
                <i class="fas fa-rocket"></i> <?php echo $spTextSA['Modern SEO Features'] ?? 'Modern SEO Features'?>
            </div>
            <table class="score-table">
                <thead>
                    <tr>
                        <th style="width: 5%;"></th>
                        <th style="width: 25%;"><?php echo $spText['label']['Factor'] ?? 'Factor'?></th>
                        <th style="width: 40%;"><?php echo $spText['label']['Condition'] ?? 'Condition'?></th>
                        <th style="width: 15%;"><?php echo $spText['label']['Score'] ?? 'Score'?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-mobile-alt"></i></td>
                        <td><strong><?php echo $spTextSA['Mobile Friendly'] ?? 'Mobile Friendly'?></strong></td>
                        <td><?php echo $spTextSA['Page has proper viewport meta tag'] ?? 'Page has proper viewport meta tag'?></td>
                        <td class="score-positive">+4</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-mobile-alt"></i></td>
                        <td><strong><?php echo $spTextSA['Mobile Friendly'] ?? 'Mobile Friendly'?></strong></td>
                        <td><?php echo $spTextSA['Page is not mobile-friendly'] ?? 'Page is not mobile-friendly'?></td>
                        <td class="score-negative">-3</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-lock"></i></td>
                        <td><strong><?php echo $spTextSA['HTTPS Security'] ?? 'HTTPS Security'?></strong></td>
                        <td><?php echo $spTextSA['Page served over HTTPS'] ?? 'Page served over HTTPS'?></td>
                        <td class="score-positive">+3</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-lock"></i></td>
                        <td><strong><?php echo $spTextSA['HTTPS Security'] ?? 'HTTPS Security'?></strong></td>
                        <td><?php echo $spTextSA['Page not secure (HTTP)'] ?? 'Page not secure (HTTP)'?></td>
                        <td class="score-negative">-3</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fab fa-facebook"></i></td>
                        <td><strong><?php echo $spTextSA['Open Graph Tags'] ?? 'Open Graph Tags'?></strong></td>
                        <td><?php echo $spTextSA['OG tags present'] ?? 'OG tags present'?></td>
                        <td class="score-positive">+2</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fab fa-facebook"></i></td>
                        <td><strong><?php echo $spTextSA['Open Graph Tags'] ?? 'Open Graph Tags'?></strong></td>
                        <td><?php echo $spTextSA['OG tags missing'] ?? 'OG tags missing'?></td>
                        <td class="score-negative">-1</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fab fa-twitter"></i></td>
                        <td><strong><?php echo $spTextSA['Twitter Cards'] ?? 'Twitter Cards'?></strong></td>
                        <td><?php echo $spTextSA['Twitter Card tags present'] ?? 'Twitter Card tags present'?></td>
                        <td class="score-positive">+2</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fab fa-twitter"></i></td>
                        <td><strong><?php echo $spTextSA['Twitter Cards'] ?? 'Twitter Cards'?></strong></td>
                        <td><?php echo $spTextSA['Twitter Card tags missing'] ?? 'Twitter Card tags missing'?></td>
                        <td class="score-negative">-1</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="score-section">
            <div class="score-section-title">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $spTextSA['Technical Issues'] ?? 'Technical Issues'?>
            </div>
            <table class="score-table">
                <thead>
                    <tr>
                        <th style="width: 5%;"></th>
                        <th style="width: 25%;"><?php echo $spText['label']['Factor'] ?? 'Factor'?></th>
                        <th style="width: 40%;"><?php echo $spText['label']['Condition'] ?? 'Condition'?></th>
                        <th style="width: 15%;"><?php echo $spText['label']['Score'] ?? 'Score'?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-unlink"></i></td>
                        <td><strong><?php echo $spText['label']['Brocken'] ?? 'Broken Link'?></strong></td>
                        <td><?php echo $spTextSA['Page is broken (4xx/5xx error)'] ?? 'Page is broken (4xx/5xx error)'?></td>
                        <td class="score-negative">-3</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-link"></i></td>
                        <td><strong><?php echo $spTextSA['Total Links'] ?? 'Total Links'?></strong></td>
                        <td><?php echo $spTextSA['Too many links (>= 150)'] ?? 'Too many links (>= 150)'?></td>
                        <td class="score-negative">-2</td>
                    </tr>
                    <tr>
                        <td class="factor-icon"><i class="fas fa-link"></i></td>
                        <td><strong><?php echo $spTextSA['Total Links'] ?? 'Total Links'?></strong></td>
                        <td><?php echo $spTextSA['Slightly high links (100-149)'] ?? 'Slightly high links (100-149)'?></td>
                        <td class="score-negative">-1</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; margin-top: 20px;">
            <h4 style="margin: 0 0 10px 0; color: #333;"><?php echo $spTextSA['Maximum Possible Score'] ?? 'Maximum Possible Score'?></h4>
            <div class="max-score-badge">
                <i class="fas fa-star"></i> 38 <?php echo $spText['label']['Points'] ?? 'Points'?> = 100%
            </div>
            <p style="margin: 15px 0 0 0; color: #666; font-size: 13px;">
                <?php echo $spTextSA['Score is displayed as a percentage'] ?? 'Score is displayed as a percentage of the maximum possible score'?>
            </p>
        </div>
    </div>
</div>

<table class="actionSec mt-2">
    <tr>
        <td>
            <a onclick="scriptDoLoad('siteauditor.php', 'content')" href="javascript:void(0);" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> <?php echo $spText['button']['Back'] ?? 'Back'?>
            </a>
        </td>
    </tr>
</table>
