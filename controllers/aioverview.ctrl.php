<?php

/**
 * Class defines all details about parsing, storing and reporting on
 * Google AI Overview presence/citation data collected alongside keyword
 * rank checks (DataForSEO advanced SERP / SEO Panel API archive).
 */
class AIOverviewController extends Controller {

    /**
     * Normalise a domain or URL for comparison: lowercase, strip protocol,
     * strip leading www., strip port, strip trailing slash/path.
     */
    public static function normalizeDomain($input) {
        $domain = trim((string)$input);
        if ($domain === '') return '';

        // strip protocol if a full URL was passed
        if (stristr($domain, '://')) {
            $parsed = parse_url($domain);
            $domain = !empty($parsed['host']) ? $parsed['host'] : $domain;
        } else {
            // no protocol - still may have a path/query, take the host-like prefix
            $domain = explode('/', $domain)[0];
        }

        $domain = strtolower($domain);
        $domain = preg_replace('/^www\./', '', $domain);
        $domain = preg_replace('/:\d+$/', '', $domain); // strip port

        return $domain;
    }

    /**
     * Known two-label public suffixes where the registrable domain needs
     * three labels (e.g. example.co.uk, not co.uk). Conservative, not a
     * full public-suffix-list - documented limitation for other ccTLDs.
     */
    private static $twoLabelSuffixes = [
        'co.uk', 'org.uk', 'gov.uk', 'ac.uk', 'me.uk', 'net.uk',
        'com.au', 'net.au', 'org.au', 'gov.au', 'edu.au',
        'co.nz', 'net.nz', 'org.nz',
        'co.jp', 'co.in', 'co.za', 'co.kr',
        'com.br', 'com.mx', 'com.sg',
    ];

    /**
     * Best-effort registrable domain (last two labels, three for known
     * ccTLD second-level suffixes).
     */
    public static function registrableDomain($domain) {
        $domain = self::normalizeDomain($domain);
        if ($domain === '') return '';

        $labels = explode('.', $domain);
        if (count($labels) <= 2) return $domain;

        $lastTwo = implode('.', array_slice($labels, -2));
        if (in_array($lastTwo, self::$twoLabelSuffixes, true) && count($labels) >= 3) {
            return implode('.', array_slice($labels, -3));
        }

        return $lastTwo;
    }

    /**
     * Determine whether a cited reference domain counts as a citation of
     * the tracked website domain, per the configured subdomain policy.
     *
     * @param string $refDomain     Domain from the AI Overview reference
     * @param string $trackedDomain The tracked website's domain (or URL)
     * @param string $policy        'registrable' (default) or 'exact'
     */
    public static function isDomainCited($refDomain, $trackedDomain, $policy = 'registrable') {
        $ref     = self::normalizeDomain($refDomain);
        $tracked = self::normalizeDomain($trackedDomain);
        if ($ref === '' || $tracked === '') return false;

        if ($ref === $tracked) return true;

        // Only a bare registrable domain (not an explicit subdomain entry)
        // can match subdomains, and only under the 'registrable' policy.
        if ($policy === 'registrable' && $tracked === self::registrableDomain($tracked)) {
            $suffix = '.' . $tracked;
            return substr($ref, -strlen($suffix)) === $suffix;
        }

        return false;
    }

