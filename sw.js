// SEO Panel service worker - installability only, no offline caching.
//
// SEO Panel is a dynamic, session-based admin dashboard: every page depends
// on the logged-in user's data and can change between requests. Caching
// responses here would risk serving stale reports or, worse, one user's
// cached page to another session on a shared device. So this worker exists
// purely to satisfy PWA installability (Chrome/Android "Add to Home
// Screen") - it never intercepts caching, it just passes every request
// straight to the network.

self.addEventListener('install', function(event) {
	self.skipWaiting();
});

self.addEventListener('activate', function(event) {
	event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', function(event) {
	// Scope defaults to "/" (this file is served from the site root), so
	// without this check a service worker registered from any earlier
	// logged-in session at this origin keeps intercepting requests on
	// EVERY later visit - including a fresh install/upgrade run at the
	// same URL, started from a completely different database. Streamed,
	// flush()-based responses (install/upgrade's live progress bar - see
	// Install::importWithProgress()) passed through a service worker's
	// fetch() re-dispatch are a known rough edge: browsers often buffer
	// the whole response before delivering any of it to the page instead
	// of truly streaming it, which makes a successful install look
	// frozen partway through even though the server already finished.
	// The installer/upgrader must always hit the network directly.
	if (event.request.url.indexOf('/install/') !== -1 || event.request.url.indexOf('/upgrade.php') !== -1) {
		return;
	}
	event.respondWith(fetch(event.request));
});
