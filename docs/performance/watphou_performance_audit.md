# Watphou Travels — browser memory and loading audit

**Date:** 24 September 2026  
**Site measured:** https://www.watphoutravels.site/  
**Tool:** Google Chrome via Playwright, Chrome DevTools Protocol `Performance.getMetrics`, plus decoded-image bytes (`naturalWidth × naturalHeight × 4`).  
**Viewports:** desktop 1366×768 and mobile 390×844.  
**Official Lighthouse:** Hostinger returns Hypertext Transfer Protocol (HTTP) 403 to Lighthouse’s automated Chrome. Scores below are from the same Chrome metrics, not a Lighthouse category score.

JavaScript heap is not the same as the memory Chrome uses for photos. Photo bitmaps live outside the JavaScript heap. Both were measured.

## 1. Executive summary

There is **no JavaScript memory leak**. After the homepage settled, the JavaScript heap stayed around 1.8–2.1 megabytes (MB), event listeners stayed at 56, and decoded photo bytes stayed flat for 5 minutes. Ten trips through Home → Tours → a tour → About → Request a quote did **not** climb without limit. Listeners and document counts went up and down with the page, then came back down.

The high memory comes from **decoded photographs that are larger than the space they are shown in**, plus the homepage hero keeping two full-size frames in memory. On the About page that was about **53 MB** of decoded photos. On a quiet page (Contact, Tailor-made, Request a quote) the header logo alone was a 1280×720 picture shown at 36 pixels tall, about **3.7 MB** decoded.

The ongoing processor (Central Processing Unit, CPU) cost is the homepage slideshow. It still changes photo about every 2 seconds while the hero is on screen (that timing stays). It now **stops while the hero is off screen** and while the browser tab is hidden, so a visitor reading the rest of the page is not decoding a new photograph every 2 seconds.

A test computer also injected Kaspersky scripts into Chrome. Those are the antivirus on that PC, not part of the website.

## 2. Baseline (before)

Measured on theme 2.5.1, before this change. The same browser tab was reused, so Cascading Style Sheets (CSS) and JavaScript after the homepage were often already cached (`transferSize` 0). Image numbers are still the new bytes for that page.

### Desktop, cold-enough first view of each page’s images

| Page | Image transfer | Decoded images | JavaScript heap | Document Object Model (DOM) elements | Listeners |
|------|---------------:|---------------:|----------------:|-------------------------------------:|----------:|
| Home | 2.21 MB | 16.2 MB | 2.9 MB | 538 | 106 |
| Tours | 0.36 MB | 14.8 MB | 3.7 MB | 387 | 160 |
| Day tour | 0.90 MB | 16.3 MB | 1.9 MB | 322 | 34 |
| 4-day tour | 1.18 MB | 21.0 MB | 3.1 MB | 354 | 86 |
| About | 1.67 MB | **53.2 MB** | 2.2 MB | 303 | 47 |
| Tailor-made | cached | 5.2 MB | 2.2 MB | 179 | 22 |
| Request a quote | cached | 5.2 MB | 3.4 MB | 259 | 58 |
| Contact | cached | 5.2 MB | 3.9 MB | 168 | 92 |
| Bolaven Plateau | 0.10 MB | 11.9 MB | 2.6 MB | 290 | 36 |

Mobile homepage: 0.83 MB images, **9.4 MB** decoded, heap 2.9 MB. Mobile About: **21.2 MB** decoded.

No web fonts. CSS on the homepage was about 8 kilobytes (KB) transferred. Theme JavaScript was about 14 KB. Hostinger Reach was about 11 KB together (`embed.js` 4.5 KB + `subscription-view.js` 6 KB).

### Five minutes idle on the homepage (desktop)

| Time | JavaScript heap | Listeners | Decoded images | Hero frames decoded |
|------|----------------:|----------:|---------------:|---------------------:|
| 0 s | 2.90 MB | 106 | 16.21 MB | 2 |
| 60 s | 1.76 MB | 56 | 16.21 MB | 2 |
| 120 s | 1.94 MB | 56 | 16.21 MB | 2 |
| 180 s | 2.11 MB | 56 | 16.21 MB | 2 |
| 240 s | 1.77 MB | 56 | 16.21 MB | 2 |
| 300 s | 1.94 MB | 56 | 16.21 MB | 2 |

Image transfer rose during the first minute (the slideshow fetched the next frames once) and then stayed at 3.45 MB. It did not keep downloading.

### Ten navigation cycles

JavaScript heap after each cycle, in MB: 4.0, 3.4, 3.8, 3.0, 4.9, 3.9, 2.9, 5.0, 3.9, 2.9.  
Listeners: 137, 83, 83, 24, 135, 83, 24, 135, 83, 24.  
Open documents: 8, 6, 6, 4, 8, 6, 4, 8, 6, 4.

Opening and closing the menu, resizing, and scrolling **lowered** the heap (4.1 MB → 3.8 MB) and listeners (90 → 67). Nothing piled up.

## 3. Problems found

