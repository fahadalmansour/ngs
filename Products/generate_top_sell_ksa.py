#!/usr/bin/env python3
"""Generate Saudi-market top-sell CSV slices from WooCommerce catalog data."""

from pathlib import Path

import pandas as pd


BASE_DIR = Path(__file__).resolve().parent
SOURCE_CSV = BASE_DIR / "woocommerce-final.csv"

OUTPUTS = {
    200: BASE_DIR / "woocommerce-top-sell-200.csv",
    100: BASE_DIR / "woocommerce-top-sell-100.csv",
    50: BASE_DIR / "woocommerce-top-sell-50.csv",
}

BALANCED_OUTPUTS = {
    200: BASE_DIR / "woocommerce-top-sell-balanced-200.csv",
    100: BASE_DIR / "woocommerce-top-sell-balanced-100.csv",
    50: BASE_DIR / "woocommerce-top-sell-balanced-50.csv",
}

BALANCED_MAX_SHARE = {
    200: 0.12,
    100: 0.14,
    50: 0.16,
}


# Derived from NGS_MARKET_TRENDS_2025.md:
# 1) Security, 2) Energy/Climate, 3) Lighting/Convenience, plus HA ecosystem.
CATEGORY_PRIORITY = [
    (130, ["security", "camera", "cam", "doorbell", "lock", "access", "nvr", "dvr", "monitoring", "ptz"]),
    (115, ["climate", "mini split", "air purifier", "ventilation", "fan", "humidifier", "dehumidifier"]),
    (110, ["energy", "energy monitor", "smart valve", "leak", "water heater", "water quality"]),
    (100, ["lighting", "motion lighting", "switch", "plug", "relay", "in-wall", "covers", "button"]),
    (95, ["voice assistants", "voice", "smart display", "video calling"]),
    (90, ["sensor", "zigbee", "thread border router", "hub", "bridge", "coordinator"]),
    (80, ["ha hardware", "official ha", "raspberry pi", "mini pc", "sbc", "microcontroller"]),
]

BRAND_BONUS = [
    (20, ["aqara", "sonoff", "xiaomi", "ring", "yale", "shelly"]),
    (14, ["google", "amazon", "apple", "eufy", "tuya", "philips", "tp-link"]),
]

CATEGORY_PENALTY = [
    (-95, ["usb cable", "ethernet cable", "adapter", "misc component", "mounts", "brackets", "tools"]),
    (-80, ["power supply", "gpu", "network card", "server", "hdd", "nvme", "sata ssd", "raid", "hba"]),
    (-70, ["services"]),
]

SMART_HOME_KEYWORDS = [
    "smart",
    "zigbee",
    "z-wave",
    "matter",
    "thread",
    "home assistant",
    "sensor",
    "camera",
    "doorbell",
    "lock",
    "switch",
    "relay",
    "thermostat",
    "climate",
    "ac",
    "hub",
    "gateway",
    "voice",
    "alexa",
    "google",
    "apple homekit",
    "energy monitor",
    "leak",
]


def _norm(value) -> str:
    if pd.isna(value):
        return ""
    return str(value).strip().lower()


def _featured_to_int(value) -> int:
    text = _norm(value)
    return 1 if text in {"1", "true", "yes"} else 0


def _to_float(value, default=0.0) -> float:
    try:
        return float(value)
    except (TypeError, ValueError):
        return default


def _category_score(category: str) -> int:
    score = 0
    for weight, keywords in CATEGORY_PRIORITY:
        if any(k in category for k in keywords):
            score = max(score, weight)
    for penalty, keywords in CATEGORY_PENALTY:
        if any(k in category for k in keywords):
            score += penalty
    return score


def _brand_score(brand: str) -> int:
    for bonus, keywords in BRAND_BONUS:
        if any(k in brand for k in keywords):
            return bonus
    if "generic" in brand:
        return -8
    return 0


def _price_score(price: float) -> float:
    # Favor practical consumer-price bands in KSA while keeping premium options.
    if 80 <= price <= 1200:
        return 10.0
    if 1200 < price <= 3000:
        return 6.0
    if 30 <= price < 80:
        return 4.0
    return 1.0


def _keyword_score(text: str) -> int:
    return sum(3 for kw in SMART_HOME_KEYWORDS if kw in text)


def _category_cap(limit: int, max_share: float) -> int:
    return max(2, int(limit * max_share + 0.9999))


