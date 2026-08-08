const CACHE = "bible-cache";

const files = [
    "./",
    "./index.php",
    "./styles.css",
    "./public/god_is_love.png"
];


// Install new service worker immediately
self.addEventListener("install", event => {

    event.waitUntil(
        caches.open(CACHE)
            .then(cache => cache.addAll(files))
            .then(() => self.skipWaiting())
    );

});


// Remove old caches and take control immediately
self.addEventListener("activate", event => {

    event.waitUntil(
        Promise.all([
            caches.keys().then(keys => {
                return Promise.all(
                    keys
                        .filter(key => key !== CACHE)
                        .map(key => caches.delete(key))
                );
            }),

            self.clients.claim()
        ])
    );

});


// Network first for pages, cache first for assets
self.addEventListener("fetch", event => {

    // Always get newest pages
    if (event.request.mode === "navigate") {

        event.respondWith(
            fetch(event.request)
                .catch(() => caches.match(event.request))
        );

        return;
    }


    // Cache assets
    event.respondWith(
        caches.match(event.request)
            .then(response => response || fetch(event.request))
    );

});