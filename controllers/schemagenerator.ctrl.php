<?php

/**
 * AI Schema Markup Generator - turns a simple form into valid schema.org
 * (JSON-LD) markup for the 4 types most directly tied to AI answer-engine
 * citation: Organization, LocalBusiness, Article, FAQPage (the most
 * heavily used type by Google AI Overviews and chat-based answer engines
 * specifically). Site Auditor already flags whether a page HAS structured
 * data at all (see controllers/components/auditorcomponent.php's
 * has_structured_data check) - this is the tool that actually produces
 * it, rather than just detecting its absence.
 *
 * Entirely self-hosted / no third-party calls - unlike AI Perception
 * Check, generating markup here never sends anything anywhere. One saved
 * config per (website, schema type) in schema_markup - field_data is the
 * raw form values (re-editable later); the JSON-LD itself is always
 * rebuilt from field_data on demand via __buildJsonLd(), a pure function
 * with no DB/session dependency, kept separate specifically so it can be
 * unit tested directly against schema.org's exact required shapes.
 */
class SchemaGeneratorController extends Controller {

	var $layout = 'ajax';

	const SCHEMA_TYPES = ['Organization', 'LocalBusiness', 'Article', 'FAQPage'];

	// GET schema-generator.php - pick website + type, show the form
	// (prefilled from a saved config if one exists, else from the
	// website's own already-known name/url/title/description as a real
	// convenience rather than a blank form) and a live JSON-LD preview.
	function showGenerator($info) {
		$userId = isLoggedIn();
		$websiteController = new WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$this->set('noWebsites', empty($websiteList));

		$websiteId = !empty($info['website_id']) ? intval($info['website_id']) : 0;
		if (!empty($websiteId) && !isAdmin() && !in_array($websiteId, array_column($websiteList, 'id'))) {
			$websiteId = 0;
		}
		if (empty($websiteId) && !empty($websiteList)) {
			$websiteId = intval($websiteList[0]['id']);
		}
		$this->set('websiteId', $websiteId);

		$schemaType = (!empty($info['schema_type']) && in_array($info['schema_type'], self::SCHEMA_TYPES, true))
			? $info['schema_type'] : self::SCHEMA_TYPES[0];
		$this->set('schemaType', $schemaType);
		$this->set('schemaTypes', self::SCHEMA_TYPES);

		$fieldData = [];
		$savedTypes = [];
		if (!empty($websiteId)) {
			$stored = $this->__getStoredFieldData($websiteId, $schemaType);
			$fieldData = !empty($stored) ? $stored : $this->__getDefaultFieldData($websiteId, $schemaType);
			$savedTypes = array_column($this->db->select("SELECT schema_type FROM schema_markup WHERE website_id=" . intval($websiteId)), 'schema_type');
		}
		$this->set('fieldData', $fieldData);
		$this->set('savedTypes', $savedTypes);

		$jsonLd = !empty($fieldData) ? $this->__buildJsonLd($schemaType, $fieldData) : null;
		// JSON_HEX_TAG escapes < and > (as </>) so a field value
		// that happens to contain "</script>" (e.g. a pasted description)
		// can never prematurely close the <script type="application/
		// ld+json"> block this markup is meant to be pasted into - this
		// generator's whole job is to hand back something safe to paste
		// as-is, not something that needs its own escaping afterward
		$this->set('jsonLdOutput', $jsonLd !== null ? json_encode($jsonLd, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) : null);

		$this->set('spTextAIV', $this->getLanguageTexts('aivisibility', $_SESSION['lang_code']));
		$this->render('schemagenerator/generator');
	}

