<?php
$mtgCharsetOptions = ['GB2312', 'US-ASCII', 'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4',
	'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-2022-JP', 'ISO-2022-JP-2',
	'ISO-2022-KR', 'SHIFT_JIS', 'EUC-KR', 'BIG5', 'KOI8-R', 'KSC_5601', 'HZ-GB-2312', 'JIS_X0208', 'UTF-8', 'other'];
$mtgCurCharset = $websiteInfo['meta_charset'] ?? '';
?>
<select name="charset" class="custom-select">
	<option value=""></option>
	<?php foreach ($mtgCharsetOptions as $mtgCharsetOpt) { ?>
	<option<?php echo ($mtgCurCharset === $mtgCharsetOpt) ? ' selected' : ''?>><?php echo $mtgCharsetOpt?></option>
	<?php } ?>
</select>
