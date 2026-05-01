#!/usr/bin/env python3.11
"""
Homepage Diagnostic Script for neogen.store
Captures screenshots + detailed section analysis via Playwright.
"""

import json
import sys
from playwright.sync_api import sync_playwright

SITE = "https://neogen.store"
DESKTOP_VP = {"width": 1440, "height": 900}
MOBILE_VP = {"width": 393, "height": 852}
SCREENSHOT_DIR = "/Volumes/Fahadmega/NGS_Business/screenshots"


def diagnose_sections(page):
    """Extract detailed info about every section on the homepage."""
    return page.evaluate("""() => {
        const results = {
            pageTitle: document.title,
            bodyClasses: document.body.className,
            htmlDir: document.documentElement.dir,
            htmlLang: document.documentElement.lang,
            viewport: { width: window.innerWidth, height: window.innerHeight },
            scrollHeight: document.documentElement.scrollHeight,
            elementorContainer: null,
            sections: [],
            heroAnalysis: null,
            productGrids: [],
            hiddenElements: [],
            cssInjection: null,
            elementorVersion: null,
        };

        // Check Elementor version
        const elVer = document.querySelector('meta[name="generator"][content*="Elementor"]');
        if (elVer) results.elementorVersion = elVer.content;

        // Check neogen-theme-css style block
        const neoCSS = document.getElementById('neogen-theme-css');
        if (neoCSS) {
            const cssText = neoCSS.textContent || neoCSS.innerText || '';
            results.cssInjection = {
                exists: true,
                tagName: neoCSS.tagName,
                length: cssText.length,
                preview: cssText.substring(0, 500),
            };
        } else {
            const neoLink = document.querySelector('link#neogen-theme-css, style#neogen-theme-css, link#neogen-theme-customizer-css, style#neogen-theme-customizer-css');
            results.cssInjection = {
                exists: !!neoLink,
                tagName: neoLink ? neoLink.tagName : null,
                id: neoLink ? neoLink.id : null,
                href: neoLink && neoLink.href ? neoLink.href : null,
                note: neoLink ? 'Found as ' + neoLink.tagName + '#' + neoLink.id : 'NOT FOUND on page',
            };
        }

        // Gather ALL inline styles and link stylesheets with 'neogen' in id
        const allStyles = document.querySelectorAll('style[id*="neogen"], link[id*="neogen"]');
        results.neogenStyles = Array.from(allStyles).map(s => ({
            tagName: s.tagName,
            id: s.id,
            href: s.href || null,
            contentLength: s.textContent ? s.textContent.length : 0,
            preview: s.textContent ? s.textContent.substring(0, 300) : null,
        }));

        // Find main Elementor container for page 2745
        const elContainer = document.querySelector('.elementor-2745, [data-elementor-id="2745"]');
        if (elContainer) {
            results.elementorContainer = {
                found: true,
                tagName: elContainer.tagName,
                className: elContainer.className,
                childCount: elContainer.children.length,
                rect: elContainer.getBoundingClientRect().toJSON(),
            };
        } else {
            results.elementorContainer = { found: false };
            // Try to find any elementor container
            const anyEl = document.querySelector('[class*="elementor-"]');
            results.elementorContainer.anyElementor = anyEl ? anyEl.className.substring(0, 200) : 'none';
        }

        // Find ALL elementor sections
        const oldSections = document.querySelectorAll('.elementor-2745 .elementor-section');
        const newSections = document.querySelectorAll('.elementor-2745 .e-con');
        const sectionWrap = document.querySelector('.elementor-2745 > .elementor-section-wrap');

        results.sectionCounts = {
            oldFormat: oldSections.length,
            newFormat: newSections.length,
            hasSectionWrap: !!sectionWrap,
        };

        // Get the top-level sections (direct children of section-wrap or container)
        let topSections;
        if (sectionWrap) {
            topSections = Array.from(sectionWrap.children);
        } else if (elContainer) {
            topSections = Array.from(elContainer.children);
        } else {
            topSections = [];
        }

        topSections.forEach((sec, i) => {
            const rect = sec.getBoundingClientRect();
            const style = window.getComputedStyle(sec);
            const bg = style.backgroundImage;
            const bgColor = style.backgroundColor;

            const textContent = sec.innerText || '';
            const textPreview = textContent.replace(/\\s+/g, ' ').trim().substring(0, 200);

            const images = sec.querySelectorAll('img');
            const imgSrcs = Array.from(images).slice(0, 5).map(img => ({
                src: img.src ? img.src.substring(0, 120) : '',
                alt: img.alt || '',
                naturalWidth: img.naturalWidth,
                naturalHeight: img.naturalHeight,
                displayWidth: img.width,
                displayHeight: img.height,
            }));

            const products = sec.querySelectorAll('.product, .wc-block-grid__product, [class*="product-card"]');
            const wooGrids = sec.querySelectorAll('.woocommerce, .products, .wc-block-grid');

            // Inner columns
            const columns = sec.querySelectorAll('.elementor-column, .elementor-col-50, .elementor-col-33, .e-con-inner');
            const widgets = sec.querySelectorAll('.elementor-widget');

            const sectionData = {
                index: i,
                tagName: sec.tagName,
                id: sec.id || null,
                className: sec.className.substring(0, 300),
                dataId: sec.getAttribute('data-id') || null,
                elementType: sec.getAttribute('data-element_type') || null,
                dataSettings: (() => {
                    try { return sec.getAttribute('data-settings') ? JSON.parse(sec.getAttribute('data-settings')) : null; }
                    catch(e) { return sec.getAttribute('data-settings'); }
                })(),
                rect: {
                    x: Math.round(rect.x),
                    y: Math.round(rect.y),
                    width: Math.round(rect.width),
                    height: Math.round(rect.height),
                },
                computedStyle: {
                    display: style.display,
                    visibility: style.visibility,
                    opacity: style.opacity,
                    overflow: style.overflow,
                    overflowX: style.overflowX,
                    overflowY: style.overflowY,
                    position: style.position,
                    zIndex: style.zIndex,
                    margin: style.margin,
                    padding: style.padding,
                    backgroundColor: bgColor,
                    backgroundImage: bg !== 'none' ? bg.substring(0, 200) : 'none',
                    backgroundSize: style.backgroundSize,
                    backgroundPosition: style.backgroundPosition,
                    minHeight: style.minHeight,
                    maxHeight: style.maxHeight,
                    height: style.height,
                    direction: style.direction,
                    textAlign: style.textAlign,
                    color: style.color,
                },
                innerStructure: {
                    columnCount: columns.length,
                    widgetCount: widgets.length,
                    widgetTypes: Array.from(widgets).slice(0, 10).map(w => {
                        const cls = w.className;
                        const match = cls.match(/elementor-widget-([\\w-]+)/);
                        return match ? match[1] : 'unknown';
                    }),
                },
                textPreview: textPreview,
                imageCount: images.length,
                imageSamples: imgSrcs,
                productCount: products.length,
                hasWooGrid: wooGrids.length > 0,
                isZeroHeight: rect.height === 0,
                isHidden: style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0',
            };

            results.sections.push(sectionData);

            if (sectionData.isZeroHeight || sectionData.isHidden) {
                results.hiddenElements.push({
                    index: i,
                    id: sec.id,
                    className: sec.className.substring(0, 100),
                    reason: sectionData.isZeroHeight ? 'zero-height' : 'hidden-css',
                    display: style.display,
                    visibility: style.visibility,
                    opacity: style.opacity,
                    height: rect.height,
                });
            }
        });

        // Hero analysis: first section
        if (results.sections.length > 0) {
            const firstSec = topSections[0];
            if (firstSec) {
                const hStyle = window.getComputedStyle(firstSec);
                const overlay = firstSec.querySelector('.elementor-background-overlay');
                results.heroAnalysis = {
                    tagName: firstSec.tagName,
                    className: firstSec.className.substring(0, 300),
                    rect: firstSec.getBoundingClientRect().toJSON(),
                    backgroundImage: hStyle.backgroundImage !== 'none' ? hStyle.backgroundImage.substring(0, 300) : 'none',
                    backgroundColor: hStyle.backgroundColor,
                    backgroundSize: hStyle.backgroundSize,
                    backgroundPosition: hStyle.backgroundPosition,
                    minHeight: hStyle.minHeight,
                    height: hStyle.height,
                    padding: hStyle.padding,
                    overlayExists: !!overlay,
                    overlayColor: overlay ? window.getComputedStyle(overlay).backgroundColor : null,
                    overlayOpacity: overlay ? window.getComputedStyle(overlay).opacity : null,
                    headings: Array.from(firstSec.querySelectorAll('h1, h2, h3')).map(h => ({
                        tag: h.tagName,
                        text: h.innerText.substring(0, 100),
                        fontSize: window.getComputedStyle(h).fontSize,
                        color: window.getComputedStyle(h).color,
                        fontWeight: window.getComputedStyle(h).fontWeight,
                    })),
                    buttons: Array.from(firstSec.querySelectorAll('a.elementor-button, .elementor-button-wrapper a, a[class*="btn"]')).map(b => ({
                        text: b.innerText.substring(0, 50),
                        href: b.href,
                        classes: b.className.substring(0, 100),
                        bgColor: window.getComputedStyle(b).backgroundColor,
                        color: window.getComputedStyle(b).color,
                    })),
                    heroImage: (() => {
                        const img = firstSec.querySelector('img');
                        if (!img) return null;
                        return {
                            src: img.src.substring(0, 200),
                            alt: img.alt,
                            naturalWidth: img.naturalWidth,
                            naturalHeight: img.naturalHeight,
                            displayWidth: img.width,
                            displayHeight: img.height,
                        };
                    })(),
                };
            }
        }

        // Product grid analysis
        const allProductLists = document.querySelectorAll('.elementor-2745 ul.products, .elementor-2745 .products');
        allProductLists.forEach(pc => {
            const items = pc.querySelectorAll('li.product, .product');
            if (items.length === 0) return;

            const gridStyle = window.getComputedStyle(pc);
            results.productGrids.push({
                tagName: pc.tagName,
                className: pc.className.substring(0, 200),
                productCount: items.length,
                display: gridStyle.display,
                gridTemplateColumns: gridStyle.gridTemplateColumns || null,
                flexWrap: gridStyle.flexWrap || null,
                gap: gridStyle.gap || gridStyle.gridGap || null,
                rect: pc.getBoundingClientRect().toJSON(),
                firstProductRect: items[0] ? items[0].getBoundingClientRect().toJSON() : null,
                firstProductImage: (() => {
                    if (!items[0]) return null;
                    const img = items[0].querySelector('img');
                    if (!img) return null;
                    return {
                        src: img.src.substring(0, 120),
                        naturalWidth: img.naturalWidth,
                        naturalHeight: img.naturalHeight,
                        displayWidth: img.width,
                        displayHeight: img.height,
                    };
                })(),
                columnAnalysis: (() => {
                    // Check how many items appear on first row
                    if (items.length < 2) return null;
                    const firstY = items[0].getBoundingClientRect().y;
                    let columnsInFirstRow = 0;
                    for (let it of items) {
                        if (Math.abs(it.getBoundingClientRect().y - firstY) < 5) columnsInFirstRow++;
                        else break;
                    }
                    return { columnsInFirstRow };
                })(),
            });
        });

        // Section spacing
        if (results.sections.length > 1) {
            results.sectionSpacing = [];
            for (let i = 1; i < results.sections.length; i++) {
                const prev = results.sections[i - 1];
                const curr = results.sections[i];
                const gap = curr.rect.y - (prev.rect.y + prev.rect.height);
                results.sectionSpacing.push({
                    between: 'section[' + (i-1) + '] -> section[' + i + ']',
                    gapPx: Math.round(gap),
                    prevBottom: Math.round(prev.rect.y + prev.rect.height),
                    currTop: Math.round(curr.rect.y),
                });
            }
        }

        return results;
    }""")