| Class | What | Where | Impact | Severity |
|-------|------|--------|--------|----------|
| B — heavy but stable | Header logo `logo-wpt.jpg` is 1280×720, shown at 36 pixels tall | `header.php` | ~3.7 MB decoded on **every** page | High |
| B — heavy but stable | Footer logo 756×459, shown about 72 pixels tall | `footer.php` | ~1.4 MB decoded on every page | Medium |
| B + E | About photos are about 1400 pixels wide, shown at 180 pixels tall | `template-about.php`, `assets/images/about/` | About page ~53 MB decoded, 1.67 MB downloaded | High |
| B + E | Tour gallery used the 1024-pixel WordPress size for a 180-pixel-tall grid. Click-to-enlarge still needs the original | `photos.php`, `single-tour.php` | 4-day tour ~21 MB decoded, 1.18 MB downloaded | High |
| B + D | Tour hero was a CSS `background-image`, so the browser always fetched one large file and could not pick a smaller one on a phone | `single-tour.php` | Extra decode, no responsive image | Medium |
| C — CPU | Homepage hero decodes a new photograph about every 2 seconds even after the visitor has scrolled away | `theme.js` | Keeps the processor busy on a weak computer while they read the page | High |
| D — graphics | Review track had `will-change: transform` all the time, so Chrome kept a graphics layer promoted | `theme.css` | Extra graphics memory while the carousel sits still | Low |
| E — network | Request-form script loaded on pages with no form (tours list, privacy, and so on) | `watphou-bookings.php` | Small script, still useless work | Low |
| G — third party | Hostinger Reach `embed.js` and `subscription-view.js` on every page | Hostinger Reach plugin | About 11 KB. Not the RAM problem. Now deferred so they do not block parsing | Low |
| F — server | Staging Hypertext Markup Language (HTML) is `Cache-Control: no-store` | `mu-plugins/watphou-env.php` | Correct while the site must stay out of Google. Static files are already cached (CSS up to 1 year, Brotli) | Info |
| Not the site | Kaspersky injected `gc.kis.v2.scr.kaspersky-labs.com` into the test Chrome | The PC used for the test | Can add CPU and memory that the website does not control | Info |

Not found:

- Duplicate desktop and mobile copies of the header, menu, or hero. One menu is hidden with CSS on small screens.
- A second copy of jQuery. The public site does not load jQuery.
- Web fonts. The theme uses system fonts (`Segoe UI`, Roboto, Arial).
- An unbounded leak of timers, listeners, or detached page documents.

The homepage hero already kept only the current and next frame decoded (2 of 11). That earlier fix is still in place. The remaining homepage cost is those two frames (a 1920-pixel photograph is about 6 MB decoded) plus the old logo.

## 4. Changes made

Nothing visual was redesigned. Text, menus, addresses, forms, languages, and the slideshow timing while the hero is on screen are unchanged.

| File | Before | After | Why it is faster | Feature check |
|------|--------|-------|------------------|---------------|
| `header.php` + `logo-wpt-header.jpg` | 1280×720 JPEG (37 KB file, ~3.7 MB decoded) | 256×144 JPEG (5 KB file) | Same logo at the size it is actually drawn | Logo still in the header |
| `footer.php` + `logo-wpt-white-footer.png` | 756×459 PNG | 360×219 PNG | Same white mark, less decode | Footer mark still white on the dark bar |
| `template-about.php` + `about/*-800w.jpg` | Grid loaded the 1400-pixel file | Grid loads an 800-pixel file. The click-to-enlarge link still opens the original | About decoded memory and download drop. Sharpness at 180 pixels tall is kept (800 pixels covers a high-density screen) | Lightbox still opens the full photo |
| `photos.php` + `single-tour.php` | Gallery `src` was the 1024-pixel size | Grid uses the 768-pixel size. `data-full` keeps the original for the viewer | Less decode and download in the grid | Tour photos, captions, and enlarge still work |
| `single-tour.php` | Hero was a CSS background | Hero is an `<img>` with WordPress responsive `srcset` | A phone can choose a smaller file. Look stays cover / centered | Tour title, price, and request button unchanged |
| `theme.js` | Slideshow kept running after scroll | Slideshow pauses when the hero is off screen, and when the tab is hidden. It starts again when the hero comes back | Stops repeated JPEG decode while the visitor reads lower on the page | On-screen timing is still about 2 / 5 / 10 seconds |
| `theme.js` | Lightbox used whatever file was in the grid | Lightbox prefers `data-full` or the link to the original | Grid can be lighter without a blurry enlarge view | Enlarge still shows the original |
| `theme.js` + `theme.css` | `will-change: transform` always on; resize handler every event | Graphics hint only during the 450 ms move; resize is one frame at a time | Less permanent graphics memory | Reviews still roll and the buttons still work |
| `theme.css` | Gallery figures always fully rendered | `content-visibility: auto` with a 180-pixel placeholder | Off-screen gallery cells can be skipped until scrolled near | Photos still appear when you scroll to them |
| `watphou-bookings.php` | Form script on every URL | Script only on the homepage, tour pages, and pages that contain the request form | Privacy and the tours list skip a script they do not use | Homepage and tour request forms still validate |
| `functions.php` | Reach scripts parsed immediately | `defer` on Hostinger Reach script tags | They still load, after the page is parsed | Reach is not removed |

