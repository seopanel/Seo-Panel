<div class="dashboard-no-websites">
    <div class="empty-state text-center py-5">
        <div class="empty-state-icon mb-4">
            <i class="fas fa-globe fa-5x text-muted"></i>
        </div>
        <h3 class="mb-3"><?php echo $_SESSION['text']['common']['nowebsites']?></h3>
        <p class="text-muted mb-4">
            <?php echo $spTextWebsite['Get started by adding your first website to track its SEO performance.'] ?? 'Get started by adding your first website to track its SEO performance.'?>
        </p>
        <a href="<?php echo SP_WEBPATH?>/admin-panel.php?sec=newweb" class="btn btn-primary btn-lg">
            <i class="fas fa-plus-circle"></i> <?php echo $spTextWebsite['Create Website'] ?? 'Create Website'?>
        </a>
    </div>
</div>

<style>
.dashboard-no-websites {
    background: #fff;
    border-radius: 8px;
    padding: 60px 20px;
    margin: 20px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.empty-state-icon {
    opacity: 0.5;
}
</style>