def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)

        # ===== DESKTOP =====
        print("=" * 60)
        print("DESKTOP DIAGNOSIS (1440x900)")
        print("=" * 60)

        ctx_desktop = browser.new_context(
            viewport=DESKTOP_VP,
            locale="ar-SA",
            device_scale_factor=1,
            ignore_https_errors=True,
        )
        page_d = ctx_desktop.new_page()
        print(f"Navigating to {SITE} ...")
        page_d.goto(SITE, wait_until="networkidle", timeout=60000)
        page_d.wait_for_timeout(3000)

        desktop_path = f"{SCREENSHOT_DIR}/home_diag_desktop.png"
        page_d.screenshot(path=desktop_path, full_page=True)
        print(f"Desktop screenshot saved: {desktop_path}")

        desktop_data = diagnose_sections(page_d)
        print(f"\nPage title: {desktop_data['pageTitle']}")
        print(f"Direction: {desktop_data['htmlDir']}, Lang: {desktop_data['htmlLang']}")
        print(f"Scroll height: {desktop_data['scrollHeight']}px")
        print(f"Elementor version: {desktop_data['elementorVersion']}")

        css_info = desktop_data['cssInjection']
        print(f"\n--- Neogen Theme CSS ---")
        print(f"  Exists: {css_info.get('exists')}")
        if css_info.get('length'):
            print(f"  Tag: {css_info['tagName']}, Length: {css_info['length']} chars")
            print(f"  Preview: {css_info.get('preview', '')[:300]}")
        elif css_info.get('href'):
            print(f"  Tag: {css_info['tagName']}, ID: {css_info.get('id')}")
            print(f"  Href: {css_info['href']}")
        else:
            print(f"  Note: {css_info.get('note', 'N/A')}")

        if desktop_data.get('neogenStyles'):
            print(f"  All neogen styles on page:")
            for ns in desktop_data['neogenStyles']:
                print(f"    - {ns['tagName']}#{ns['id']}: "
                      f"{'href=' + str(ns['href']) if ns['href'] else str(ns['contentLength']) + ' chars'}")

        ec = desktop_data['elementorContainer']
        print(f"\n--- Elementor Container (.elementor-2745) ---")
        print(f"  Found: {ec['found']}")
        if ec['found']:
            print(f"  Tag: {ec['tagName']}, Children: {ec['childCount']}")
            print(f"  Classes: {ec['className'][:200]}")
            print(f"  Rect: {ec['rect']['width']}x{ec['rect']['height']}")

        sc = desktop_data.get('sectionCounts', {})
        print(f"  Old sections (.elementor-section): {sc.get('oldFormat', '?')}")
        print(f"  New sections (.e-con): {sc.get('newFormat', '?')}")
        print(f"  Has section-wrap: {sc.get('hasSectionWrap', '?')}")

        print(f"\n--- SECTIONS ({len(desktop_data['sections'])} top-level) ---")
        for sec in desktop_data['sections']:
            status = ""
            if sec['isZeroHeight']:
                status = " *** ZERO HEIGHT ***"
            elif sec['isHidden']:
                status = " *** HIDDEN ***"

            print(f"\n  [{sec['index']}]{status}")
            print(f"    Tag: {sec['tagName']}, ID: {sec.get('id', '-')}")
            print(f"    Classes: {sec['className'][:140]}")
            print(f"    data-id: {sec.get('dataId', '-')}, element_type: {sec.get('elementType', '-')}")
            print(f"    Rect: x={sec['rect']['x']}, y={sec['rect']['y']}, "
                  f"W={sec['rect']['width']}, H={sec['rect']['height']}")
            cs = sec['computedStyle']
            print(f"    display={cs['display']} visibility={cs['visibility']} opacity={cs['opacity']} "
                  f"overflow={cs['overflow']}")
            print(f"    position={cs['position']} z-index={cs['zIndex']}")
            print(f"    margin: {cs['margin']}")
            print(f"    padding: {cs['padding']}")
            print(f"    bg-color: {cs['backgroundColor']}")
            print(f"    bg-image: {cs['backgroundImage'][:100]}")
            print(f"    bg-size: {cs.get('backgroundSize','-')} bg-pos: {cs.get('backgroundPosition','-')}")
            print(f"    min-h: {cs['minHeight']} max-h: {cs['maxHeight']} h: {cs.get('height','-')}")
            print(f"    direction: {cs['direction']}")
            ist = sec.get('innerStructure', {})
            print(f"    Columns: {ist.get('columnCount',0)}, Widgets: {ist.get('widgetCount',0)}")
            print(f"    Widget types: {ist.get('widgetTypes', [])}")
            print(f"    Images: {sec['imageCount']}, Products: {sec['productCount']}, WooGrid: {sec['hasWooGrid']}")
            if sec['textPreview']:
                print(f"    Text: {sec['textPreview'][:150]}")
            for img in sec.get('imageSamples', [])[:2]:
                print(f"    IMG: {img['src'][:100]} nat={img['naturalWidth']}x{img['naturalHeight']} "
                      f"disp={img['displayWidth']}x{img['displayHeight']}")

        hero = desktop_data.get('heroAnalysis')
        if hero:
            print(f"\n--- HERO SECTION DEEP ANALYSIS ---")
            print(f"  Tag: {hero['tagName']}, Classes: {hero['className'][:200]}")
            r = hero['rect']
            print(f"  Rect: w={r['width']:.0f}, h={r['height']:.0f}")
            print(f"  bg-image: {hero['backgroundImage'][:200]}")
            print(f"  bg-color: {hero['backgroundColor']}")
            print(f"  bg-size: {hero.get('backgroundSize','-')}, bg-pos: {hero.get('backgroundPosition','-')}")
            print(f"  min-height: {hero['minHeight']}, height: {hero.get('height','-')}")
            print(f"  padding: {hero['padding']}")
            print(f"  overlay: {hero['overlayExists']}, "
                  f"overlay-color: {hero.get('overlayColor','-')}, "
                  f"overlay-opacity: {hero.get('overlayOpacity','-')}")
            print(f"  Headings:")
            for h in hero.get('headings', []):
                print(f"    <{h['tag']}> \"{h['text']}\" size={h['fontSize']} "
                      f"color={h['color']} weight={h.get('fontWeight','-')}")
            print(f"  Buttons:")
            for b in hero.get('buttons', []):
                print(f"    \"{b['text']}\" -> {b['href'][:80]} bg={b.get('bgColor','-')} color={b.get('color','-')}")
            if hero.get('heroImage'):
                hi = hero['heroImage']
                print(f"  Hero image: {hi['src'][:150]}")
                print(f"    nat={hi['naturalWidth']}x{hi['naturalHeight']} "
                      f"disp={hi['displayWidth']}x{hi['displayHeight']}")

        if desktop_data['productGrids']:
            print(f"\n--- PRODUCT GRIDS ({len(desktop_data['productGrids'])}) ---")
            for pg in desktop_data['productGrids']:
                print(f"  Grid: <{pg.get('tagName','?')}> {pg['className'][:100]}")
                print(f"    Products: {pg['productCount']}, display: {pg['display']}")
                print(f"    grid-template-columns: {pg.get('gridTemplateColumns', 'N/A')}")
                print(f"    flex-wrap: {pg.get('flexWrap', 'N/A')}")
                print(f"    gap: {pg.get('gap', 'N/A')}")
                r = pg['rect']
                print(f"    Rect: w={r['width']:.0f}, h={r['height']:.0f}")
                ca = pg.get('columnAnalysis')
                if ca:
                    print(f"    Columns in first row: {ca['columnsInFirstRow']}")
                if pg.get('firstProductImage'):
                    fpi = pg['firstProductImage']
                    print(f"    1st img: nat={fpi['naturalWidth']}x{fpi['naturalHeight']} "
                          f"disp={fpi['displayWidth']}x{fpi['displayHeight']}")
        else:
            print(f"\n--- NO PRODUCT GRIDS FOUND ---")

        if desktop_data['hiddenElements']:
            print(f"\n--- HIDDEN/ZERO-HEIGHT ELEMENTS ({len(desktop_data['hiddenElements'])}) ---")
            for he in desktop_data['hiddenElements']:
                print(f"  [{he['index']}] {he['reason']}: "
                      f"display={he['display']} visibility={he['visibility']} "
                      f"opacity={he['opacity']} height={he['height']}")
                print(f"    class: {he.get('className','')[:100]}")

        if desktop_data.get('sectionSpacing'):
            print(f"\n--- SECTION SPACING ---")
            for sp in desktop_data['sectionSpacing']:
                flag = ""
                if sp['gapPx'] < -5:
                    flag = " <<< OVERLAP"
                elif sp['gapPx'] > 100:
                    flag = " <<< LARGE GAP"
                elif sp['gapPx'] < 0:
                    flag = " (minor overlap)"
                print(f"  {sp['between']}: {sp['gapPx']}px{flag}")

        ctx_desktop.close()

        # ===== MOBILE =====
        print("\n" + "=" * 60)
        print("MOBILE DIAGNOSIS (393x852)")
        print("=" * 60)

        ctx_mobile = browser.new_context(
            viewport=MOBILE_VP,
            locale="ar-SA",
            device_scale_factor=2,
            is_mobile=True,
            has_touch=True,
            ignore_https_errors=True,
        )
        page_m = ctx_mobile.new_page()
        page_m.goto(SITE, wait_until="networkidle", timeout=60000)
        page_m.wait_for_timeout(3000)

        mobile_path = f"{SCREENSHOT_DIR}/home_diag_mobile.png"
        page_m.screenshot(path=mobile_path, full_page=True)
        print(f"Mobile screenshot saved: {mobile_path}")

        mobile_data = diagnose_sections(page_m)
        print(f"Scroll height: {mobile_data['scrollHeight']}px")
        print(f"Sections found: {len(mobile_data['sections'])}")

        for sec in mobile_data['sections']:
            status = ""
            if sec['isZeroHeight']:
                status = " *** ZERO HEIGHT ***"
            elif sec['isHidden']:
                status = " *** HIDDEN ***"
            print(f"  [{sec['index']}]{status}: "
                  f"{sec['rect']['width']}x{sec['rect']['height']} @ y={sec['rect']['y']}  "
                  f"display={sec['computedStyle']['display']} "
                  f"imgs={sec['imageCount']} prods={sec['productCount']}  "
                  f"text=\"{sec['textPreview'][:80]}\"")

        if mobile_data.get('sectionSpacing'):
            print(f"\n--- MOBILE SECTION SPACING ---")
            for sp in mobile_data['sectionSpacing']:
                flag = ""
                if sp['gapPx'] < -5: flag = " <<< OVERLAP"
                elif sp['gapPx'] > 100: flag = " <<< LARGE GAP"
                print(f"  {sp['between']}: {sp['gapPx']}px{flag}")

        if mobile_data['hiddenElements']:
            print(f"\n--- MOBILE HIDDEN/ZERO-HEIGHT ---")
            for he in mobile_data['hiddenElements']:
                print(f"  [{he['index']}]: {he['reason']} h={he['height']}")

        if mobile_data['productGrids']:
            print(f"\n--- MOBILE PRODUCT GRIDS ---")
            for pg in mobile_data['productGrids']:
                print(f"  Products: {pg['productCount']}, display: {pg['display']}, "
                      f"cols: {pg.get('gridTemplateColumns', '-')}, "
                      f"rect: {pg['rect']['width']:.0f}x{pg['rect']['height']:.0f}")
                ca = pg.get('columnAnalysis')
                if ca:
                    print(f"    Columns in first row: {ca['columnsInFirstRow']}")

        ctx_mobile.close()
        browser.close()

        # Save JSON
        report = {"desktop": desktop_data, "mobile": mobile_data}
        report_path = f"{SCREENSHOT_DIR}/home_diag_report.json"
        with open(report_path, "w", encoding="utf-8") as f:
            json.dump(report, f, ensure_ascii=False, indent=2)
        print(f"\nFull JSON report saved: {report_path}")

    print("\nDiagnosis complete.")


if __name__ == "__main__":
    run()
