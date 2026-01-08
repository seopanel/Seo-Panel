<?php

/***************************************************************************
 *   Copyright (C) 2025 by Geo Varghese(www.seopanel.org)                 *
 *   sendtogeo@gmail.com                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/

# class defines sitemap parsing functions for site auditor
class SitemapParser extends Controller {

    /**
     * Parse sitemap.xml and extract URLs
     *
     * @param string $sitemapUrl URL to sitemap
     * @param int $projectId Project ID
     * @return array Array of discovered URLs
     */
    function parseSitemap($sitemapUrl, $projectId) {
        $spider = new Spider();
        $result = $spider->getContent($sitemapUrl);
        $urls = array();

        if (empty($result['page'])) {
            return $urls;
        }

        // Try to parse as XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($result['page']);

        if ($xml === false) {
            // Not valid XML, might be text sitemap
            return $this->parseTextSitemap($result['page']);
        }

        // Register namespaces
        $namespaces = $xml->getNamespaces(true);

        // Check if this is a sitemap index
        if (isset($xml->sitemap)) {
            // This is a sitemap index - recursively parse child sitemaps
            foreach ($xml->sitemap as $sitemap) {
                $childUrl = (string)$sitemap->loc;
                if (!empty($childUrl)) {
                    // Save sitemap index entry
                    $this->saveSitemapInfo($projectId, $childUrl, 'index');
                    // Recursively parse child sitemap
                    $childUrls = $this->parseSitemap($childUrl, $projectId);
                    $urls = array_merge($urls, $childUrls);
                }
            }
        }
        // Regular sitemap with URLs
        elseif (isset($xml->url)) {
            foreach ($xml->url as $url) {
                $loc = (string)$url->loc;
                if (!empty($loc)) {
                    $urls[] = $loc;
                }
            }
        }

        return $urls;
    }

    /**
     * Parse text-based sitemap
     *
     * @param string $content Sitemap content
     * @return array Array of URLs
     */
    function parseTextSitemap($content) {
        $urls = array();
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments and empty lines
            if (empty($line) || substr($line, 0, 1) === '#') {
                continue;
            }
            // Check if line is a valid URL
            if (preg_match('#^https?://#i', $line)) {
                $urls[] = $line;
            }
        }

        return $urls;
    }

    /**
     * Discover sitemaps from robots.txt
     *
     * @param string $domainUrl Base domain URL
     * @return array Array of sitemap URLs
     */
    function discoverSitemapsFromRobots($domainUrl) {
        $robotsUrl = rtrim($domainUrl, '/') . '/robots.txt';
        $spider = new Spider();
        $result = $spider->getContent($robotsUrl);
        $sitemaps = array();

        if (empty($result['page'])) {
            return $sitemaps;
        }

        // Parse robots.txt for Sitemap: directives
        $lines = explode("\n", $result['page']);
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^Sitemap:\s*(.+)$/i', $line, $matches)) {
                $sitemaps[] = trim($matches[1]);
            }
        }

        return $sitemaps;
    }

    /**
     * Try common sitemap locations
     *
     * @param string $domainUrl Base domain URL
     * @return array Array of sitemap URLs that exist
     */
    function discoverCommonSitemaps($domainUrl) {
        $baseUrl = rtrim($domainUrl, '/');
        $commonPaths = array(
            '/sitemap.xml',
            '/sitemap_index.xml',
            '/sitemap1.xml',
            '/sitemaps/sitemap.xml',
            '/sitemap/sitemap.xml'
        );

        $found = array();
        $spider = new Spider();

        foreach ($commonPaths as $path) {
            $url = $baseUrl . $path;
            $header = Spider::getHeader($url);

            // Check if returns 200 OK
            if (stristr($header, '200 OK')) {
                $found[] = $url;
                // Only check first one found to save time
                break;
            }
        }

        return $found;
    }

    /**
     * Save sitemap info to database
     *
     * @param int $projectId Project ID
     * @param string $sitemapUrl Sitemap URL
     * @param string $type Sitemap type (xml, txt, index)
     * @return void
     */
    function saveSitemapInfo($projectId, $sitemapUrl, $type = 'xml') {
        // Check if already exists
        $sql = "SELECT id FROM auditorsitemaps WHERE project_id=$projectId AND sitemap_url='".addslashes($sitemapUrl)."'";
        $existing = $this->db->select($sql, true);

        if (empty($existing['id'])) {
            $sql = "INSERT INTO auditorsitemaps (project_id, sitemap_url, sitemap_type, last_parsed)
                    VALUES ($projectId, '".addslashes($sitemapUrl)."', '$type', NOW())";
            $this->db->query($sql);
        } else {
            $sql = "UPDATE auditorsitemaps SET last_parsed=NOW() WHERE id=".$existing['id'];
            $this->db->query($sql);
        }
    }

    /**
     * Update sitemap URL count
     *
     * @param int $projectId Project ID
     * @param string $sitemapUrl Sitemap URL
     * @param int $count Number of URLs found
     * @return void
     */
    function updateSitemapUrlCount($projectId, $sitemapUrl, $count) {
        $sql = "UPDATE auditorsitemaps SET urls_found=$count
                WHERE project_id=$projectId AND sitemap_url='".addslashes($sitemapUrl)."'";
        $this->db->query($sql);
    }
}
?>
