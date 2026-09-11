<?php echo showSectionHead($spTextTools['Skipped Directories']); ?>
<div class="card mb-3">
    <div class="card-body">
        <form id='search_form'>
            <div class="row">
                <div class="col-md-4">
                    <label class="font-weight-bold"><?php echo $spText['common']['Name']?>:</label>
                    <input type="text" name="search_name" value="<?php echo htmlentities($searchInfo['search_name'], ENT_QUOTES)?>" onblur="<?php echo $onChange?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="font-weight-bold"><?php echo $spText['common']['Website']?>:</label>
                    <?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="javascript:void(0);" onclick="<?php echo $onChange?>" class="btn btn-secondary btn-block"><?php echo $spText['button']['Search']?></a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
if(empty($websiteId)){
    showErrorMsg($spText['common']['nowebsites'].'!');
}
?>

<div id='subcontent'>
<?php echo $pagingDiv?>
<table class="list">
    <tr class="listHead">
        <td class="text-center" style="width:5%"><?php echo $spText['common']['Id']?></td>
        <td><?php echo $spText['common']['Directory']?></td>
        <td class="text-center">PR</td>
        <td class="text-center" width="18%"><?php echo $spText['common']['Action']?></td>
    </tr>
    <?php
    $colCount = 4;
    if(count($list) > 0) {
        foreach($list as $i => $listInfo) {
            $argStr = "sec=unskip&id={$listInfo['id']}&pageno=$pageNo&website_id=$websiteId&search_name=".$searchInfo['search_name'];
            $includeLink = "<a href='javascript:void(0);' onclick=\"scriptDoLoad('directories.php', 'content', '$argStr')\" class='btn btn-primary btn-sm'>".$spTextDir['Add back to directory list']."</a>";
            ?>
            <tr>
                <td class="text-center"><?php echo $i + 1?></td>
                <td>
                    <a href="<?php echo $listInfo['submit_url']?>" target="_blank"><?php echo $listInfo['domain']?></a>
                </td>
                <td class="text-center"><?php echo !empty($listInfo['pagerank']) ? $listInfo['pagerank'] : '&mdash;'?></td>
                <td class="text-center"><?php echo $includeLink?></td>
            </tr>
            <?php
        }
    }else{
        echo showNoRecordsList($colCount-2);
    }
    ?>
</table>
</div>