    /**
     * Parse the DataForSEO 'advanced' SERP items array for an ai_overview
     * entry. Defensive against missing/renamed fields - never throws.
     *
     * @param array $items The 'items' array from a task_get/advanced (or live/advanced) result
     * @param string $checkedDate Fallback data date (Y-m-d) if the response carries none
     * @return array Normalised AI Overview struct (see class doc)
     */
    public static function parseDataForSEO($items, $checkedDate) {
        $normalized = [
            'supported'  => true, // DataForSEO advanced always answers this question
            'present'    => false,
            'async'      => false,
            'references' => [],
            'data_date'  => $checkedDate,
        ];

        if (empty($items) || !is_array($items)) {
            return $normalized;
        }

        $aioItem = null;
        foreach ($items as $item) {
            if (is_array($item) && (($item['type'] ?? '') === 'ai_overview')) {
                $aioItem = $item;
                break;
            }
        }

        if (empty($aioItem)) {
            return $normalized;
        }

        $normalized['present'] = true;
        $normalized['async']   = !empty($aioItem['asynchronous_ai_overview']);

        $rawRefs = [];

        // references can appear at the top level of the ai_overview object...
        if (!empty($aioItem['references']) && is_array($aioItem['references'])) {
            foreach ($aioItem['references'] as $ref) {
                if (is_array($ref)) $rawRefs[] = $ref;
            }
        }

        // ...and/or nested inside individual ai_overview_element items
        if (!empty($aioItem['items']) && is_array($aioItem['items'])) {
            foreach ($aioItem['items'] as $element) {
                if (!empty($element['references']) && is_array($element['references'])) {
                    foreach ($element['references'] as $ref) {
                        if (is_array($ref)) $rawRefs[] = $ref;
                    }
                }
            }
        }

        $seenUrls = [];
        foreach ($rawRefs as $ref) {
            $url = trim((string)($ref['url'] ?? ''));
            if ($url === '' || isset($seenUrls[$url])) continue;
            $seenUrls[$url] = true;

            $normalized['references'][] = [
                'domain'      => !empty($ref['domain']) ? $ref['domain'] : self::normalizeDomain($url),
                'url'         => $url,
                'title'       => $ref['title'] ?? null,
                'source_name' => $ref['source'] ?? null,
            ];
        }

        return $normalized;
    }

    /**
     * Map the SEO Panel API's already-normalised ai_overview response into
     * the same internal struct used for the DataForSEO path.
     *
     * @param array  $spapiData   The 'data' object from POST /v1/SERP
     * @param string $checkedDate Fallback data date (Y-m-d)
     * @return array|null Normalised struct, or null when the archive has not
     *                     crawled this keyword's Google mapping yet (pending -
     *                     caller should skip saving rather than treat as "absent")
     */
    public static function mapSpApi($spapiData, $checkedDate) {
        $capabilities = !empty($spapiData['capabilities']) && is_array($spapiData['capabilities'])
            ? $spapiData['capabilities'] : [];

        if (!in_array('ai_overview', $capabilities, true)) {
            return [
                'supported'  => false,
                'present'    => false,
                'async'      => false,
                'references' => [],
                'data_date'  => $checkedDate,
            ];
        }

        // supported, but this keyword's Google mapping has not been crawled yet
        if (!isset($spapiData['ai_overview']) || !is_array($spapiData['ai_overview'])) {
            return null;
        }

        $aio = $spapiData['ai_overview'];
        $collectedAt = !empty($aio['collected_at']) ? $aio['collected_at'] : $checkedDate;
        $dataDate = date('Y-m-d', strtotime($collectedAt) ?: strtotime($checkedDate));

        $normalized = [
            'supported'  => true,
            'present'    => !empty($aio['present']),
            'async'      => !empty($aio['asynchronous']),
            'references' => [],
            'data_date'  => $dataDate,
        ];

        $seenUrls = [];
        if (!empty($aio['references']) && is_array($aio['references'])) {
            foreach ($aio['references'] as $ref) {
                if (!is_array($ref)) continue;
                $url = trim((string)($ref['url'] ?? ''));
                if ($url === '' || isset($seenUrls[$url])) continue;
                $seenUrls[$url] = true;

                $normalized['references'][] = [
                    'domain'      => !empty($ref['domain']) ? $ref['domain'] : self::normalizeDomain($url),
                    'url'         => $url,
                    'title'       => $ref['title'] ?? null,
                    'source_name' => null, // spAPI does not carry a source label per reference
                ];
            }
        }

        return $normalized;
    }