Versions now live: theme **2.5.2**, `watphou-core` **1.8.2**, `watphou-bookings` **1.2.1**.

## 5. Before / after

Decoded-image bytes are the useful RAM number. JavaScript heap barely changed, because it was never the problem.

| Metric | Before | After | Change |
|--------|-------:|------:|--------|
| Homepage decoded images (desktop) | 16.2 MB | 11.6 MB | −28% |
| Homepage decoded images (mobile) | 9.4 MB | 4.8 MB | −49% |
| About decoded images (desktop) | 53.2 MB | 16.5 MB | −69% |
| About decoded images (mobile) | 21.2 MB | 6.1 MB | −71% |
| 4-day tour decoded images (desktop) | 21.0 MB | 11.0 MB | −48% |
| 4-day tour image download | 1.18 MB | 0.69 MB | −41% |
| About image download (desktop) | 1.67 MB | 0.73 MB | −56% |
| About image download (mobile) | 0.64 MB | 0.29 MB | −54% |
| Contact / quote / tailor-made decoded images | 5.2 MB | 0.56 MB | −89% |
| Homepage JavaScript heap | 2.9 MB | 2.9 MB | flat |
| Homepage DOM elements | 538 | 538 | unchanged |
| Homepage Largest Contentful Paint (LCP), desktop | not captured | 2.25 s | under 2.5 s |
| Homepage LCP, mobile | not captured | 1.92 s | under 2.5 s |
| Cumulative Layout Shift (CLS) | not captured | about 0 | under 0.1 |
| 5-minute idle heap (homepage, hero still on screen) | 1.8–2.1 MB, listeners stuck at 56 | 1.8–2.1 MB, listeners stuck at 56 | still flat |
| 5-minute idle decoded images | 16.21 MB, flat after 60 s | 11.60 MB, flat after 60 s | −28%, still flat |
| Heap after 10 navigation cycles | 2.9–5.0 MB, not climbing | 2.9–5.0 MB, not climbing | still not a leak |

LCP before the change was not recorded, because the first run did not install a paint observer. The after numbers are from that observer in Chrome.

Warm loads of CSS and JavaScript are already cache hits (`public, max-age=31536000` on theme CSS, Brotli). HTML stays `no-store` on staging so Google is not invited to index it.

## 6. Remaining risks

- The homepage hero on a wide screen is still two 1920-pixel photographs (about 6 MB decoded each). That is the picture quality of the slideshow. Shrinking it further would look soft on a 1920-pixel-wide monitor.
- While the hero is **on screen**, it still changes about every 2 seconds on a fast computer. That is the requested design. Weak computers with the hero filling the window will still decode frames. Scrolling down now stops that.
- About can still decode roughly 16 MB if many photos are on screen. That is much less than 53 MB.
- WordPress still prints the emoji script (`wp-emoji-release.min.js`) for the map pin. It is small. Removing it can change the pin’s drawing from WordPress’s emoji image to the computer’s own emoji.
- Hostinger Reach still runs on every page. It is deferred, not removed.
- Interaction to Next Paint (INP) was not given a lab number. Empty-form validation and the mobile menu were exercised in a real browser and responded immediately. Under a 4× processor slowdown plus Slow 4G, the mobile menu still opened in about 0.7 seconds.
- A 6× slowdown plus Slow 4G took about 39 seconds to finish loading the homepage. That time is the hero photograph download, not a frozen script. The page stayed usable once the first view was up.
- Scrolling the homepage away from the hero drops decoded hero frames from 2 to 1. That was confirmed in the throttled Chrome run.
- This Chrome was headless for the numbers. Hostinger sometimes answers the homepage with HTTP 403 to that client even while the real page is returned. A normal browser loaded the pages with HTTP 200.

## 7. Recommended later (not done now)

1. At the real-domain launch, turn off staging `no-store` only when indexing is allowed. Do not add a second cache plugin on top of Hostinger’s cache.
2. If the office does not use Hostinger Reach, deactivate that plugin. That is the only third-party script we load on purpose.
3. Optional: stop the WordPress emoji script if a system pin is acceptable.
4. A 1280-pixel-wide hero file (between today’s 960 and 1920) would cut desktop decode a bit more without going soft on a 1366-pixel laptop. Not added in this pass, to avoid a new photo set without a side-by-side look.

## Checks after the change

- French homepage still shows French headings, menu, and “4000 îles”.
- Tour page still has the itinerary, inclusions, exclusions, upgrades, gallery buttons, related tours, and the request form.
- Submitting an empty request lists Name, Email, Preferred date, and the privacy agreement. It does not send the email.
- Mobile menu opens (Home, Day Tours, Destinations, Request a quote).
- Theme script on the live site is `theme.js?ver=2.5.2`. Tours listing and the privacy page do not load `form.js`. The homepage and the tour page do.
- Widths 360, 412, 768, 1366, and 1920 on the homepage, the tours list, the 4-day tour, and the French homepage: no horizontal overflow, and no new page errors.
