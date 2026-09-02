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
	event.respondWith(fetch(event.request));
});
