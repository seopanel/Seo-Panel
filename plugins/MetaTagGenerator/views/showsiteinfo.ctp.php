<?php
// Helper to echo `selected`/`checked` only when $val matches the current
// value - every <select> here used to have zero such logic at all (none
// of rating/distribution/robots/revisit-after/twitter_card ever
// persisted anything to compare against), so a validation-failure retry
// silently reset all five to blank. Centralized here rather than
// repeated five times. function_exists() guard - this view can be
// included more than once within one long-running PHP process (e.g. a
// validation-failure retry re-renders it within the same request), and a
// bare `function mtgSel(...)` would fatal with "cannot redeclare" on the
// second include.
if (!function_exists('mtgSel')) {
    function mtgSel($current, $val) { return ((string)$current === (string)$val) ? ' selected' : ''; }
}
?>
<div class="mtg-form">
    <div class="mtg-form-header">
        <i class="fas fa-tags"></i>
        <h3>Meta Tag Generator</h3>
    </div>

    <form id="editSubmitInfo">
    <input type="hidden" name="website_id" value="<?php echo intval($websiteInfo['website_id'])?>"/>

    <?php if (!empty($localAiAvailable)) { ?>
    <div class="mtg-ai-suggest">
        <button type="button" id="mtgAiSuggestBtn" class="btn btn-sm btn-outline-primary" onclick="mtgSuggestWithAI(<?php echo intval($websiteInfo['website_id'])?>)">
            <i class="fas fa-magic"></i> Suggest Title &amp; Description with AI
        </button>
        <p>Drafts a title/description below using your on-premise Local AI (Ollama) - nothing is sent to a third party. Review before using.</p>
    </div>
    <script type="text/javascript">
    function mtgSuggestWithAI(websiteId) {
        var btn = document.getElementById('mtgAiSuggestBtn');
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        $.ajax({
            url: '<?php echo PLUGIN_SCRIPT_URL; ?>&action=suggestMetaTags&website_id=' + websiteId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.ok) {
                    if (response.title) { document.querySelector('#editSubmitInfo input[name="title"]').value = response.title; mtgUpdateCount('title'); }
                    if (response.description) { document.querySelector('#editSubmitInfo textarea[name="description"]').value = response.description; mtgUpdateCount('description'); }
                } else {
                    alert(response.error || 'Could not generate a suggestion.');
                }
            },
            error: function() {
                alert('Could not generate a suggestion.');
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }
    </script>
    <?php } ?>

    <div class="mtg-form-row">
        <label>Website Title</label>
        <input type="text" name="title" id="mtgTitleInput" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['title']))?>" oninput="mtgUpdateCount('title')">
        <?php echo $errMsg['title']?>
        <p class="mtg-form-hint"><span class="mtg-count" id="mtgTitleCount">0</span>/100 characters</p>
    </div>

    <div class="mtg-form-row">
        <label>Canonical URL</label>
        <input type="text" name="canonical_url" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['canonical_url'] ?? ''))?>">
        <p class="mtg-form-hint">Defaults to this website's own URL. Tells search engines which URL is the "real" one for duplicate-content purposes - clear it if this page shouldn't self-canonicalize.</p>
    </div>

    <div class="mtg-form-row">
        <label>Website Description</label>
        <textarea name="description" id="mtgDescInput" oninput="mtgUpdateCount('description')"><?php echo htmlspecialchars(stripslashes($websiteInfo['description']))?></textarea>
        <?php echo $errMsg['description']?>
        <p class="mtg-form-hint"><span class="mtg-count" id="mtgDescCount">0</span>/255 characters</p>
    </div>

    <div class="mtg-form-row">
        <div class="mtg-form-check">
            <input type="hidden" name="viewport" value="0">
            <input type="checkbox" id="mtgViewport" name="viewport" value="1" <?php echo (!isset($websiteInfo['viewport']) || !empty($websiteInfo['viewport'])) ? "checked='checked'" : ""?>>
            <label for="mtgViewport">Include Mobile Viewport Tag</label>
        </div>
        <p class="mtg-form-hint">Include the standard responsive-design viewport tag (recommended for virtually every page).</p>
    </div>

    <div class="mtg-form-row">
        <label>Website Keywords</label>
        <textarea name="keywords" id="mtgKeywordsInput" oninput="mtgUpdateCount('keywords')"><?php echo htmlspecialchars(stripslashes($websiteInfo['keywords']))?></textarea>
        <?php echo $errMsg['keywords']?>
        <p class="mtg-form-hint"><span class="mtg-count" id="mtgKeywordsCount">0</span>/12 unique search terms, separated by a comma and space</p>
    </div>

    <div class="mtg-form-row-pair">
        <div class="mtg-form-row">
            <label>Author</label>
            <input type="text" name="owner_name" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['owner_name']))?>" placeholder="Your Name/Company">
        </div>
        <div class="mtg-form-row">
            <label>Email</label>
            <input type="text" name="owner_email" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['owner_email']))?>" placeholder="support@yoursite.com">
        </div>
    </div>

    <div class="mtg-form-row-pair">
        <div class="mtg-form-row">
            <label>Copyright</label>
            <input type="text" name="copyright" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['copyright'] ?? ''))?>" placeholder="Copyright YourCompany - <?php echo date('Y')?>">
        </div>
        <div class="mtg-form-row">
            <label>Expires</label>
            <input type="text" name="expires" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['expires'] ?? ''))?>">
        </div>
    </div>

    <div class="mtg-form-row-pair">
        <div class="mtg-form-row">
            <label>Language</label>
            <?php echo $this->render('language/languageselectbox', 'ajax'); ?>
        </div>
        <div class="mtg-form-row">
            <label>Charset</label>
            <?php echo $this->pluginRender('charsetselectbox', 'ajax'); ?>
        </div>
    </div>

    <div class="mtg-form-section">Search Engine Directives</div>

    <div class="mtg-form-row-pair">
        <div class="mtg-form-row">
            <label>Rating</label>
            <?php $curRating = $websiteInfo['rating'] ?? ''; ?>
            <select name="rating">
                <option value=""></option>
                <option value="General"<?php echo mtgSel($curRating, 'General')?>>General</option>
                <option value="Mature"<?php echo mtgSel($curRating, 'Mature')?>>Mature</option>
                <option value="Restricted"<?php echo mtgSel($curRating, 'Restricted')?>>Restricted</option>
            </select>
        </div>
        <div class="mtg-form-row">
            <label>Distribution</label>
            <?php $curDist = $websiteInfo['distribution'] ?? ''; ?>
            <select name="distribution">
                <option value=""></option>
                <option value="Global"<?php echo mtgSel($curDist, 'Global')?>>Global</option>
                <option value="Local"<?php echo mtgSel($curDist, 'Local')?>>Local</option>
            </select>
        </div>
    </div>

    <div class="mtg-form-row-pair">
        <div class="mtg-form-row">
            <label>Robots</label>
            <?php $curRobots = $websiteInfo['robots'] ?? ''; ?>
            <select name="robots" title="NOINDEX,NOFOLLOW keeps a page out of search results entirely and stops bots following its links - the opposite of INDEX,FOLLOW, the default when left blank">
                <option value=""></option>
                <option value="INDEX,FOLLOW"<?php echo mtgSel($curRobots, 'INDEX,FOLLOW')?>>INDEX,FOLLOW</option>
                <option value="INDEX,NOFOLLOW"<?php echo mtgSel($curRobots, 'INDEX,NOFOLLOW')?>>INDEX,NOFOLLOW</option>
                <option value="NOINDEX,FOLLOW"<?php echo mtgSel($curRobots, 'NOINDEX,FOLLOW')?>>NOINDEX,FOLLOW</option>
                <option value="NOINDEX,NOFOLLOW"<?php echo mtgSel($curRobots, 'NOINDEX,NOFOLLOW')?>>NOINDEX,NOFOLLOW</option>
            </select>
        </div>
        <div class="mtg-form-row">
            <label>Revisit-after</label>
            <?php $curRevisit = $websiteInfo['revisit_after'] ?? ''; ?>
            <select name="revisit-after">
                <option value=""></option>
                <option value="1 Day"<?php echo mtgSel($curRevisit, '1 Day')?>>1 Day</option>
                <option value="7 Days"<?php echo mtgSel($curRevisit, '7 Days')?>>7 Days</option>
                <option value="31 Days"<?php echo mtgSel($curRevisit, '31 Days')?>>31 Days</option>
                <option value="180 Days"<?php echo mtgSel($curRevisit, '180 Days')?>>180 Days</option>
                <option value="365 Days"<?php echo mtgSel($curRevisit, '365 Days')?>>365 Days</option>
            </select>
        </div>
    </div>

    <div class="mtg-form-section">Social Sharing (Open Graph / Twitter Card)</div>

    <div class="mtg-form-row">
        <label>Share Title</label>
        <input type="text" name="og_title" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['og_title'] ?? ''))?>">
        <p class="mtg-form-hint">Shown when this page is shared on social media/chat apps. Leave blank to reuse the Website Title above.</p>
    </div>

    <div class="mtg-form-row">
        <label>Share Description</label>
        <textarea name="og_description"><?php echo htmlspecialchars(stripslashes($websiteInfo['og_description'] ?? ''))?></textarea>
        <p class="mtg-form-hint">Leave blank to reuse the Website Description above.</p>
    </div>

    <div class="mtg-form-row-pair">
        <div class="mtg-form-row">
            <label>Share Image URL</label>
            <input type="text" name="og_image" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['og_image'] ?? ''))?>" placeholder="https://yoursite.com/share-image.jpg">
            <p class="mtg-form-hint">At least 1200x630px recommended.</p>
        </div>
        <div class="mtg-form-row">
            <label>Canonical Share URL</label>
            <input type="text" name="og_url" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['og_url'] ?? ''))?>">
            <p class="mtg-form-hint">Optional - the canonical URL this page should be credited to when shared.</p>
        </div>
    </div>

    <div class="mtg-form-row">
        <label>Twitter Card Type</label>
        <?php $curTwitter = $websiteInfo['twitter_card'] ?? ''; ?>
        <select name="twitter_card">
            <option value="">-- None --</option>
            <option value="summary"<?php echo mtgSel($curTwitter, 'summary')?>>Summary</option>
            <option value="summary_large_image"<?php echo mtgSel($curTwitter, 'summary_large_image')?>>Summary with Large Image</option>
        </select>
    </div>

    <div class="mtg-form-actions">
        <a onclick="<?php echo pluginGETMethod(); ?>" href="javascript:void(0);" class="btn btn-warning">
            <?php echo $spText['button']['Cancel']?>
        </a>
        <?php /* targets 'content', not 'subcontent' - this view is reached
           two different ways: via index.ctp.php's own search form (which
           loads it INTO a #subcontent div it owns) and via Audit's "Fix"
           link (pluginGETMethod()'s default area is 'content' - there's no
           #subcontent anywhere in that path at all). 'subcontent' only
           worked for the first path; targeting 'content' (which always
           exists, set up by the core plugin dispatch) works for both -
           previously, clicking Proceed via the Fix link silently did
           nothing (document.getElementById('subcontent') is null), which
           compounded the "doesn't persist" bug: that specific entry point
           couldn't even reach createmetatag() at all. */ ?>
        <a onclick="<?php echo pluginPOSTMethod('editSubmitInfo', 'content', 'action=createmetatag'); ?>" href="javascript:void(0);" class="btn btn-primary">
            <?php echo $spText['button']['Proceed']?>
        </a>
    </div>
    </form>
</div>

<script type="text/javascript">
function mtgUpdateCount(field) {
    if (field === 'title') {
        var len = document.getElementById('mtgTitleInput').value.length;
        var el = document.getElementById('mtgTitleCount');
        el.innerText = len;
        el.className = 'mtg-count' + (len > 100 ? ' over-limit' : '');
    } else if (field === 'description') {
        var len = document.getElementById('mtgDescInput').value.length;
        var el = document.getElementById('mtgDescCount');
        el.innerText = len;
        el.className = 'mtg-count' + (len > 255 ? ' over-limit' : '');
    } else if (field === 'keywords') {
        var raw = document.getElementById('mtgKeywordsInput').value;
        var count = raw.split(',').map(function(s) { return s.trim(); }).filter(function(s) { return s.length > 0; }).length;
        var el = document.getElementById('mtgKeywordsCount');
        el.innerText = count;
        el.className = 'mtg-count' + (count > 12 ? ' over-limit' : '');
    }
}
mtgUpdateCount('title');
mtgUpdateCount('description');
mtgUpdateCount('keywords');
</script>