	// POST schema-generator.php?sec=generate - save this type's field
	// values (upsert - one row per website+type) and re-show the
	// generator with the freshly built JSON-LD
	function generateSchema($info) {
		$userId = isLoggedIn();
		$websiteId = intval($info['website_id'] ?? 0);
		$schemaType = $info['schema_type'] ?? '';

		$websiteController = new WebsiteController();
		$ownedIds = array_column($websiteController->__getAllWebsites($userId, true), 'id');
		if (empty($websiteId) || !in_array($schemaType, self::SCHEMA_TYPES, true) || (!isAdmin() && !in_array($websiteId, $ownedIds))) {
			showErrorMsg($_SESSION['text']['label']['Access denied']);
			return;
		}

		$fieldData = $this->__extractFieldData($schemaType, $info);
		$fieldDataJson = addslashes(json_encode($fieldData));
		$schemaTypeSql = addslashes($schemaType);
		$now = date('Y-m-d H:i:s');
		$this->db->query("INSERT INTO schema_markup (website_id, schema_type, field_data, updated_at)
			VALUES ($websiteId, '$schemaTypeSql', '$fieldDataJson', '$now')
			ON DUPLICATE KEY UPDATE field_data='$fieldDataJson', updated_at='$now'");

		$this->showGenerator(['website_id' => $websiteId, 'schema_type' => $schemaType]);
	}

	// POST schema-generator.php?sec=remove - deletes a saved config,
	// reverting the form back to smart defaults on next view
	function removeSchema($info) {
		$userId = isLoggedIn();
		$websiteId = intval($info['website_id'] ?? 0);
		$schemaType = $info['schema_type'] ?? '';

		$websiteController = new WebsiteController();
		$ownedIds = array_column($websiteController->__getAllWebsites($userId, true), 'id');
		if (empty($websiteId) || (!isAdmin() && !in_array($websiteId, $ownedIds))) {
			showErrorMsg($_SESSION['text']['label']['Access denied']);
			return;
		}

		$this->db->query("DELETE FROM schema_markup WHERE website_id=$websiteId AND schema_type='" . addslashes($schemaType) . "'");
		$this->showGenerator(['website_id' => $websiteId, 'schema_type' => $schemaType]);
	}

	function __getStoredFieldData($websiteId, $schemaType) {
		$row = $this->db->select("SELECT field_data FROM schema_markup WHERE website_id=" . intval($websiteId) . " AND schema_type='" . addslashes($schemaType) . "'", true);
		if (empty($row['field_data'])) return [];
		$decoded = json_decode($row['field_data'], true);
		return is_array($decoded) ? $decoded : [];
	}

	// smart defaults pulled from the website's own already-known info -
	// real convenience value even before any Local-AI-assisted drafting
	// is layered on top later
	function __getDefaultFieldData($websiteId, $schemaType) {
		$website = $this->dbHelper->getRow('websites', "id=" . intval($websiteId));
		if (empty($website)) return [];

		switch ($schemaType) {
			case 'Organization':
				return [
					'name' => $website['name'] ?? '',
					'url' => $website['url'] ?? '',
					'description' => $website['description'] ?? '',
					'logo' => '',
					'sameAs' => '',
				];
			case 'LocalBusiness':
				return [
					'name' => $website['name'] ?? '',
					'url' => $website['url'] ?? '',
					'description' => $website['description'] ?? '',
					'image' => '',
					'street_address' => '',
					'locality' => '',
					'region' => '',
					'postal_code' => '',
					'country' => '',
					'telephone' => '',
					'price_range' => '',
				];
			case 'Article':
				return [
					'headline' => $website['title'] ?? '',
					'description' => $website['description'] ?? '',
					'image' => '',
					'author_name' => '',
					'date_published' => date('Y-m-d'),
					'date_modified' => date('Y-m-d'),
					'publisher_name' => $website['name'] ?? '',
					'publisher_logo' => '',
				];
			case 'FAQPage':
				return ['pairs' => [['question' => '', 'answer' => '']]];
			default:
				return [];
		}
	}

	// pulls raw POST fields into the same field_data shape
	// __getDefaultFieldData() uses, so both feed __buildJsonLd() identically
	function __extractFieldData($schemaType, $info) {
		switch ($schemaType) {
			case 'Organization':
				return [
					'name' => trim($info['name'] ?? ''),
					'url' => trim($info['url'] ?? ''),
					'description' => trim($info['description'] ?? ''),
					'logo' => trim($info['logo'] ?? ''),
					'sameAs' => trim($info['sameAs'] ?? ''),
				];
			case 'LocalBusiness':
				return [
					'name' => trim($info['name'] ?? ''),
					'url' => trim($info['url'] ?? ''),
					'description' => trim($info['description'] ?? ''),
					'image' => trim($info['image'] ?? ''),
					'street_address' => trim($info['street_address'] ?? ''),
					'locality' => trim($info['locality'] ?? ''),
					'region' => trim($info['region'] ?? ''),
					'postal_code' => trim($info['postal_code'] ?? ''),
					'country' => trim($info['country'] ?? ''),
					'telephone' => trim($info['telephone'] ?? ''),
					'price_range' => trim($info['price_range'] ?? ''),
				];
			case 'Article':
				return [
					'headline' => trim($info['headline'] ?? ''),
					'description' => trim($info['description'] ?? ''),
					'image' => trim($info['image'] ?? ''),
					'author_name' => trim($info['author_name'] ?? ''),
					'date_published' => trim($info['date_published'] ?? ''),
					'date_modified' => trim($info['date_modified'] ?? ''),
					'publisher_name' => trim($info['publisher_name'] ?? ''),
					'publisher_logo' => trim($info['publisher_logo'] ?? ''),
				];
			case 'FAQPage':
				$questions = $info['question'] ?? [];
				$answers = $info['answer'] ?? [];
				$pairs = [];
				foreach ($questions as $i => $q) {
					$q = trim($q);
					$a = trim($answers[$i] ?? '');
					if ($q !== '' && $a !== '') {
						$pairs[] = ['question' => $q, 'answer' => $a];
					}
				}
				return ['pairs' => $pairs];
			default:
				return [];
		}
	}

	// Pure function: schema type + field data -> the PHP array structure
	// ready for json_encode(). No DB/session dependency, deliberately -
	// this is what gets unit tested directly against schema.org's exact
	// required shape for each type (spTests/tests/php/
	// schema_generator_test.php). Every optional field is only included
	// when non-empty, so an unfinished/partially-filled form still
	// produces valid, honest markup rather than empty-string fields.
	function __buildJsonLd($schemaType, $fieldData) {
		switch ($schemaType) {
			case 'Organization':
				$out = [
					'@context' => 'https://schema.org',
					'@type' => 'Organization',
					'name' => $fieldData['name'] ?? '',
					'url' => $fieldData['url'] ?? '',
				];
				if (!empty($fieldData['description'])) $out['description'] = $fieldData['description'];
				if (!empty($fieldData['logo'])) $out['logo'] = $fieldData['logo'];
				if (!empty($fieldData['sameAs'])) {
					$links = array_values(array_filter(array_map('trim', explode(',', $fieldData['sameAs']))));
					if (!empty($links)) $out['sameAs'] = $links;
				}
				return $out;

			case 'LocalBusiness':
				$out = [
					'@context' => 'https://schema.org',
					'@type' => 'LocalBusiness',
					'name' => $fieldData['name'] ?? '',
				];
				if (!empty($fieldData['url'])) $out['url'] = $fieldData['url'];
				if (!empty($fieldData['description'])) $out['description'] = $fieldData['description'];
				if (!empty($fieldData['image'])) $out['image'] = $fieldData['image'];
				$address = array_filter([
					'streetAddress' => $fieldData['street_address'] ?? '',
					'addressLocality' => $fieldData['locality'] ?? '',
					'addressRegion' => $fieldData['region'] ?? '',
					'postalCode' => $fieldData['postal_code'] ?? '',
					'addressCountry' => $fieldData['country'] ?? '',
				]);
				if (!empty($address)) {
					$out['address'] = array_merge(['@type' => 'PostalAddress'], $address);
				}
				if (!empty($fieldData['telephone'])) $out['telephone'] = $fieldData['telephone'];
				if (!empty($fieldData['price_range'])) $out['priceRange'] = $fieldData['price_range'];
				return $out;

			case 'Article':
				$out = [
					'@context' => 'https://schema.org',
					'@type' => 'Article',
					'headline' => $fieldData['headline'] ?? '',
				];
				if (!empty($fieldData['description'])) $out['description'] = $fieldData['description'];
				if (!empty($fieldData['image'])) $out['image'] = $fieldData['image'];
				if (!empty($fieldData['author_name'])) {
					$out['author'] = ['@type' => 'Person', 'name' => $fieldData['author_name']];
				}
				if (!empty($fieldData['date_published'])) $out['datePublished'] = $fieldData['date_published'];
				if (!empty($fieldData['date_modified'])) $out['dateModified'] = $fieldData['date_modified'];
				if (!empty($fieldData['publisher_name'])) {
					$publisher = ['@type' => 'Organization', 'name' => $fieldData['publisher_name']];
					if (!empty($fieldData['publisher_logo'])) {
						$publisher['logo'] = ['@type' => 'ImageObject', 'url' => $fieldData['publisher_logo']];
					}
					$out['publisher'] = $publisher;
				}
				return $out;

			case 'FAQPage':
				$mainEntity = [];
				foreach (($fieldData['pairs'] ?? []) as $pair) {
					if (empty($pair['question']) || empty($pair['answer'])) continue;
					$mainEntity[] = [
						'@type' => 'Question',
						'name' => $pair['question'],
						'acceptedAnswer' => ['@type' => 'Answer', 'text' => $pair['answer']],
					];
				}
				return [
					'@context' => 'https://schema.org',
					'@type' => 'FAQPage',
					'mainEntity' => $mainEntity,
				];

			default:
				return null;
		}
	}
}