def _build_balanced_subset(ranked: pd.DataFrame, limit: int, max_share: float) -> pd.DataFrame:
    """Apply category caps while preserving ranked relevance."""
    r = ranked.reset_index(drop=True).copy()
    r["_cat_display"] = r["Categories"].fillna("Uncategorized").astype(str)
    r["_row_id"] = r.index

    hard_cap = _category_cap(limit, max_share)
    relaxed_cap = hard_cap + max(1, limit // 100)

    selected = []
    selected_set = set()
    per_cat = {}

    def add_from_pool(pool: pd.DataFrame, cap: int) -> None:
        for row_id, cat in zip(pool["_row_id"], pool["_cat_display"]):
            if len(selected) >= limit:
                return
            if row_id in selected_set:
                continue
            if per_cat.get(cat, 0) >= cap:
                continue
            selected.append(row_id)
            selected_set.add(row_id)
            per_cat[cat] = per_cat.get(cat, 0) + 1

    # Priority pools: keep strong smart-home relevance first, then broaden.
    primary_pool = r[r["_score_cat"] >= 80]
    secondary_pool = r[(r["_score_cat"] >= 30) & (r["_score_cat"] < 80)]

    add_from_pool(primary_pool, hard_cap)
    add_from_pool(secondary_pool, hard_cap)
    add_from_pool(r, relaxed_cap)

    if len(selected) < limit:
        for row_id in r["_row_id"]:
            if len(selected) >= limit:
                break
            if row_id in selected_set:
                continue
            selected.append(row_id)
            selected_set.add(row_id)

    return r.loc[selected].drop(columns=["_cat_display", "_row_id"])


def main() -> None:
    if not SOURCE_CSV.exists():
        raise FileNotFoundError(f"Missing source file: {SOURCE_CSV}")

    df = pd.read_csv(SOURCE_CSV)

    df["_cat_norm"] = df["Categories"].map(_norm)
    df["_brand_norm"] = df["Brands"].map(_norm) if "Brands" in df.columns else ""
    df["_tags_norm"] = df["Tags"].map(_norm) if "Tags" in df.columns else ""
    df["_featured"] = df["Is featured?"].map(_featured_to_int) if "Is featured?" in df.columns else 0
    df["_margin"] = pd.to_numeric(df.get("Meta: _margin"), errors="coerce").fillna(0.0)
    df["_price"] = pd.to_numeric(df.get("Regular price"), errors="coerce").fillna(0.0)
    df["_stock"] = pd.to_numeric(df.get("Stock"), errors="coerce").fillna(0.0)
    df["_name_norm"] = df["Name"].map(_norm) if "Name" in df.columns else ""
    df["_desc_norm"] = df["Description"].map(_norm) if "Description" in df.columns else ""

    # Keep top-sell outputs product-focused for smart home.
    df = df[df["_cat_norm"] != "services"].copy()

    df["_score_cat"] = df["_cat_norm"].map(_category_score)
    df["_score_brand"] = df["_brand_norm"].map(_brand_score)
    df["_score_featured"] = df["_featured"] * 22
    df["_score_margin"] = df["_margin"].clip(lower=0, upper=50) * 0.08
    df["_score_price"] = df["_price"].map(_price_score)
    df["_score_stock"] = (df["_stock"] > 0).astype(int) * 5
    df["_score_exclusive"] = df["_tags_norm"].str.contains("ngs-exclusive", na=False).astype(int) * 6
    df["_score_keywords"] = (
        (df["_name_norm"] + " " + df["_cat_norm"] + " " + df["_tags_norm"] + " " + df["_desc_norm"])
        .map(_keyword_score)
        .clip(upper=30)
    )

    df["_ksa_score"] = (
        df["_score_cat"]
        + df["_score_brand"]
        + df["_score_featured"]
        + df["_score_margin"]
        + df["_score_price"]
        + df["_score_stock"]
        + df["_score_exclusive"]
        + df["_score_keywords"]
    )

    ranked = df.sort_values(
        by=["_ksa_score", "_featured", "_margin", "_price", "Name"],
        ascending=[False, False, False, True, True],
        kind="mergesort",
    )

    drop_cols = [c for c in ranked.columns if c.startswith("_")]
    for limit, out_path in OUTPUTS.items():
        ranked.head(limit).drop(columns=drop_cols).to_csv(out_path, index=False)
        print(f"Wrote {out_path.name} ({min(limit, len(ranked))} rows)")

    for limit, out_path in BALANCED_OUTPUTS.items():
        max_share = BALANCED_MAX_SHARE.get(limit, 0.14)
        balanced = _build_balanced_subset(ranked, limit, max_share)
        balanced.drop(columns=drop_cols).to_csv(out_path, index=False)
        print(f"Wrote {out_path.name} ({min(limit, len(balanced))} rows)")


if __name__ == "__main__":
    main()