    /**
     * Compute display-ready, deduped/positioned references and citation
     * status for a normalised AI Overview struct, without touching the DB.
     * Shared by saveResult() (persisted cron path) and the ad-hoc Quick
     * Keyword Position Checker (display-only, no keyword_id to persist against).
     *
     * @param array  $normalized      Struct from parseDataForSEO()/mapSpApi()
     * @param string $trackedDomain
     * @param string $subdomainPolicy 'registrable' | 'exact'
     * @return array ['supported'=>bool,'present'=>bool,'async'=>bool,'dataDate'=>string|null,'refs'=>[...],'citedPosition'=>int|null]
     */
    public static function computeCitation($normalized, $trackedDomain, $subdomainPolicy = 'registrable') {
        // dedupe defensively (parsers already dedupe, but stay safe) and assign display order
        $refs = [];
        $seenUrls = [];
        foreach ($normalized['references'] as $ref) {
            $url = trim((string)($ref['url'] ?? ''));
            if ($url === '' || isset($seenUrls[$url])) continue;
            $seenUrls[$url] = true;
            $refs[] = [
                'position'    => count($refs) + 1,
                'domain'      => $ref['domain'] ?? self::normalizeDomain($url),
                'url'         => $url,
                'title'       => $ref['title'] ?? null,
                'source_name' => $ref['source_name'] ?? null,
            ];
        }

        $citedPosition = null;
        foreach ($refs as $ref) {
            if (self::isDomainCited($ref['domain'], $trackedDomain, $subdomainPolicy)) {
                $citedPosition = $ref['position'];
                break;
            }
        }

        $supported = !empty($normalized['supported']) || !array_key_exists('supported', $normalized);
        $present   = $supported && !empty($normalized['present']);

        return [
            'supported'     => $supported,
            'present'       => $present,
            'async'         => !empty($normalized['async']),
            'dataDate'      => !empty($normalized['data_date']) ? $normalized['data_date'] : null,
            'refs'          => $refs,
            'citedPosition' => $citedPosition,
        ];
    }

    /**
     * Persist a normalised AI Overview result onto every searchresults row
     * for this keyword/search-engine/date (so whichever row a report query
     * picks as "the" row for that date carries correct AIO data), and
     * upsert the deduplicated reference detail rows.
     *
     * @param int         $keywordId
     * @param int         $seId
     * @param string      $checkedDate    Y-m-d
     * @param string      $provider       'dataforseo' | 'spapi'
     * @param array|null  $normalized     Struct from parseDataForSEO()/mapSpApi(), or null to skip (pending)
     * @param string      $trackedDomain  The tracked website's domain/URL
     * @param string      $subdomainPolicy 'registrable' | 'exact'
     */
    function saveResult($keywordId, $seId, $checkedDate, $provider, $normalized, $trackedDomain, $subdomainPolicy = 'registrable') {
        if (empty($normalized)) return; // pending on the provider side - leave unmeasured for now

        $keywordId = intval($keywordId);
        $seId = intval($seId);
        $checkedDate = addslashes($checkedDate);

        $computed = self::computeCitation($normalized, $trackedDomain, $subdomainPolicy);
        $refs = $computed['refs'];
        $citedPosition = $computed['citedPosition'];
        $supported = $computed['supported'];
        $present = $computed['present'];
        $dataDate = $computed['dataDate'] ?: $checkedDate;

        $updateData = [
            'provider'                 => $provider,
            'aio_present|int'          => $present ? 1 : 0,
            'aio_cited|int'            => !is_null($citedPosition) ? 1 : 0,
            'aio_async|int'            => !empty($normalized['async']) ? 1 : 0,
            'aio_reference_count|int'  => count($refs),
            'aio_cited_position'       => is_null($citedPosition) ? 'NULL' : intval($citedPosition),
            'aio_supported|int'        => $supported ? 1 : 0,
            'aio_checked_at'           => 'NOW()',
            'aio_data_date'            => addslashes($dataDate),
        ];

        $this->dbHelper->updateRow(
            'searchresults',
            $updateData,
            "keyword_id=$keywordId AND searchengine_id=$seId AND result_date='$checkedDate'"
        );

        if ($supported) {
            $this->saveReferences($keywordId, $checkedDate, $refs);
        }
    }

    /**
     * Idempotent upsert of AI Overview reference rows for a keyword/date:
     * delete-then-insert, matching the convention used by
     * ReportController::saveMatchedKeywordInfo() for re-runs.
     */
    private function saveReferences($keywordId, $checkedDate, $refs) {
        $keywordId = intval($keywordId);
        $checkedDate = addslashes($checkedDate);

        $resultRow = $this->dbHelper->getRow(
            'searchresults',
            "keyword_id=$keywordId AND result_date='$checkedDate'",
            'MIN(id) as id'
        );
        $resultId = !empty($resultRow['id']) ? intval($resultRow['id']) : null;

        $this->dbHelper->deleteRows('aio_references', "keyword_id=$keywordId AND checked_date='$checkedDate'");

        if (empty($refs)) return;

        $now = date('Y-m-d H:i:s');
        foreach ($refs as $ref) {
            $data = [
                'keyword_id|int'   => $keywordId,
                'result_id'        => is_null($resultId) ? 'NULL' : intval($resultId),
                'checked_date'     => $checkedDate,
                'ref_position|int' => $ref['position'],
                'domain'           => (string)$ref['domain'],
                'url'              => $ref['url'],
                'title'            => (string)($ref['title'] ?? ''),
                'source_name'      => (string)($ref['source_name'] ?? ''),
                'created_at'       => 'NOW()',
            ];
            $this->dbHelper->insertRow('aio_references', $data);
        }
    }

    /**
     * Rolling-window presence indicator for a keyword/search-engine: counts
     * DISTINCT observation dates actually returned (never calendar days),
     * over the most recent $windowSize measured checks.
     *
     * @return array ['measured' => int, 'present' => int, 'observations' => [['date'=>..,'present'=>bool,'provider'=>..], ...]]
     */
    function getRollingWindow($keywordId, $seId, $windowSize = 7) {
        $keywordId = intval($keywordId);
        $seId = intval($seId);
        $windowSize = intval($windowSize) ?: 7;

        // MAX() on provider/aio_checked_at (not just aio_present) keeps this
        // portable under ONLY_FULL_GROUP_BY - both are effectively single-valued
        // per aio_data_date, so MAX() is just a safe way to project them.
        $sql = "SELECT aio_data_date, MAX(aio_present) AS aio_present, MAX(provider) AS provider, MAX(aio_checked_at) as aio_checked_at
                FROM searchresults
                WHERE keyword_id=$keywordId AND searchengine_id=$seId AND aio_checked_at IS NOT NULL
                GROUP BY aio_data_date
                ORDER BY aio_data_date DESC
                LIMIT $windowSize";
        $rows = $this->db->select($sql);

        $observations = [];
        foreach (array_reverse($rows) as $row) {
            $observations[] = [
                'date'    => $row['aio_data_date'],
                'present' => !empty($row['aio_present']),
                'provider'=> $row['provider'],
            ];
        }

        $present = count(array_filter($observations, function($o) { return $o['present']; }));

        return [
            'measured'     => count($observations),
            'present'      => $present,
            'observations' => $observations,
        ];
    }

    /**
     * Most recent citation panel for a keyword: all cited domains from the
     * latest measured check, ordered by position, tracked domain flagged.
     */
    function getCompetitorPanel($keywordId, $trackedDomain, $subdomainPolicy = 'registrable') {
        $keywordId = intval($keywordId);

        $dateRow = $this->dbHelper->getRow(
            'aio_references',
            "keyword_id=$keywordId",
            'MAX(checked_date) as checked_date'
        );
        $latestDate = !empty($dateRow['checked_date']) ? $dateRow['checked_date'] : null;
        if (empty($latestDate)) return [];

        $refs = $this->dbHelper->getAllRows(
            'aio_references',
            "keyword_id=$keywordId AND checked_date='" . addslashes($latestDate) . "'",
            '*'
        );

        usort($refs, function($a, $b) { return $a['ref_position'] <=> $b['ref_position']; });

        foreach ($refs as &$ref) {
            $ref['is_tracked'] = self::isDomainCited($ref['domain'], $trackedDomain, $subdomainPolicy);
        }

        return $refs;
    }

    /**
     * Prune aio_references detail rows older than the configured retention
     * window. Called once per cron run, not per keyword.
     */
    function pruneOldReferences() {
        $retentionDays = defined('SP_AIO_RETENTION_DAYS') ? intval(SP_AIO_RETENTION_DAYS) : 90;
        if ($retentionDays <= 0) return;

        $cutoff = date('Y-m-d', strtotime("-$retentionDays days"));
        $this->dbHelper->deleteRows('aio_references', "checked_date < '$cutoff'");
    }
}
?>
